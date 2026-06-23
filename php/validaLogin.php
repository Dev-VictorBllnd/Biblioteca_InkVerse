<?php
    if(session_status() !== PHP_SESSION_ACTIVE){
        session_start();
    }

    include("funcoes.php");

    $_SESSION['logado'] = 0;

    $email = stripslashes($_POST["nEmail"]);
    $senha = stripslashes($_POST["nSenha"]);

    include("conexao.php");
    
    $sql = "SELECT * FROM funcionario "
            ." WHERE Email = '$email' "
            ." AND Senha = md5('$senha');";
            
    $resultLogin = mysqli_query($conn, $sql);
    mysqli_close($conn);

    if (mysqli_num_rows($resultLogin) > 0) {  
        
        foreach ($resultLogin as $coluna) {
                        
            $_SESSION['idCargo']       = $coluna['idCargo'];
            $_SESSION['logado']        = 1;
            $_SESSION['idLogin']       = $coluna['idFuncionario']; 
            $_SESSION['NomeLogin']     = $coluna['Nome'];
            $_SESSION['FotoLogin']     = $coluna['Foto'];
            header('location: ../dashboard.php');
        }        
    } else {
        header('location: ../');
    } 
?>