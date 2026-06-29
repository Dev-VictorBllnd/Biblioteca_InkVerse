<?php
    include("conexao.php");

    // Captura os comandos da URL
    $funcao    = $_GET["funcao"] ?? '';
    $idEditora = (int)($_GET["codigo"] ?? 0);

    // Captura os dados do POST de forma segura
    $nome     = mysqli_real_escape_string($conn, $_POST["nNome"]     ?? '');
    $email    = mysqli_real_escape_string($conn, $_POST["nEmail"]    ?? '');
    $telefone = mysqli_real_escape_string($conn, $_POST["nTelefone"] ?? '');

    if($funcao == "I"){
        // INSERÇÃO
        $sql = "INSERT INTO editora (Nome, Email, Telefone) "
              ." VALUES ('$nome', '$email', '$telefone');";
        mysqli_query($conn, $sql);

    } elseif($funcao == "A") {
        // ATUALIZAÇÃO
        $sql = "UPDATE editora "
              ." SET Nome = '$nome', "
              ." Email = '$email', "
              ." Telefone = '$telefone' "
              ." WHERE idEditora = $idEditora;";
        mysqli_query($conn, $sql);

    } elseif($funcao == "D") {
        // EXCLUSÃO (pode falhar por causa da Chave Estrangeira em livro)
        $sql = "DELETE FROM editora WHERE idEditora = $idEditora;";
        if(!mysqli_query($conn, $sql)){
            mysqli_close($conn);
            header("location: ../editoras.php?erro=vinculo");
            exit;
        }
    }

    mysqli_close($conn);
    header("location: ../editoras.php?sucesso=1");
?>
