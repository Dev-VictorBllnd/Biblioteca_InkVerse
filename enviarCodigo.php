<?php
session_start();

require_once("php/conexao.php");
require_once("php/funcoes.php");

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
| Busca o nome do funcionário (para personalizar o e-mail)
|--------------------------------------------------------------------------
*/

$stmtNome = $conn->prepare("SELECT Nome FROM funcionario WHERE Email=?");
$stmtNome->bind_param("s", $email);
$stmtNome->execute();
$dadosFuncionario = $stmtNome->get_result()->fetch_assoc();
$nomeFuncionario  = $dadosFuncionario['Nome'] ?? '';



/*
|--------------------------------------------------------------------------
| Envia o código por e-mail (PHPMailer)
|--------------------------------------------------------------------------
*/

$assunto = 'Código de Recuperação de Senha - InkVerse';

$msg = "
    <h3>Olá, {$nomeFuncionario}!</h3>
    <p>Recebemos um pedido para redefinir a sua senha no sistema InkVerse.</p>
    <p>Use o código abaixo para continuar (válido por 15 minutos):</p>
    <p style='font-size:24px; font-weight:bold; letter-spacing:4px;'>{$codigo}</p>
    <p><small>Se não foi você quem solicitou, ignore este e-mail.</small></p>
";

$enviado = enviarEmail($email, $msg, $assunto, $nomeFuncionario);



/*
|--------------------------------------------------------------------------
| Redireciona para validar código
|--------------------------------------------------------------------------
*/

if ($enviado) {

    echo "
    <script>
    alert('Código enviado com sucesso para o seu e-mail.');
    window.location='verificar-codigo.php';
    </script>
    ";

} else {

    echo "
    <script>
    alert('Não foi possível enviar o e-mail com o código. Tente novamente mais tarde.');
    window.location='Esqueci-Senha.php';
    </script>
    ";

}

exit();

?>