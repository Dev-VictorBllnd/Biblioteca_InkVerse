<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include("funcoes.php");
include("conexao.php");

// Limpa a sessão de login
$_SESSION['logado'] = 0;

$email = trim($_POST["nEmail"]);
$senha = trim($_POST["nSenha"]);

$sql = "SELECT * FROM funcionario
        WHERE Email = ?
        AND Senha = MD5(?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Erro na consulta: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $email, $senha);
mysqli_stmt_execute($stmt);

$resultLogin = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultLogin) > 0) {

    $usuario = mysqli_fetch_assoc($resultLogin);

    // Verifica se a conta está ativa
    if ($usuario['Ativo'] != 'S') {

        mysqli_stmt_close($stmt);
        mysqli_close($conn);

        echo "<script>
                alert('Sua conta está desativada. Entre em contato com o administrador.');
                window.location='../index.php';
              </script>";
        exit();
    }

    // Login autorizado
    $_SESSION['logado'] = 1;
    $_SESSION['idLogin'] = $usuario['idFuncionario'];
    $_SESSION['idCargo'] = $usuario['idCargo'];
    $_SESSION['NomeLogin'] = $usuario['Nome'];
    $_SESSION['FotoLogin'] = $usuario['Foto'];

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    header("Location: ../dashboard.php");
    exit();

} else {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    echo "<script>
            alert('E-mail ou senha incorretos.');
            window.location='../index.php';
          </script>";
    exit();
}
?>