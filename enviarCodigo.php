<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include("php/conexao.php");

if (!isset($_POST['email'])) {
    die("E-mail não informado.");
}

$email = trim($_POST['email']);

$sql = "SELECT * FROM funcionario WHERE email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro SQL: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {

    $codigo = rand(100000, 999999);

    $_SESSION['email_recuperacao'] = $email;
    $_SESSION['codigo_recuperacao'] = $codigo;

    $sql = "UPDATE funcionario
            SET codigo_recuperacao = ?
            WHERE email = ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param("is", $codigo, $email);
    $stmt->execute();

    header("Location: verificar-codigo.php");

    exit();

} else {

    echo "E-mail não encontrado.";

}
?>