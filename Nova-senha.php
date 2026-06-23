

<!DOCTYPE html>

<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Senha - InkVerse</title>

 <link rel="stylesheet" href="dist/css/Novasenha.css">

</head>

<body class="tela-login">

    <!-- Lado esquerdo -->
    <div class="lado-esquerdo">

        <h2>Redefinir Senha</h2>

        <p>Crie uma nova senha para acessar sua conta.</p>

        <div class="logo-area">
            <img src="dist/img/logo.png" alt="Logo Biblioteca">
        </div>

        <h1>InkVerse</h1>

    </div>

    <!-- Lado direito -->
    <div class="lado-direito">

        <div class="login-box">

            <h2>Nova Senha</h2>

            <p class="subtitulo">
                Digite sua nova senha e confirme para continuar.
            </p>

            <form method="POST" action="php/alterarSenha.php">

                <div class="campo">
                    <label for="iNovaSenha">Nova Senha</label>

                    <input
                        type="password"
                        id="iNovaSenha"
                        name="nNovaSenha"
                        placeholder="Digite a nova senha"
                        required>
                </div>

                <div class="campo">
                    <label for="iConfirmarSenha">Confirmar Senha</label>

                    <input
                        type="password"
                        id="iConfirmarSenha"
                        name="nConfirmarSenha"
                        placeholder="Confirme a nova senha"
                        required>
                </div>

                <div class="mostrar-senha">
                    <input type="checkbox" id="mostrarSenha">
                    <label for="mostrarSenha">Mostrar senhas</label>
                </div>

                <button type="submit" class="btn-login">
                    Alterar Senha
                </button>

            </form>

        </div>

    </div>

    <script>
        const check = document.getElementById("mostrarSenha");
        const senha1 = document.getElementById("iNovaSenha");
        const senha2 = document.getElementById("iConfirmarSenha");

        check.addEventListener("change", function () {
            const tipo = this.checked ? "text" : "password";

            senha1.type = tipo;
            senha2.type = tipo;
        });
    </script>

</body>
</html>