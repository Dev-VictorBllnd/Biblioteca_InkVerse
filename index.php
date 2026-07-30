<?php
// Impede que o navegador guarde esta página em cache com os campos
// preenchidos (evita que a senha digitada reapareça ao voltar/deslogar)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Biblioteca</title>

    <link rel="stylesheet" href="dist/css/Login-index.css">
    <!-- Font Awesome (ícones sol/lua) -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Modo escuro (paleta Claude) -->
    <link rel="stylesheet" href="dist/css/login-dark-claude.css">

    <!-- Aplica o modo escuro ANTES da página renderizar, evitando o "flash" de tela clara -->
    <script>
        (function () {
            if (localStorage.getItem('inkverse-dark-mode') === '1') {
                document.documentElement.classList.add('dark-mode');
            }
        })();
    </script>

</head>

<body class="tela-login">

    <a href="#" id="toggle-dark-mode-login" title="Alternar modo escuro">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </a>

    <!-- Lado esquerdo -->
    <div class="lado-esquerdo">

        <h2>Seja Bem-vindo!</h2>

        <p>Faça login para acessar a biblioteca.</p>

        <div class="logo-area">
            <img src="dist/img/logo.png" alt="Logo Biblioteca">
        </div>

        <h1>InkVerse</h1>

    </div>

    <!-- Lado direito -->
    <div class="lado-direito">

        <div class="login-box">

            <h2>Login</h2>

            <p class="subtitulo">
                Entre com seu usuário e senha para acessar o sistema.
            </p>

            <form method="POST" action="php/validaLogin.php">

                <div class="campo">
                    <label for="iEmail">Usuário</label>

                    <input
                        type="email"
                        id="iEmail"
                        name="nEmail"
                        placeholder="Digite seu usuário"
                        required>
                </div>

                <div class="campo">
                    <label for="iSenha">Senha</label>

                    <input
                        type="password"
                        id="iSenha"
                        name="nSenha"
                        placeholder="Digite sua senha"
                        autocomplete="new-password"
                        required>
                </div>

                <div class="mostrar-senha">
                    <input type="checkbox" id="mostrarSenha">
                    <label for="mostrarSenha">Mostrar senha</label>
                </div>

                <div class="opcoes">
                    <a href="esqueci-senha.php">
                        Esqueci minha senha
                    </a>
                </div>

                <button type="submit" class="btn-login">
                    Entrar
                </button>

                <a href="acervo.php" class="btn-login">
                    Visualizar Livros
                </a>

            </form>

        </div>

    </div>

    <script>
        const check = document.getElementById("mostrarSenha");
        const senha = document.getElementById("iSenha");

        check.addEventListener("change", function () {
            senha.type = this.checked ? "text" : "password";
        });

        // Reforço de segurança: se o navegador ainda assim restaurar esta
        // página do cache local (voltar/avançar), limpa a senha digitada
        // e desmarca "Mostrar senha", em vez de deixá-la visível no campo.
        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                senha.value = "";
                senha.type = "password";
                if (check) check.checked = false;
            }
        });
    </script>

    <!-- Modo escuro (paleta Claude) -->
    <script src="dist/js/dark-mode.js"></script>

</body>
</html>