<?php
session_start();

require_once("conexao.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    die("Acesso inválido.");
}

// Verifica se o código foi validado
if (
    !isset($_SESSION['email']) ||
    empty($_SESSION['email']) ||
    !isset($_SESSION['codigo_validado'])
) {

    echo "<script>
            alert('Sessão expirada. Faça a recuperação novamente.');
            window.location='../Esqueci-Senha.php';
          </script>";
    exit();
}

$email = $_SESSION['email'];

$senha = trim($_POST['nSenha']);
$confirmar = trim($_POST['nConfirmarSenha']);

// Campos vazios
if (empty($senha) || empty($confirmar)) {

    echo "<script>
            alert('Preencha todos os campos.');
            history.back();
          </script>";
    exit();
}

// Senhas diferentes
if ($senha != $confirmar) {

    echo "<script>
            alert('As senhas não conferem!');
            history.back();
          </script>";
    exit();
}

// Criptografa a senha (compatível com seu login)
$senha = md5($senha);

// Atualiza a senha e limpa o código de recuperação
$sql = "UPDATE funcionario
        SET Senha = ?,
            CodigoRecuperacao = NULL,
            ExpiraCodigo = NULL
        WHERE Email = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Erro SQL: " . $conn->error);
}

$stmt->bind_param("ss", $senha, $email);

if ($stmt->execute()) {

    session_unset();
    session_destroy();

    echo "<script>
            alert('Senha alterada com sucesso!');
            window.location='../index.php';
          </script>";

} else {

    echo "<script>
            alert('Erro ao alterar a senha.');
            history.back();
          </script>";
}

$stmt->close();
$conn->close();
?>