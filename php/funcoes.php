<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Ficheiros corretos e necessários para o InkVerse
include("funcaoMenu.php");
include("funcaoFuncionario.php");
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

//Função que envia o e-mail (ex: código de recuperação de senha) para o usuário
//Retorna true se o e-mail foi enviado com sucesso, ou false se houve falha
function enviarEmail($email,$msg,$assunto,$nome){

    require_once __DIR__ . '/../libs/PHPMailer/Exception.php';
    require_once __DIR__ . '/../libs/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/../libs/PHPMailer/SMTP.php';
	$mail = new PHPMailer(true);

    try {
        //Server settings
        $mail->isSMTP();                                            // Set mailer to use SMTP
        //$mail->SMTPDebug  = 3;                                    // Enable verbose debug output (usar apenas para testes)
        $mail->Host       = 'smtp.gmail.com';                       // Servidor SMTP do Gmail
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = 'inkversebiblioteca@gmail.com';         // TROCAR: e-mail da empresa criado no Gmail
        $mail->Password   = 'uqlqgoonwafzemkz';                     // TROCAR: senha de app de 16 dígitos (sem espaços, sem aspas)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Gmail usa STARTTLS
        $mail->Port       = 587;                                    // Porta padrão do Gmail com STARTTLS
        $mail->CharSet    = 'UTF-8';

        //Recipients
        $mail->setFrom('inkversebiblioteca@gmail.com', 'InkVerse'); // TROCAR: mesmo e-mail da empresa
        $mail->addAddress($email, $nome);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $assunto;
        $mail->Body    = $msg;
        $mail->AltBody = strip_tags($msg);

        $mail->send();
        return true;

    } catch (Exception $e) {

        //Para depurar o erro real durante os testes, descomente a linha abaixo:
        //$_SESSION['msg-senha'] = $mail->ErrorInfo;
        $_SESSION['msg-senha'] = 'Houve uma falha no envio do e-mail. Verifique com seu administrador.';
        return false;
    }
}

?>