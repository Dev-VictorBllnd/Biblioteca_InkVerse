<!DOCTYPE html>
<html lang="pt-br">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Verificar Código</title>

<link rel="stylesheet" href="css/verificarCodigo.css">

</head>


<body>


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
                    placeholder="Digite o código"
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


</body>

</html>