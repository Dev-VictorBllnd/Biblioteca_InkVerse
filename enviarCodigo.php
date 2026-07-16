<?php
session_start();


require_once("php/conexao.php");

error_reporting(E_ALL);
ini_set("display_errors",1);



/*
|--------------------------------------------------------------------------
| Recebe o e-mail
|--------------------------------------------------------------------------
*/

if(isset($_POST["email"])){

    $email = trim($_POST["email"]);


    if(empty($email)){

        echo "
        <script>
        alert('Informe o e-mail.');
        window.location='Esqueci-Senha.php';
        </script>";
         exit();

    }


    $sql = "SELECT * FROM funcionario WHERE Email=?";


    $stmt = $conn->prepare($sql);


    if(!$stmt){

        die("Erro SQL: ".$conn->error);

    }


    $stmt->bind_param("s",$email);

    $stmt->execute();


    $resultado = $stmt->get_result();



    if($resultado->num_rows == 0){


        echo "
        <script>
        alert('E-mail não encontrado.');
        window.location='Esqueci-Senha.php';
        </script>";

        exit();

    }


    $_SESSION["email"] = $email;


}else{


    if(!isset($_SESSION["email"])){

        echo "
        <script>
        alert('Sessão inválida.');
        window.location='Esqueci-Senha.php';
        </script>";

        exit();

    }


    $email = $_SESSION["email"];

}



/*
|--------------------------------------------------------------------------
| Gera código de recuperação
|--------------------------------------------------------------------------
*/


$codigo = random_int(100000,999999);



$_SESSION["codigo_recuperacao"] = $codigo;



/*
|--------------------------------------------------------------------------
| Salva no banco
|--------------------------------------------------------------------------
*/


$sql = "UPDATE funcionario
SET CodigoRecuperacao=?,
ExpiraCodigo=DATE_ADD(NOW(),INTERVAL 15 MINUTE)
WHERE Email=?";



$stmt = $conn->prepare($sql);



if(!$stmt){

    die("Erro UPDATE: ".$conn->error);

}



$stmt->bind_param("is",$codigo,$email);



if(!$stmt->execute()){

    die("Erro ao salvar código: ".$stmt->error);

}



/*
|--------------------------------------------------------------------------
| Redireciona para validar código
|--------------------------------------------------------------------------
*/

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Código Enviado</title>

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial, Helvetica, sans-serif;
}

body{
    background:linear-gradient(135deg,#0b1a2c,#16395e);
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}

.notificacao{

    width:430px;
    background:#fff;
    border-radius:15px;
    overflow:hidden;
    box-shadow:0 15px 40px rgba(0,0,0,.35);
    animation:aparecer .4s;

}

.topo{
    height:8px;
    background:#198754;
}

.conteudo{

    padding:40px;
    text-align:center;

}

.icone{

    width:90px;
    height:90px;
    background:#198754;
    color:#fff;
    border-radius:50%;
    margin:auto;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:45px;

}

h2{

    color:#0b1a2c;
    margin-top:25px;
    margin-bottom:15px;

}

p{

    color:#666;
    font-size:16px;
    line-height:26px;

}

.botao{

    display:inline-block;
    margin-top:30px;
    padding:14px 35px;
    background:#0b1a2c;
    color:#fff;
    text-decoration:none;
    border-radius:8px;
    transition:.3s;

}

.botao:hover{

    background:#16395e;
    transform:translateY(-2px);

}

@keyframes aparecer{

    from{
        opacity:0;
        transform:translateY(-30px);
    }

    to{
        opacity:1;
        transform:translateY(0);
    }

}

</style>

</head>

<body>

<div class="notificacao">

<div class="topo"></div>

<div class="conteudo">

<div class="icone">
✓
</div>

<h2>Código Enviado!</h2>

<p>
O código de recuperação foi enviado com sucesso para seu e-mail.
Agora clique em <strong>Continuar</strong> para informar o código recebido.
</p>

<a href="verificar-codigo.php" class="botao">
Continuar
</a>

</div>

</div>

</body>
</html>
<?php
exit();

?>