<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha</title>

<link rel="stylesheet" href="dist/css/esqueci-Senha.css">

</head>
<body>

<div class="tela-login">

    <div class="lado-esquerdo">

        <h1>Biblioteca</h1>

        <h2>Recuperação de Senha</h2>

        <p>
            Informe seu e-mail para receber o código de verificação.
        </p>

        <div class="logo-area">
            <img src="dist/img/logo.1.png" alt="Logo Biblioteca">
        </div>

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