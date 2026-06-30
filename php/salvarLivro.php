<?php
include("conexao.php");

$funcao = $_GET["funcao"] ?? '';

if ($funcao == "I") {
    $titulo  = mysqli_real_escape_string($conn, $_POST["nTitulo"] ?? '');
    $autor   = mysqli_real_escape_string($conn, $_POST["nAutor"]  ?? '');
    $genero  = (int)($_POST["nGenero"]  ?? 0);
    $editora = (int)($_POST["nEditora"] ?? 0);
    $ano     = (int)($_POST["nAno"]     ?? 0);
    $isbn    = mysqli_real_escape_string($conn, $_POST["nIsbn"]   ?? '');
    $qtd     = (int)($_POST["nQtd"]     ?? 1);

    if (!$titulo || !$autor || !$genero || !$editora || !$ano || !$isbn || $qtd < 1 || $qtd > 99) {
        header("Location: ../livros.php?erro_cad=1");
        exit;
    }

    $sqlCheck = "SELECT idLivro FROM livro WHERE Isbn = '$isbn' LIMIT 1;";
    $resCheck  = mysqli_query($conn, $sqlCheck);
    if ($resCheck && mysqli_num_rows($resCheck) > 0) {
        header("Location: ../livros.php?erro_isbn=1");
        exit;
    }

    $sqlLivro = "INSERT INTO livro (Titulo, Autor, idGenero, idEditora, Isbn, ano)
                 VALUES ('$titulo', '$autor', $genero, $editora, '$isbn', $ano);";

    if (!mysqli_query($conn, $sqlLivro)) {
        header("Location: ../livros.php?erro_cad=1");
        exit;
    }

    $idLivro = mysqli_insert_id($conn);

    // Cria os exemplares (cópias físicas) deste livro
    for ($i = 0; $i < $qtd; $i++) {
        $sqlEx = "INSERT INTO exemplar (idLivro, Emprestado, Ativo) VALUES ($idLivro, 'nao', 'S');";
        mysqli_query($conn, $sqlEx);
    }

    // Upload da capa (opcional)
    if (isset($_FILES['Capa']) && $_FILES['Capa']['tmp_name'] != '') {
        $ext      = pathinfo($_FILES['Capa']['name'], PATHINFO_EXTENSION);
        $novoNome = 'capa-'.$idLivro.'-'.time().'.'.$ext;

        if (!is_dir('../dist/img/livros/')) {
            mkdir('../dist/img/livros/', 0777, true);
        }

        if (move_uploaded_file($_FILES['Capa']['tmp_name'], '../dist/img/livros/'.$novoNome)) {
            $dirImagem = 'dist/img/livros/'.$novoNome;
            mysqli_query($conn, "UPDATE livro SET Foto = '$dirImagem' WHERE idLivro = $idLivro;");
        }
    }

    mysqli_close($conn);
    header("Location: ../livros.php?sucesso_cad=1");
    exit;
}

mysqli_close($conn);
header("Location: ../livros.php");
exit;
?>