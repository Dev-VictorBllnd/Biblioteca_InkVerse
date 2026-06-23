<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha - Código</title>

<link rel="stylesheet" href="dist/css/Enviar-Codigo.css">

</head>
<body>

<div class="tela-codigo">

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

        <div class="codigo-box">

            <h2>Verificar Código</h2>

            <p class="subtitulo">
                Informe o código recebido para continuar.
            </p>

            <form method="POST" action="nova-senha.php">

                <div class="campo">
                    <label for="codigo">Código</label>

                    <input
                        type="text"
                        id="codigo"
                        name="codigo"
                        maxlength="6"
                        required
                        placeholder="Digite o código de recuperação">
                </div>

                <button type="submit" class="btn-verificar">
                    Verificar
                </button>

            </form>

        </div>

    </div>

</div>

</body>
</html>