<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verifica se o código foi validado
if (
    !isset($_SESSION['email']) ||
    empty($_SESSION['email']) ||
    !isset($_SESSION['codigo_validado']) ||
    $_SESSION['codigo_validado'] !== true
) {

    echo "<script>
            alert('Acesso negado. Valide o código primeiro.');
            window.location='Esqueci-Senha.php';
          </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Nova Senha - InkVerse</title>

<link rel="stylesheet" href="dist/css/Novasenha.css">

</head>

<body class="tela-login">

<div class="lado-esquerdo">

    <h2>Redefinir Senha</h2>

    <p>Crie uma nova senha para acessar sua conta.</p>

    <div class="logo-area">
        <img src="dist/img/logo.png" alt="Logo">
    </div>

    <h1>InkVerse</h1>

</div>

<div class="lado-direito">

    <div class="login-box">

        <h2>Nova Senha</h2>

        <form action="php/alterarSenha.php" method="POST">

            <div class="campo">

                <label for="senha">Nova Senha</label>

                <input
                    type="password"
                    id="senha"
                    name="nSenha"
                    placeholder="Digite a nova senha"
                    required>

            </div>

            <div class="campo">

                <label for="confirmar">Confirmar Senha</label>

                <input
                    type="password"
                    id="confirmar"
                    name="nConfirmarSenha"
                    placeholder="Confirme a nova senha"
                    required>

            </div>

            <div class="mostrar-senha">

                <input type="checkbox" id="mostrar">

                <label for="mostrar">Mostrar senha</label>

            </div>

            <button type="submit" class="btn-login">
                Alterar Senha
            </button>

        </form>

    </div>

</div>

<script>

const mostrar = document.getElementById("mostrar");
const senha = document.getElementById("senha");
const confirmar = document.getElementById("confirmar");

mostrar.addEventListener("change", function () {

    const tipo = this.checked ? "text" : "password";

    senha.type = tipo;
    confirmar.type = tipo;

});

</script>

</body>
</html>