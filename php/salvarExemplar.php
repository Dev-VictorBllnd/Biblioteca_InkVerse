<?php
include("conexao.php");

$funcao = $_GET["funcao"] ?? '';
$idExemplar = $_GET["codigo"] ?? 0;
$idLivro = $_GET["idLivro"] ?? 0;

if ($funcao == "A") {
    $idLivroForm = $_POST["nIdLivro"];
    $titulo = mysqli_real_escape_string($conn, $_POST["nTitulo"]);
    $autor = mysqli_real_escape_string($conn, $_POST["nAutor"]);
    $genero = (int)$_POST["nGenero"];
    $editora = (int)$_POST["nEditora"];
    $ano = (int)$_POST["nAno"];
    $isbn = mysqli_real_escape_string($conn, $_POST["nIsbn"]);

    // Atualiza todos os exemplares desta obra
    $sql = "UPDATE livro SET 
                Titulo = '$titulo', 
                Autor = '$autor', 
                idGenero = $genero, 
                idEditora = $editora, 
                Isbn = '$isbn', 
                ano = $ano 
            WHERE idLivro = $idLivroForm;";
            
    if (mysqli_query($conn, $sql)) {

        // Upload da nova capa (opcional) — só atualiza se o arquivo for enviado
        if (isset($_FILES['Capa']) && $_FILES['Capa']['tmp_name'] != '') {
            $ext      = pathinfo($_FILES['Capa']['name'], PATHINFO_EXTENSION);
            $novoNome = 'capa-'.$idLivroForm.'-'.time().'.'.$ext;

            if (!is_dir('../dist/img/livros/')) {
                mkdir('../dist/img/livros/', 0777, true);
            }

            if (move_uploaded_file($_FILES['Capa']['tmp_name'], '../dist/img/livros/'.$novoNome)) {
                $dirImagem = 'dist/img/livros/'.$novoNome;
                mysqli_query($conn, "UPDATE livro SET Foto = '$dirImagem' WHERE idLivro = $idLivroForm;");
            }
        }

        header("Location: ../livros.php?sucesso_edit=1");
    } else {
        echo "Erro ao atualizar dados da obra: " . mysqli_error($conn);
    }

} elseif ($funcao == "D") {
    // Tenta Excluir Permanentemente o Exemplar
    $sql = "DELETE FROM exemplar WHERE idExemplar = $idExemplar;";
    
    try {
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            header("Location: ../livros.php?sucesso_del=1");
        } else {
            // Falha por ForeignKey silenciosa
            header("Location: ../livros.php?erro_excluir=1&idExemplar=$idExemplar&idLivro=$idLivro");
        }
    } catch (Exception $e) {
        // Falha por Exception
        header("Location: ../livros.php?erro_excluir=1&idExemplar=$idExemplar&idLivro=$idLivro");
    }

} elseif ($funcao == "I") {
    // INATIVAÇÃO DO EXEMPLAR ESPECÍFICO
    $idExemplarInativar = $_GET["codigo"] ?? 0;
    
    // Altera a flag para 'N' apenas nesta cópia física!
    $sql = "UPDATE exemplar SET Ativo = 'N' WHERE idExemplar = $idExemplarInativar;";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../livros.php?sucesso_inativar=1");
    } else {
        echo "Erro ao inativar exemplar: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>