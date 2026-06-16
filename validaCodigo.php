<?php
session_start();
include("php/conexao.php");

$email = $_SESSION['email_recuperacao'];
if (!isset($_POST['codigo'])) {
    die("Código não informado.");
}

$codigo = $_POST['codigo'];

$sql = "SELECT * FROM funcionario
        WHERE email = ?
        AND codigo_recuperacao = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $email, $codigo);
$stmt->execute();

$resultado = $stmt->get_result();

if($resultado->num_rows > 0){

    $_SESSION['codigo_validado'] = true;

    echo "Código validado com sucesso!";

}else{

    echo "Código inválido.";

}
?>