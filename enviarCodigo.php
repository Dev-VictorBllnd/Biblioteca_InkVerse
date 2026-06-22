<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Senha/codigo</title>

<link rel="stylesheet" href="dist/css/enviar-Codigo.css">

</head>
<body> 
    <div class="tela-codigo">

    <div class="lado-esquerdo">

    <h2>Seja Bem-vindo!</h2>

    <p>Faça login para acessar a biblioteca.</p>

    <div class="logo-area">
        <img src="dist/img/logo.png" alt="Logo Biblioteca">
    </div>

<h1>InkVerse</h1>

</div>

    <div class="lado-direito">

        <div class="codigo-box">

            <h2>Verificar Código</h2>

            <p class="subtitulo">
                Informe o código recebido para continuar.
            </p>

            <form method="POST">

                <div class="campo">
                    <label>Código</label>
                    <input type="text" name="codigo" maxlength="6" required placeholder="digite o código de recuperação">
                </div>

                <button type="submit" class="btn-verificar">
                    Verificar
                </button>

            </form>

        </div>

    </div>

</div>
</body>