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

$primeiroNome = trim(explode(' ', trim($nomeFuncionario))[0] ?? '');
$saudacaoNome = $primeiroNome !== '' ? htmlspecialchars($primeiroNome, ENT_QUOTES, 'UTF-8') : 'olá';
$codigoFormatado = implode(' ', str_split((string)$codigo));

$msg = "
<!DOCTYPE html>
<html lang='pt-BR'>
<head>
<meta charset='UTF-8'>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<title>{$assunto}</title>
</head>
<body style='margin:0; padding:0; background-color:#f2f1ef; font-family: Arial, Helvetica, sans-serif;'>
  <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color:#f2f1ef; padding:32px 16px;'>
    <tr>
      <td align='center'>
        <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='max-width:480px; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.06);'>

          <!-- Cabeçalho -->
          <tr>
            <td align='center' style='background-color:#1f1b2e; padding:28px 24px;'>
              <img src='cid:logoInkVerse' alt='InkVerse' height='40' style='display:block; height:40px; width:auto;'>
            </td>
          </tr>

          <!-- Conteúdo -->
          <tr>
            <td style='padding:36px 32px 8px 32px;'>
              <p style='margin:0 0 4px 0; font-size:13px; letter-spacing:1px; text-transform:uppercase; color:#8a5cf6; font-weight:bold;'>Recuperação de senha</p>
              <h1 style='margin:0 0 16px 0; font-size:22px; color:#1f1b2e;'>Olá, {$saudacaoNome}!</h1>
              <p style='margin:0 0 20px 0; font-size:15px; line-height:1.6; color:#4a4a4a;'>
                Recebemos um pedido para redefinir a sua senha no sistema <strong>InkVerse</strong>.
                Use o código abaixo para continuar:
              </p>
            </td>
          </tr>

          <!-- Código -->
          <tr>
            <td align='center' style='padding:0 32px 24px 32px;'>
              <div style='background-color:#f6f3ff; border:1px solid #e2d9ff; border-radius:10px; padding:18px 24px; display:inline-block;'>
                <span style='font-size:30px; font-weight:bold; letter-spacing:8px; color:#1f1b2e;'>{$codigoFormatado}</span>
              </div>
              <p style='margin:14px 0 0 0; font-size:13px; color:#8a8a8a;'>Este código é válido por <strong>15 minutos</strong>.</p>
            </td>
          </tr>

          <!-- Aviso de segurança -->
          <tr>
            <td style='padding:0 32px 32px 32px;'>
              <table role='presentation' width='100%' cellpadding='0' cellspacing='0' style='background-color:#fff8ec; border-radius:8px; border:1px solid #f2e2bd;'>
                <tr>
                  <td style='padding:14px 16px; font-size:13px; line-height:1.5; color:#7a6a3f;'>
                    Se você não solicitou essa alteração, ignore este e-mail — sua senha permanecerá a mesma.
                    Não compartilhe este código com ninguém, nem mesmo com a equipe InkVerse.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Rodapé -->
          <tr>
            <td align='center' style='background-color:#faf9f8; padding:20px 24px; border-top:1px solid #ececec;'>
              <p style='margin:0; font-size:12px; color:#9a9a9a;'>Este é um e-mail automático, por favor não responda.</p>
              <p style='margin:4px 0 0 0; font-size:12px; color:#9a9a9a;'>&copy; " . date('Y') . " InkVerse. Todos os direitos reservados.</p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
";

$altBody = "Olá, {$saudacaoNome}!\n\n"
    . "Recebemos um pedido para redefinir a sua senha no sistema InkVerse.\n"
    . "Use o código abaixo para continuar (válido por 15 minutos):\n\n"
    . "{$codigoFormatado}\n\n"
    . "Se não foi você quem solicitou, ignore este e-mail.";

$enviado = enviarEmail($email, $msg, $assunto, $nomeFuncionario, $altBody);



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