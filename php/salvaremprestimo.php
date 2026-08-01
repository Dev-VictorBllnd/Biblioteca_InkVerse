<?php
include('autenticacao.php');
include('conexao.php');
include('funcaoCliente.php'); // NOVO — para statusMultaCliente()

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

    // NOVO — bloqueio real no servidor, buscando do mesmo lugar que a tela de Clientes usa
    $statusMulta = statusMultaCliente($cliente);
    if ($statusMulta['tem_multa']) {
        header("Location: ../emprestimo.php?erro=multa");
        exit;
    }

    // ... resto da função continua igual

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
    $hoje = date('Y-m-d');
    $TAXA_MULTA_DIA = 1.00; // mesma regra já usada na tela (R$/dia)

    // Nunca confia no valor calculado no navegador — recalcula no servidor
    $qItem = mysqli_query($conn, "
        SELECT ehe.data_prevista, e.idCliente
        FROM emprestimo_has_exemplar ehe
        INNER JOIN emprestimo e ON e.idEmprestimo = ehe.idEmprestimo
        WHERE ehe.idEmprestimo = $idEmprestimo AND ehe.idExemplar = $idExemplar
        LIMIT 1
    ");
    $item = ($qItem && mysqli_num_rows($qItem) > 0) ? mysqli_fetch_assoc($qItem) : null;

    $valorMulta = 0.00;
    if ($item && $item['data_prevista'] < $hoje) {
        $diasAtraso = (int)floor((strtotime($hoje) - strtotime($item['data_prevista'])) / 86400);
        $valorMulta = $diasAtraso * $TAXA_MULTA_DIA;
    }
    $valorMultaSql = $valorMulta > 0 ? $valorMulta : "NULL";

    mysqli_query($conn, "
        UPDATE emprestimo_has_exemplar
        SET Data_devolucao = NOW(),
            multa = $valorMultaSql
        WHERE idEmprestimo = '$idEmprestimo' AND idExemplar = '$idExemplar'
    ");
    mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'");

    // Congela a multa no saldo do cliente — some da lista "em mãos",
    // mas continua contando até ser quitada em Clientes.
    if ($valorMulta > 0 && $item) {
        mysqli_query($conn, "
            UPDATE cliente
            SET multa = COALESCE(multa, 0) + $valorMulta
            WHERE idCliente = " . (int)$item['idCliente'] . "
        ");
    }

    header("Location: ../emprestimo.php?sucesso=devolvido");
    exit;
}

    // =========================================================================
    // 4. PAGAR MULTA (Função M) — grava multa no exemplar E credita no saldo do cliente
    // =========================================================================
    if ($funcao == 'M') {
    $idEmprestimo = (int)($_POST['idEmprestimo'] ?? 0);
    $idExemplar   = (int)($_POST['idExemplar']   ?? 0);
    $valorMulta   = (float)($_POST['nValorMulta'] ?? 0);

    mysqli_query($conn, "
        UPDATE emprestimo_has_exemplar
        SET multa = $valorMulta,
            Data_devolucao = NOW()
        WHERE idEmprestimo = $idEmprestimo AND idExemplar = $idExemplar
    ");
    mysqli_query($conn, "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'");

    if ($valorMulta > 0) {
        mysqli_query($conn, "
            UPDATE cliente c
            INNER JOIN emprestimo e ON e.idCliente = c.idCliente
            SET c.multa = COALESCE(c.multa, 0) + $valorMulta
            WHERE e.idEmprestimo = $idEmprestimo
        ");
    }

    header("Location: ../emprestimo.php?sucesso=multa");
    exit;
}

    // =========================================================================
    // 5. QUITAR MULTA(S) DO CLIENTE (Função P) — zera o saldo e libera novos empréstimos
    // =========================================================================
    if ($funcao == 'P') {
        $cliente = (int)($_POST['nCliente'] ?? 0);

        if ($cliente > 0) {
            mysqli_query($conn, "UPDATE cliente SET multa = 0 WHERE idCliente = $cliente");

            // Mantém o histórico por exemplar marcado como pago (apenas para consulta/auditoria)
            mysqli_query($conn, "
                UPDATE emprestimo_has_exemplar ehe
                INNER JOIN emprestimo e ON e.idEmprestimo = ehe.idEmprestimo
                SET ehe.multa_paga = 'S'
                WHERE e.idCliente = $cliente
                  AND ehe.multa > 0
                  AND ehe.multa_paga = 'N'
            ");
        }

        // Se veio de uma requisição AJAX (tela de clientes), responde JSON
        if (!empty($_POST['ajax'])) {
            header('Content-Type: application/json');
            echo json_encode(['sucesso' => true]);
            exit;
        }

        header("Location: ../clientes.php?sucesso=multaquitada");
        exit;
    }
}
?>