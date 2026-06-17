<?php
session_start();

if(isset($_POST['codigo'])){

    $codigoDigitado = $_POST['codigo'];

    if($codigoDigitado == $_SESSION['codigo_recuperacao']){

        header("Location: nova-senha.php");
        exit();

    }else{

        $erro = "Código inválido.";

    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<title>Verificar Código</title>
</head>
<body>

<h2>Verificação</h2>

<p>
Código para teste:
<strong>
<?php echo $_SESSION['codigo_recuperacao']; ?>
</strong>
</p>

<?php
if(isset($erro)){
    echo "<p style='color:red'>$erro</p>";
}
?>

<form method="POST">

    <label>Digite o código</label>
    <br><br>

    <input
        type="text"
        name="codigo"
        maxlength="6"
        required>

    <br><br>

    <button type="submit">
        Verificar
    </button>

</form>

</body>
</html>