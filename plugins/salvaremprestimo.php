<?php
session_start();
// Ajuste o caminho da conexão se necessário (ex: '../php/conexao.php' ou apenas 'conexao.php' se já estiver na mesma pasta)
include('conexao.php'); 

if (isset($_GET['funcao'])) {
    $funcao = $_GET['funcao'];

    // ==========================================
    // 1. INSERIR NOVO EMPRÉSTIMO
    // ==========================================
    if ($funcao == 'I') {
        // Recebe os dados do modal de Novo Empréstimo
        $idCliente = $_POST['nCliente'];
        $idExemplar = $_POST['nExemplar'];
        $dataEmprestimo = $_POST['nDataEmprestimo'];
        $dataPrevista = $_POST['nDataPrevista']; 
        $observacoes = $_POST['nObservacoes']; 

        /* PASSO 1: Inserir na tabela principal de empréstimo.
          Nota: Se a sua tabela 'emprestimo' tiver colunas para data_prevista ou observacoes, 
          você deve adicioná-las aqui. Ex: INSERT INTO emprestimo (idCliente, observacoes) VALUES...
        */
        $sqlEmprestimo = "INSERT INTO emprestimo (idCliente) VALUES ('$idCliente')";
        
        if (mysqli_query($conn, $sqlEmprestimo)) {
            // Captura o ID do empréstimo que acabou de ser gerado no banco
            $idEmprestimo = mysqli_insert_id($conn);

            // PASSO 2: Vincular o exemplar ao empréstimo na tabela auxiliar
            $sqlExemplar = "INSERT INTO emprestimo_has_exemplar (idEmprestimo, idExemplar, Data_emprestimo) 
                            VALUES ('$idEmprestimo', '$idExemplar', '$dataEmprestimo')";
            
            mysqli_query($conn, $sqlExemplar);
            
            // Redireciona de volta para a tela de empréstimos com mensagem de sucesso
            header("Location: ../emprestimos.php?sucesso=1");
        } else {
            // Redireciona com erro
            header("Location: ../emprestimos.php?erro=1");
        }
        exit;
    }

    // ==========================================
    // 2. ATUALIZAR/EDITAR EMPRÉSTIMO
    // ==========================================
    if ($funcao == 'U') {
        // Recebe os dados do modal de Edição
        $idEmprestimo = $_POST['idEmprestimo'];
        $dataEmprestimo = $_POST['nDataEmprestimo'];
        
        // Verifica se o campo de devolução veio vazio; se sim, manda NULL para o banco
        $dataDevolucao = !empty($_POST['nDataDevolucao']) ? "'" . $_POST['nDataDevolucao'] . "'" : "NULL";

        // Atualiza os dados na tabela 'emprestimo_has_exemplar'
        $sqlUpdate = "UPDATE emprestimo_has_exemplar 
                      SET Data_emprestimo = '$dataEmprestimo', 
                          Data_devolucao = $dataDevolucao 
                      WHERE idEmprestimo = '$idEmprestimo'";

        if (mysqli_query($conn, $sqlUpdate)) {
            header("Location: ../emprestimos.php?sucesso=1");
        } else {
            header("Location: ../emprestimos.php?erro=1");
        }
        exit;
    }

// ==========================================
    // 3. REGISTRAR DEVOLUÇÃO (BOTÃO VERDE)
    // ==========================================
    if ($funcao == 'devolver') {
        $idEmprestimo = $_GET['id'];
        
        $sqlDevolver = "UPDATE emprestimo_has_exemplar 
                        SET Data_devolucao = CURDATE() 
                        WHERE idEmprestimo = '$idEmprestimo'";

        // Se a query funcionar...
        if (mysqli_query($conn, $sqlDevolver)) {
            if (isset($_GET['origem']) && $_GET['origem'] == 'devolucoes') {
                header("Location: ../devolucoes.php?sucesso=1");
            } else {
                header("Location: ../emprestimos.php?sucesso=1");
            }
        } else {
            // SE FALHAR, O SISTEMA VAI PARAR E MOSTRAR O ERRO NA TELA
            die("Erro no Banco de Dados: " . mysqli_error($conn));
        }
        exit;
    }
}
?>