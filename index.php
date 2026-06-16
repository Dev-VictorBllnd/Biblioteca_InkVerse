<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Biblioteca</title>

    <link rel="stylesheet" href="Login.css">

</head>

<body class="tela-login">

    <div class="login-box">

        <h2>Login</h2>

        <form method="POST" action="php/validaLogin.php">

            <p>
                <label for="iEmail">E-mail</label>
                <input
                    type="email"
                    id="iEmail"
                    name="nEmail"
                    placeholder="Digite seu e-mail"
                    required>
            </p>

            <p>
                <label for="iSenha">Senha</label>
                <input
                    type="password"
                    id="iSenha"
                    name="nSenha"
                    placeholder="Digite sua senha"
                    required>
            </p>

            <p class="mostrar-senha">
                <input
                    type="checkbox"
                    id="mostrarSenha">

                <label for="mostrarSenha">
                    Mostrar senha
                </label>
            </p>

            <p class="esqueci-senha">
                <a href="esqueci-senha.php">
                    Esqueci minha senha
                </a>
            </p>

            <button
                type="submit"
                class="btn-login">
                Entrar
            </button>

        </form>

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