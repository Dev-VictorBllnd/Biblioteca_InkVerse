
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

<style>

.requisitos{

    margin-top:20px;
    padding:15px;
    background:#f5f5f5;
    border:1px solid #dcdcdc;
    border-radius:8px;
    text-align:left;

}

.requisitos h4{

    margin-bottom:10px;
    color:#0b1a2c;
    font-size:15px;

}

.requisitos ul{

    list-style:none;
    padding:0;

}

.requisitos li{

    margin:8px 0;
    color:#dc3545;
    font-size:14px;
    transition:.3s;

}

.requisitos li.ok{

    color:#198754;
    font-weight:bold;

}

</style>

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

        <form action="php/alterarSenha.php" method="POST" id="formSenha">

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

            <div class="requisitos">

                <h4>Sua senha deve conter:</h4>

                <ul>

                    <li id="r1">❌ Pelo menos 8 caracteres</li>

                    <li id="r2">❌ Uma letra maiúscula</li>

                    <li id="r3">❌ Uma letra minúscula</li>

                    <li id="r4">❌ Um número</li>

                    <li id="r5">❌ Um caractere especial (@, #, !, $, %, &, ...)</li>

                </ul>

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
const form = document.getElementById("formSenha");

mostrar.addEventListener("change", function(){

    const tipo = this.checked ? "text" : "password";

    senha.type = tipo;
    confirmar.type = tipo;

});

senha.addEventListener("input", validarSenha);

function atualizar(id, valido){

    const item = document.getElementById(id);

    if(valido){

        item.classList.add("ok");
        item.innerHTML = "✔ " + item.textContent.substring(2);

    }else{

        item.classList.remove("ok");
        item.innerHTML = "❌ " + item.textContent.substring(2);

    }

}

function validarSenha(){

    const valor = senha.value;

    atualizar("r1", valor.length >= 8);
    atualizar("r2", /[A-Z]/.test(valor));
    atualizar("r3", /[a-z]/.test(valor));
    atualizar("r4", /[0-9]/.test(valor));
    atualizar("r5", /[^A-Za-z0-9]/.test(valor));

}

form.addEventListener("submit", function(e){

    const valor = senha.value;

    if(
        valor.length < 8 ||
        !/[A-Z]/.test(valor) ||
        !/[a-z]/.test(valor) ||
        !/[0-9]/.test(valor) ||
        !/[^A-Za-z0-9]/.test(valor)
    ){

        e.preventDefault();

        alert("Crie uma senha forte antes de continuar.");

        return;

    }

    if(senha.value !== confirmar.value){

        e.preventDefault();

        alert("As senhas não conferem.");

    }

});

</script>

</body>
</html>