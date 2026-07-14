<?php
session_start();
include('conexao.php');

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
    // =========================================================================
    if ($funcao == 'D') {
        $idEmprestimo = (int)($_POST['idEmprestimo'] ?? 0);
        $idExemplar   = (int)($_POST['idExemplar']   ?? 0);

        mysqli_query($conn, "
            UPDATE emprestimo_has_exemplar
            SET Data_devolucao = NOW()
            WHERE idEmprestimo = '$idEmprestimo' AND idExemplar = '$idExemplar'
        ");
        mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'");

        header("Location: ../emprestimo.php?sucesso=devolvido");
        exit;
    }

    // =========================================================================
    // 4. PAGAR MULTA (Função M) — grava multa APENAS no exemplar específico
    // =========================================================================
    if ($funcao == 'M') {
        $idEmprestimo = (int)($_POST['idEmprestimo'] ?? 0);
        $idExemplar   = (int)($_POST['idExemplar']   ?? 0);
        $valorMulta   = (float)($_POST['nValorMulta'] ?? 0);

        // Grava multa e devolução SOMENTE neste exemplar — não afeta os outros
        mysqli_query($conn, "
            UPDATE emprestimo_has_exemplar
            SET multa = $valorMulta,
                Data_devolucao = NOW()
            WHERE idEmprestimo = $idEmprestimo AND idExemplar = $idExemplar
        ");
        mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'");

        header("Location: ../emprestimo.php?sucesso=multa");
        exit;
    }
}
?>