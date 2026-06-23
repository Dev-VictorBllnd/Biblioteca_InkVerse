<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha</title>

<link rel="stylesheet" href="dist/css/Esqueci-Senha.css">

</head>
<body>

    <div class="tela-login">

    <div class="lado-esquerdo">

    <h2>Seja Bem-vindo!</h2>

    <p>Faça login para acessar a biblioteca.</p>

    <div class="logo-area">
        <img src="dist/img/logo.png" alt="Logo Biblioteca">
    </div>

<h1>InkVerse</h1>

</div>

    <div class="lado-direito">

        <div class="login-box">

            <h2>Recuperar Senha</h2>

            <p class="subtitulo">
                Digite seu e-mail para receber o código de recuperação.
            </p>

            <form action="enviarCodigo.php" method="POST">

                <div class="campo">
                    <label for="email">E-mail</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Digite seu e-mail"
                        required>
                </div>

                <button type="submit" class="btn-login">
                    Enviar Código
                </button>

            </form>

            <div class="cadastro">
                <a href="index.php">Voltar ao Login</a>
            </div>

        </div>

    </div>

</div>

</body>
</html>