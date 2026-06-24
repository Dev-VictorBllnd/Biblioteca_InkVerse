<?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Adaptado para puxar da session que é gerada no validaLogin
    $idUsuario = $_SESSION['idLogin']; 
    $nome      = $_POST['nNome'];

    include('funcoes.php');

    $diretorioImg = '';
    
    if($_FILES['Foto']['tmp_name'] != ''){
        
        $ext       = pathinfo($_FILES['Foto']["name"], PATHINFO_EXTENSION);
        $novo_nome = "foto-".$idUsuario.'.'.$ext;
    
        if(is_dir('../dist/img/')){ 
            $diretorio = '../dist/img/';
        }else{
            $diretorio = mkdir('../dist/img/');
        }
      
        move_uploaded_file($_FILES['Foto']['tmp_name'], $diretorio.$novo_nome);
    
        $diretorioImg = 'dist/img/'.$novo_nome;

        include('conexao.php');
        $sql = "UPDATE funcionario "
                ." SET Foto = '".$diretorioImg."' "
                ." WHERE idFuncionario = ".$idUsuario.";";                                 
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }
    
    include('conexao.php');
    $sql = "UPDATE funcionario "
            ." SET Nome = '".$nome."' "
            ." WHERE idFuncionario = ".$idUsuario.";";                                 
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    header('location: '.$_SERVER['HTTP_REFERER']);

?>