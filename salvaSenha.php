<?php
session_start();
include("php/conexao.php");

if(!isset($_SESSION['codigo_validado'])){

    die("Acesso negado.");

}

$senha = $_POST['senha'];
$confirmar = $_POST['confirmar'];

if($senha != $confirmar){

    die("As senhas não conferem.");

}

$email = $_SESSION['email_recuperacao'];

$senhaHash = password_hash(
    $senha,
    PASSWORD_DEFAULT
);

$sql = "UPDATE usuario
        SET senha = ?,
            codigo_recuperacao = NULL
        WHERE email = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param(
    "ss",
    $senhaHash,
    $email
);

if($stmt->execute()){

    unset($_SESSION['email_recuperacao']);
    unset($_SESSION['codigo_validado']);

    echo "
    <script>
        alert('Senha alterada com sucesso!');
        window.location='../index.php';
    </script>
    ";

}else{

    echo "Erro ao alterar senha.";

}
?>