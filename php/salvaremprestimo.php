<?php
session_start();
include('conexao.php');

if (isset($_GET['funcao'])) {
    $funcao = $_GET['funcao'];

    // =========================================================================
    // 1. INSERIR NOVO EMPRÉSTIMO (Função I)
    // =========================================================================
    if ($funcao == 'I') {
        $cliente        = $_POST['nCliente']       ?? 0;
        $dataEmprestimo = $_POST['nDataEmprestimo'] ?? date('Y-m-d');
        $dataPrevista   = $_POST['nDataPrevista']   ?? date('Y-m-d', strtotime('+7 days'));
        $idFuncionario  = $_SESSION['idLogin']      ?? 1;

        $exemplares = isset($_POST['nExemplares']) ? $_POST['nExemplares'] : [];

        if (count($exemplares) == 0) {
            header("Location: ../emprestimo.php?erro=sem_livro");
            exit;
        }

        // Limite de 5 livros por cliente (considera os que já estão em mãos)
        $LIMITE_CLIENTE = 5;
        $clienteSeguro  = (int)$cliente;
        $qPend = mysqli_query($conn, "
            SELECT COUNT(*) AS qtd
            FROM emprestimo e
            INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
            WHERE e.idCliente = $clienteSeguro AND ehe.Data_devolucao IS NULL
        ");
        $jaTem = ($qPend) ? (int)mysqli_fetch_assoc($qPend)['qtd'] : 0;

        if (($jaTem + count($exemplares)) > $LIMITE_CLIENTE) {
            header("Location: ../emprestimo.php?erro=limite");
            exit;
        }

        $sqlEmp = "INSERT INTO emprestimo (idCliente, idFuncionario) VALUES ('$cliente', '$idFuncionario')";

        if (mysqli_query($conn, $sqlEmp)) {
            $idEmprestimo = mysqli_insert_id($conn);

            foreach ($exemplares as $idExemplar) {
                $sqlExe = "INSERT INTO emprestimo_has_exemplar (idEmprestimo, idExemplar, Data_emprestimo, data_prevista)
                           VALUES ('$idEmprestimo', '$idExemplar', '$dataEmprestimo', '$dataPrevista')";
                mysqli_query($conn, $sqlExe);

                $sqlStatus = "UPDATE exemplar SET Emprestado = 'sim' WHERE idExemplar = '$idExemplar'";
                mysqli_query($conn, $sqlStatus);
            }

            header("Location: ../emprestimo.php?sucesso=inserido");
            exit;
        } else {
            echo "Erro na Base de Dados: " . mysqli_error($conn);
            exit;
        }
    }

    // =========================================================================
    // 2. ATUALIZAR / RENOVAR EMPRÉSTIMO (Função U)
    // =========================================================================
    if ($funcao == 'U') {
        $idEmprestimo   = $_POST['idEmprestimo']   ?? 0;
        $idExemplar     = $_POST['idExemplar']     ?? 0;
        $dataEmprestimo = $_POST['nDataEmprestimo'] ?? '';
        $dataPrevista   = $_POST['nDataPrevista']   ?? '';

        $sqlUpd = "UPDATE emprestimo_has_exemplar
                   SET Data_emprestimo = '$dataEmprestimo',
                       data_prevista = '$dataPrevista'
                   WHERE idEmprestimo = '$idEmprestimo' AND idExemplar = '$idExemplar'";

        if(mysqli_query($conn, $sqlUpd)) {
            header("Location: ../emprestimo.php?sucesso=editado");
            exit;
        } else {
            echo "Erro ao atualizar dados: " . mysqli_error($conn);
            exit;
        }
    }

    // =========================================================================
    // 3. DEVOLVER EXEMPLAR INDIVIDUAL (Função D)
    // =========================================================================
    if ($funcao == 'D') {
        $idEmprestimo = $_POST['idEmprestimo'] ?? 0;
        $idExemplar   = $_POST['idExemplar']   ?? 0;

        $sqlDev = "UPDATE emprestimo_has_exemplar
                   SET Data_devolucao = NOW()
                   WHERE idEmprestimo = '$idEmprestimo' AND idExemplar = '$idExemplar'";
        mysqli_query($conn, $sqlDev);

        $sqlLib = "UPDATE exemplar SET Emprestado = 'nao' WHERE idExemplar = '$idExemplar'";
        mysqli_query($conn, $sqlLib);

        header("Location: ../emprestimo.php?sucesso=devolvido");
        exit;
    }
}
?>
