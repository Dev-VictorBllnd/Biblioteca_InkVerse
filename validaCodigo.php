<?php
session_start();

require_once("php/conexao.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Verifica sessão
if (!isset($_SESSION['email']) || empty($_SESSION['email'])) {

    echo "<script>
            alert('Sessão expirada.');
            window.location='Esqueci-Senha.php';
          </script>";
    exit();

}


$email = $_SESSION['email'];



// Verifica código informado
if (!isset($_POST['codigo']) || trim($_POST['codigo']) == "") {

    echo "<script>
            alert('Digite o código recebido.');
            history.back();
          </script>";
    exit();

}


$codigo = trim($_POST['codigo']);



// Busca código e validade
$sql = "SELECT CodigoRecuperacao, ExpiraCodigo
        FROM funcionario
        WHERE Email = ?
        AND CodigoRecuperacao = ?
        AND ExpiraCodigo > NOW()";


$stmt = $conn->prepare($sql);


if(!$stmt){

    die("Erro SQL: ".$conn->error);

}



$stmt->bind_param("si",$email,$codigo);


$stmt->execute();


$resultado = $stmt->get_result();



// Se não encontrou
if($resultado->num_rows == 0){


    echo "<script>
            alert('Código inválido ou expirado.');
            window.location='verificar-codigo.php';
          </script>";

    exit();

}



// Código correto

$_SESSION['codigo_validado'] = true;


header("Location: Nova-senha.php");

exit();

?>