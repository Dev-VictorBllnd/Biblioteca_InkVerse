<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Biblioteca</title>

    <link rel="stylesheet" href="dist/css/login-index.css">

</head>

<body class="tela-login">

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

                <a href="livros.php" class="btn-login">
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
    </script>

</body>
</html>