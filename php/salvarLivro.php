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

    for ($i = 0; $i < $qtd; $i++) {
        mysqli_query($conn, "INSERT INTO exemplar (idLivro, Emprestado) VALUES ($idLivro, 'N');");
    }

    mysqli_close($conn);
    header("Location: ../livros.php?sucesso_cad=1");
    exit;
}

mysqli_close($conn);
header("Location: ../livros.php");
exit;
?>