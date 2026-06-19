<?php
session_start();
include('conexao.php'); 

if (isset($_GET['funcao'])) {
    $funcao = $_GET['funcao'];

    // =========================================================================
    // 1. INSERIR NOVO EMPRÉSTIMO (Função I)
    // =========================================================================
    if ($funcao == 'I') {
        $cliente = $_POST['nCliente'];
        $dataEmprestimo = $_POST['nDataEmprestimo'];
        $dataPrevista = $_POST['nDataPrevista'];
        $idFuncionario = 1; // ID padrão do funcionário de teste
        
        $exemplares = isset($_POST['nExemplares']) ? $_POST['nExemplares'] : [];

        if (count($exemplares) == 0) {
            header("Location: ../emprestimos.php?erro=sem_livro");
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
    // 2. ATUALIZAR / REEMPRESTAR LIVRO INDIVIDUAL (Função U)
    // =========================================================================
    if ($funcao == 'U') {
        $idEmprestimo = $_POST['idEmprestimo'];
        $idExemplar   = $_POST['idExemplar'];
        $dataEmprestimo = $_POST['nDataEmprestimo'];
        $dataPrevista   = $_POST['nDataPrevista'];

        // Atualiza a data de empréstimo e a data prevista de devolução APENAS deste exemplar
        $sqlUpd = "UPDATE emprestimo_has_exemplar 
                   SET Data_emprestimo = '$dataEmprestimo', 
                       data_prevista = '$dataPrevista'
                   WHERE idEmprestimo = '$idEmprestimo' AND idExemplar = '$idExemplar'";
        
        if(mysqli_query($conn, $sqlUpd)) {
            // Redireciona com feedback de sucesso
            header("Location: ../emprestimo.php?sucesso=editado");
            exit;
        } else {
            echo "Erro ao atualizar dados: " . mysqli_error($conn);
            exit;
        }
    }
}
?>