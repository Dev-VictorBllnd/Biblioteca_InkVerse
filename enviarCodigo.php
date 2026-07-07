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

echo "

<script>

alert('Código enviado com sucesso.');

window.location='verificar-codigo.php';

</script>

";

exit();

?>