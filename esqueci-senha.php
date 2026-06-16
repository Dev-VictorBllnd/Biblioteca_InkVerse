<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Esqueci Minha Senha</title>

<link rel="stylesheet" href="dist/css/esqueciSenha.css">

</head>
<body class="tela-login">

<div class="login-box">

    <h2>Recuperar Senha</h2>

    <form action="enviarCodigo.php" method="POST">

        <p>
            <label for="email">E-mail</label>
            <input
                type="email"
                id="email"
                name="email"
                placeholder="Digite seu e-mail"
                required>
        </p>

        <button type="submit" class="btn-login">
            Enviar Código
        </button>

    </form>

    <a href="index.php" class="voltar">
        Voltar ao Login
    </a>

</div>

</body>
</html>