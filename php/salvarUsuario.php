<?php

    include('funcoes.php');

    $tipoUsuario = $_POST["nTipoUsuario"]; // Agora reflete idCargo
    $nome        = $_POST["nNome"];
    $login       = $_POST["nLogin"]; // Agora reflete Email
    $senha       = $_POST["nSenha"];
    $funcao      = $_GET["funcao"];
    $idUsuario   = $_GET["codigo"]; // Trata-se do idFuncionario

    include("conexao.php");

    if($funcao == "I"){

        // O idFuncionario é AUTO_INCREMENT no BD novo, não precisamos passar ele.
        // Cpf, Datanasc e Telefone receberão valores fictícios temporários para não dar erro.
        $sql = "INSERT INTO funcionario (idCargo, Nome, Email, Senha, Cpf, Datanasc, Telefone) "
                ." VALUES ("
                .$tipoUsuario.", "
                ."'$nome', "
                ."'$login', "
                ."md5('$senha'), "
                ."'00000000000', " // Valor obrigatório BD
                ."'2000-01-01', "  // Valor obrigatório BD
                ."'0000000000');"; // Valor obrigatório BD

    }elseif($funcao == "A"){
        if($senha == ''){ 
            $setSenha = ''; 
        }else{ 
            $setSenha = " Senha = md5('".$senha."'), ";
        }

        // Removida a atualização do FlgAtivo
        $sql = "UPDATE funcionario "
                ." SET idCargo = $tipoUsuario, "
                    ." Nome = '$nome', "
                    ." Email = '$login', "
                    .$setSenha 
                    ." idFuncionario = idFuncionario " // Manobra para manter a vírgula do $setSenha correta
                ." WHERE idFuncionario = $idUsuario;";

    }elseif($funcao == "D"){
        $sql = "DELETE FROM funcionario "
                ." WHERE idFuncionario = $idUsuario;";
    }

    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    //VERIFICA SE TEM IMAGEM NO INPUT
    if($_FILES['Foto']['tmp_name'] != ""){

        $extensao = pathinfo($_FILES['Foto']['name'], PATHINFO_EXTENSION);
        $novoNome = md5($_FILES['Foto']['name']).'.'.$extensao;        
        
        if(is_dir('../dist/img/')){
            $diretorio = '../dist/img/';
        }else{
            $diretorio = mkdir('../dist/img/');
        }

        move_uploaded_file($_FILES['Foto']['tmp_name'], $diretorio.$novoNome);
        $dirImagem = 'dist/img/'.$novoNome;

        include("conexao.php");
        
        $sql = "UPDATE funcionario "
                ." SET Foto = '$dirImagem' "
                ." WHERE idFuncionario = $idUsuario;";
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }

    header("location: ../usuarios.php");

?>