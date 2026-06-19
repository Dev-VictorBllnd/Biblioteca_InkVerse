<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Ficheiros corretos e necessários para o InkVerse
include("funcaoMenu.php");
include("funcaoUsuario.php");
include("funcaoCliente.php");
    
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

// ... o resto das funções (proximoID, enviarEmail) permanecem iguais abaixo desta linha

function proximoID($tabela, $id){
    $prox = 0;

    $sql = "SELECT MAX($id) AS proximo FROM $tabela;";

    include('conexao.php');
    $result = mysqli_query($conn, $sql);
    mysqli_close($conn);

    if(mysqli_num_rows($result) > 0){

        foreach ($result as $coluna){
            $prox = $coluna['proximo'];
        }
    }

    return $prox + 1;
}

//Função que envia o e-mail com a nova senha para o usuário
function enviarEmail($email,$msg,$assunto,$nome){

    require '../PHPMailer/vendor/autoload.php';
	$mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            // Set mailer to use SMTP
        //$mail->SMTPDebug  = 3;                                    // Enable verbose debug output
        $mail->Host       = 'smtp.hostinger.com.br';                // Specify main and backup SMTP servers
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'sa@alphatechsolucoes.com.br';          // SMTP username
        $mail->Password   = 'Senai@2024';                           // SMTP password
        $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
        $mail->Port       = 465;                                    // TCP port to connect to    
        $mail->CharSet    = 'UTF-8';
        
        $mail->SMTPOptions = array(
            'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ));

        //Recipients
        $mail->setFrom('sa@alphatechsolucoes.com.br', 'SA - SENAI');
        $mail->addAddress($email, $nome);                   

        // Content
        $mail->isHTML(true);                                        // Set email format to HTML
        $mail->Subject = $assunto;
        $mail->Body    = $msg;
        $mail->AltBody = 'SA';

        //$mail->send();
        if ($mail->send()){
            header('location:../');
        }
        

    } catch (Exception $e) {

        //$_SESSION['msg-senha'] = $mail->ErrorInfo;
        $_SESSION['msg-senha'] = 'Houve uma falha no envio da nova senha. Verifique com seu administrador.';
        header('location: '.$_SERVER['HTTP_REFERER']);
    }
}

?>