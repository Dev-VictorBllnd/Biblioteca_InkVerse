<?php
session_start();
include('conexao.php');

// ---------------------------------------------------------------------------
// Retorna true se o cliente tem multa a pagar:
//  - multa congelada de livro já devolvido e não paga, OU
//  - livro ainda em mãos e atrasado (multa em formação)
// ---------------------------------------------------------------------------
function clienteTemMulta($conn, $cliente) {
    $cliente = (int)$cliente;
    if ($cliente <= 0) return false;
    $q = mysqli_query($conn, "
        SELECT COUNT(*) AS qtd
        FROM emprestimo e
        INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
        WHERE e.idCliente = $cliente
          AND (
                (ehe.multa > 0 AND ehe.multa_paga = 'N')
             OR (ehe.Data_devolucao IS NULL AND ehe.data_prevista < CURDATE() AND (ehe.multa IS NULL OR ehe.multa = 0))
          )
    ");
    return ($q) ? ((int)mysqli_fetch_assoc($q)['qtd'] > 0) : false;
}

if (isset($_GET['funcao'])) {
    $funcao = $_GET['funcao'];

    // =========================================================================
    // 1. INSERIR NOVO EMPRÉSTIMO (Função I)
    // =========================================================================
    if ($funcao == 'I') {
        $cliente        = (int)($_POST['nCliente']        ?? 0);
        $dataEmprestimo = $_POST['nDataEmprestimo']        ?? date('Y-m-d');
        $dataPrevista   = $_POST['nDataPrevista']          ?? date('Y-m-d', strtotime('+7 days'));
        $idFuncionario  = $_SESSION['idLogin']             ?? 1;
        $exemplares     = $_POST['nExemplares']            ?? [];

        if (count($exemplares) == 0) {
            header("Location: ../emprestimo.php?erro=sem_livro");
            exit;
        }

        // Quantos livros o cliente já tem em mãos
        $LIMITE_CLIENTE = 5;
        $qPend = mysqli_query($conn, "
            SELECT COUNT(*) AS qtd
            FROM emprestimo e
            INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
            WHERE e.idCliente = $cliente AND ehe.Data_devolucao IS NULL
        ");
        $jaTem = ($qPend) ? (int)mysqli_fetch_assoc($qPend)['qtd'] : 0;

        if (($jaTem + count($exemplares)) > $LIMITE_CLIENTE) {
            header("Location: ../emprestimo.php?erro=limite");
            exit;
        }

        // Bloqueia novo empréstimo se o cliente tiver multa a pagar
        if (clienteTemMulta($conn, $cliente)) {
            header("Location: ../emprestimo.php?erro=multa");
            exit;
        }

        // ── Verifica se o cliente já tem um empréstimo ativo para reaproveitar ──
        $qEmpAtivo = mysqli_query($conn, "
            SELECT DISTINCT e.idEmprestimo
            FROM emprestimo e
            INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
            WHERE e.idCliente = $cliente AND ehe.Data_devolucao IS NULL
            ORDER BY e.idEmprestimo DESC
            LIMIT 1
        ");

        if ($qEmpAtivo && mysqli_num_rows($qEmpAtivo) > 0) {
            // Reutiliza o empréstimo existente
            $idEmprestimo = (int)mysqli_fetch_assoc($qEmpAtivo)['idEmprestimo'];
        } else {
            // Cria novo empréstimo
            mysqli_query($conn, "INSERT INTO emprestimo (idCliente, idFuncionario) VALUES ('$cliente', '$idFuncionario')");
            $idEmprestimo = mysqli_insert_id($conn);
        }

        foreach ($exemplares as $idExemplar) {
            $idExemplar = (int)$idExemplar;
            mysqli_query($conn, "
                INSERT INTO emprestimo_has_exemplar (idEmprestimo, idExemplar, Data_emprestimo, data_prevista)
                VALUES ('$idEmprestimo', '$idExemplar', '$dataEmprestimo', '$dataPrevista')
            ");
            mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'sim' WHERE idExemplar = '$idExemplar'");
        }

        // Se cliente confirmou posse dos livros, renova automaticamente os existentes
        if (isset($_POST['nConfirmaPosse']) && $_POST['nConfirmaPosse'] === 'sim') {
            $novaPrevista = date('Y-m-d', strtotime('+7 days'));
            mysqli_query($conn, "
                UPDATE emprestimo_has_exemplar ehe
                INNER JOIN emprestimo e ON e.idEmprestimo = ehe.idEmprestimo
                SET ehe.data_prevista = '$novaPrevista'
                WHERE e.idCliente = $cliente
                  AND ehe.Data_devolucao IS NULL
                  AND ehe.idEmprestimo != $idEmprestimo
            ");
        }

        header("Location: ../emprestimo.php?sucesso=inserido");
        exit;
    }

    // =========================================================================
    // 2. RENOVAR EMPRÉSTIMO (Função U) — só atualiza data_prevista
    // =========================================================================
    if ($funcao == 'U') {
        $idEmprestimo = (int)($_POST['idEmprestimo'] ?? 0);
        $idExemplar   = (int)($_POST['idExemplar']   ?? 0);
        $dataPrevista = mysqli_real_escape_string($conn, $_POST['nDataPrevista'] ?? '');
        $hoje         = date('Y-m-d');

        if ($dataPrevista < $hoje) {
            header("Location: ../emprestimo.php?erro=datainvalida");
            exit;
        }

        // Descobre o cliente deste empréstimo e bloqueia se tiver multa a pagar
        $qCli    = mysqli_query($conn, "SELECT idCliente FROM emprestimo WHERE idEmprestimo = $idEmprestimo LIMIT 1");
        $cliente = ($qCli && mysqli_num_rows($qCli)) ? (int)mysqli_fetch_assoc($qCli)['idCliente'] : 0;
        if (clienteTemMulta($conn, $cliente)) {
            header("Location: ../emprestimo.php?erro=multa");
            exit;
        }

        mysqli_query($conn, "
            UPDATE emprestimo_has_exemplar
            SET data_prevista = '$dataPrevista'
            WHERE idEmprestimo = $idEmprestimo AND idExemplar = $idExemplar
        ");

        header("Location: ../emprestimo.php?sucesso=editado");
        exit;
    }

    // =========================================================================
    // 3. DEVOLVER EXEMPLAR INDIVIDUAL (Função D)
    //    Se estiver atrasado, congela a multa (grava o valor atual e marca
    //    como NÃO paga) para que ela permaneça registrada e não aumente mais.
    // =========================================================================
    if ($funcao == 'D') {
        $idEmprestimo = (int)($_POST['idEmprestimo'] ?? 0);
        $idExemplar   = (int)($_POST['idExemplar']   ?? 0);

        // Busca a data prevista e a multa atual para congelar, se houver atraso
        $q = mysqli_query($conn, "
            SELECT data_prevista, multa
            FROM emprestimo_has_exemplar
            WHERE idEmprestimo = $idEmprestimo AND idExemplar = $idExemplar
            LIMIT 1
        ");
        $row = ($q) ? mysqli_fetch_assoc($q) : null;

        $multaCongelada = 0.0;
        if ($row) {
            $hoje       = date('Y-m-d');
            $prevista   = substr($row['data_prevista'], 0, 10);
            $multaAtual = (float)$row['multa'];

            if ($prevista < $hoje && $multaAtual == 0) {
                // Ainda não tinha multa gravada → calcula e congela agora
                $dias = (int)floor((strtotime($hoje) - strtotime($prevista)) / 86400);
                $multaCongelada = $dias * 1.00;
            } else {
                // Mantém o valor que já existia (0 se estava no prazo)
                $multaCongelada = $multaAtual;
            }
        }

        mysqli_query($conn, "
            UPDATE emprestimo_has_exemplar
            SET Data_devolucao = NOW(),
                multa      = $multaCongelada,
                multa_paga = 'N'
            WHERE idEmprestimo = '$idEmprestimo' AND idExemplar = '$idExemplar'
        ");
        mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'");

        header("Location: ../emprestimo.php?sucesso=devolvido");
        exit;
    }

    // =========================================================================
    // 4. PAGAR MULTA (Função M) — grava multa APENAS no exemplar específico
    //    e marca como PAGA.
    // =========================================================================
    if ($funcao == 'M') {
        $idEmprestimo = (int)($_POST['idEmprestimo'] ?? 0);
        $idExemplar   = (int)($_POST['idExemplar']   ?? 0);
        $valorMulta   = (float)($_POST['nValorMulta'] ?? 0);

        // Grava multa (paga) e devolução SOMENTE neste exemplar — não afeta os outros
        mysqli_query($conn, "
            UPDATE emprestimo_has_exemplar
            SET multa      = $valorMulta,
                multa_paga = 'S',
                Data_devolucao = NOW()
            WHERE idEmprestimo = $idEmprestimo AND idExemplar = $idExemplar
        ");
        mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'");

        header("Location: ../emprestimo.php?sucesso=multa");
        exit;
    }
}
?>