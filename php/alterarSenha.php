<?php
session_start();

require_once("conexao.php");

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| Função de Notificação
|--------------------------------------------------------------------------
*/
function notificacao($tipo, $titulo, $mensagem, $link = "", $textoBotao = "Continuar")
{
    $cor = "#198754";
    $icone = "✓";

    if ($tipo == "erro") {
        $cor = "#dc3545";
        $icone = "✖";
    }

    if ($tipo == "aviso") {
        $cor = "#ffc107";
        $icone = "!";
    }

    echo '
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>

        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>'.$titulo.'</title>

        <style>

            *{
                margin:0;
                padding:0;
                box-sizing:border-box;
                font-family:Arial, Helvetica, sans-serif;
            }

            body{
                min-height:100vh;
                display:flex;
                justify-content:center;
                align-items:center;
                background:linear-gradient(135deg,#0b1a2c,#1f4e79);
            }

            .card{

                width:430px;
                background:#fff;
                border-radius:18px;
                overflow:hidden;
                box-shadow:0 20px 45px rgba(0,0,0,.35);
                animation:aparecer .4s ease;

            }

            .topo{
                height:8px;
                background:'.$cor.';
            }

            .conteudo{
                padding:40px;
                text-align:center;
            }

            .icone{

                width:90px;
                height:90px;
                border-radius:50%;
                background:'.$cor.';
                color:#fff;
                margin:auto;
                display:flex;
                justify-content:center;
                align-items:center;
                font-size:42px;
                font-weight:bold;

            }

            h2{

                margin:25px 0 15px;
                color:#0b1a2c;

            }

            p{

                color:#666;
                line-height:26px;
                font-size:16px;

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

                background:#16395f;
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

        <div class="card">

            <div class="topo"></div>

            <div class="conteudo">

                <div class="icone">'.$icone.'</div>

                <h2>'.$titulo.'</h2>

                <p>'.$mensagem.'</p>

                <a href="'.$link.'" class="botao">'.$textoBotao.'</a>

            </div>

        </div>

    </body>

    </html>
    ';

    exit();
}

/*
|--------------------------------------------------------------------------
| Verifica acesso
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    notificacao(
        "erro",
        "Acesso Inválido",
        "Esta página não pode ser acessada diretamente.",
        "../index.php",
        "Voltar"
    );

}

/*
|--------------------------------------------------------------------------
| Verifica sessão
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['email']) ||
    empty($_SESSION['email']) ||
    !isset($_SESSION['codigo_validado'])
) {

    notificacao(
        "erro",
        "Sessão Expirada",
        "Faça novamente o processo de recuperação de senha.",
        "../Esqueci-Senha.php",
        "Recuperar Senha"
    );

}

$email = $_SESSION['email'];

$senha = trim($_POST['nSenha']);
$confirmar = trim($_POST['nConfirmarSenha']);

/*
|--------------------------------------------------------------------------
| Campos vazios
|--------------------------------------------------------------------------
*/

if (empty($senha) || empty($confirmar)) {

    notificacao(
        "aviso",
        "Campos Obrigatórios",
        "Preencha todos os campos para continuar.",
        "javascript:history.back()",
        "Voltar"
    );

}

/*
|--------------------------------------------------------------------------
| Senhas diferentes
|--------------------------------------------------------------------------
*/

if ($senha != $confirmar) {

    notificacao(
        "erro",
        "Senhas Diferentes",
        "As senhas digitadas não conferem.",
        "javascript:history.back()",
        "Voltar"
    );

}

/*
|--------------------------------------------------------------------------
| Criptografa senha
|--------------------------------------------------------------------------
*/

$senha = md5($senha);

/*
|--------------------------------------------------------------------------
| Atualiza senha
|--------------------------------------------------------------------------
*/

$sql = "UPDATE funcionario
        SET Senha=?,
            CodigoRecuperacao=NULL,
            ExpiraCodigo=NULL
        WHERE Email=?";

$stmt = $conn->prepare($sql);

if (!$stmt) {

    notificacao(
        "erro",
        "Erro no Banco",
        "Ocorreu um erro ao preparar a atualização.",
        "../index.php",
        "Voltar"
    );

}

$stmt->bind_param("ss", $senha, $email);

/*
|--------------------------------------------------------------------------
| Executa atualização
|--------------------------------------------------------------------------
*/

if ($stmt->execute()) {

    session_unset();
    session_destroy();

    notificacao(
        "sucesso",
        "Senha Alterada!",
        "Sua senha foi alterada com sucesso. Agora você pode entrar normalmente no sistema.",
        "../index.php",
        "Ir para Login"
    );

} else {

    notificacao(
        "erro",
        "Erro",
        "Não foi possível alterar sua senha. Tente novamente.",
        "javascript:history.back()",
        "Voltar"
    );

}

$stmt->close();
$conn->close();
?>