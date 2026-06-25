<?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Adaptado para puxar da session que é gerada no validaLogin
    $idUsuario = $_SESSION['idLogin'];
    $nome      = $_POST['nNome'];

    include('funcoes.php');

    $diretorioImg = '';

    if(isset($_FILES['Foto']) && $_FILES['Foto']['tmp_name'] != ''){

        $ext       = pathinfo($_FILES['Foto']["name"], PATHINFO_EXTENSION);
        // time() no nome evita que o navegador mostre a foto antiga em cache
        $novo_nome = "foto-".$idUsuario."-".time().'.'.$ext;

        if(!is_dir('../dist/img/usuarios/')){
            mkdir('../dist/img/usuarios/', 0777, true);
        }
        $diretorio = '../dist/img/usuarios/';

        // Só grava no banco SE o arquivo foi realmente salvo na pasta
        if(move_uploaded_file($_FILES['Foto']['tmp_name'], $diretorio.$novo_nome)){
            $diretorioImg = 'dist/img/usuarios/'.$novo_nome;

            include('conexao.php');
            $sql = "UPDATE funcionario "
                    ." SET Foto = '".$diretorioImg."' "
                    ." WHERE idFuncionario = ".$idUsuario.";";
            $result = mysqli_query($conn,$sql);
            mysqli_close($conn);
        }
    }

    include('conexao.php');
    $sql = "UPDATE funcionario "
            ." SET Nome = '".$nome."' "
            ." WHERE idFuncionario = ".$idUsuario.";";
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    header('location: '.$_SERVER['HTTP_REFERER']);
?>