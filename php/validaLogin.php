<?php
    if(session_status() !== PHP_SESSION_ACTIVE){
        session_start();
    }

    include("funcoes.php");

    $_SESSION['logado'] = 0;

    $email = stripslashes($_POST["nEmail"]);
    $senha = stripslashes($_POST["nSenha"]);

    //$_POST - Valor enviado pelo FORM através da propriedade NAME do elemento HTML 
    //$_GET - Valor enviado pelo FORM através da URL
    //$_SESSION - Variável criada pelo usuário no PHP

    include("conexao.php");
    $sql = "SELECT * FROM usuarios "
            ." WHERE login = '$email' "
            ." AND senha = md5('$senha');";
    $resultLogin = mysqli_query($conn,$sql);
    mysqli_close($conn);

    //Validar se tem retorno do BD
    if (mysqli_num_rows($resultLogin) > 0) {  
        
        //enviarEmail('destino@email.com.br','Mensagem de e-mail para SA','Teste SA','Eu mesmo');

        foreach ($resultLogin as $coluna) {
                        
            //***Verificar os dados da consulta SQL
            $_SESSION['idTipoUsuario'] = $coluna['idTipoUsuario'];
            $_SESSION['logado']        = 1;
            $_SESSION['idLogin']       = $coluna['idUsuario'];
            $_SESSION['NomeLogin']     = $coluna['Nome'];
            $_SESSION['FotoLogin']     = $coluna['Foto'];
            $_SESSION['AtivoLogin']    = $coluna['FlgAtivo'];

            //Acessar a tela inicial
            header('location: ../painel.php');
            
        }        
    }else{
        //Acessar a tela inicial
        header('location: ../');
    } 

    

?>