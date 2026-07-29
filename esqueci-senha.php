<!DOCTYPE html>
<html lang="pt-br">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Recuperar Senha</title>

    <link rel="stylesheet" href="dist/css/esqueci-Senha.css">
    <!-- Font Awesome (ícones sol/lua) -->
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <!-- Modo escuro (paleta Claude + azul) -->
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
<body>

    <a href="#" id="toggle-dark-mode-login" title="Alternar modo escuro">
        <i class="fas fa-moon"></i>
        <i class="fas fa-sun"></i>
    </a>

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

<!-- Modo escuro (paleta Claude + azul) -->
<script src="dist/js/dark-mode.js"></script>

</body>
</html>