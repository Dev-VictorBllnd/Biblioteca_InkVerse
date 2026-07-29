<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Verificar Código</title>

<link rel="stylesheet" href="dist/css/verificarCodigo.css">
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


    <!-- LADO ESQUERDO -->

    <div class="lado-esquerdo">


        <div class="logo-area">

            <h1>InkVerse</h1>

        </div>


        <h2>Recuperação</h2>


        <p>
            Digite o código recebido
            para continuar.
        </p>


    </div>




    <!-- LADO DIREITO -->

    <div class="lado-direito">


        <div class="login-box">


            <h2>Verificar Código</h2>


            <p class="subtitulo">
                Informe o código enviado para seu e-mail.
            </p>



            <form action="validaCodigo.php" method="POST">


                <div class="campo">


                    <label>Código de verificação</label>


                    <input
                    type="text"
                    name="codigo"
                    maxlength="6"
                    required>


                </div>



                <button class="btn-login" type="submit">

                    Verificar

                </button>



            </form>


        </div>


    </div>


</div>


<!-- Modo escuro (paleta Claude + azul) -->
<script src="dist/js/dark-mode.js"></script>

</body>

</html>