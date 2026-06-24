<?php
    include('funcoes.php');

    // Captura os comandos da URL
    $funcao      = $_GET["funcao"] ?? '';
    $idUsuario   = $_GET["codigo"] ?? 0; // idFuncionario

    // Captura os dados do POST de forma segura (evita erro quando é apenas Delete)
    $tipoUsuario = $_POST["nTipoUsuario"] ?? null; 
    $nome        = $_POST["nNome"] ?? '';
    $login       = $_POST["nLogin"] ?? '';       
    $senha       = $_POST["nSenha"] ?? '';
    
    // CAMPOS DO INKVERSE:
    $cpf         = $_POST["nCpf"] ?? '';
    $datanasc    = $_POST["nDatanasc"]      ?? '';
    $telefone    = $_POST["nTelefone"] ?? '';
    $ativo       = $_POST["nAtivo"] ?? 'S'; // Se não vier nada, cadastra como 'S' (Ativo)

    include("conexao.php");

    if($funcao == "I"){
        // INSERÇÃO COM O CAMPO ATIVO
        $sql = "INSERT INTO funcionario (idCargo, Nome, Email, Senha, Cpf, Datanasc, Telefone, Ativo) "
              ." VALUES ("
              ."$tipoUsuario, "
              ."'$nome', "
              ."'$login', "
              ."md5('$senha'), "
              ."'$cpf', "
              ."'$datanasc', "
              ."'$telefone', "
              ."'$ativo');";
              
        $result = mysqli_query($conn, $sql);
        
        // CORREÇÃO CRÍTICA: Pega o ID que o banco de dados acabou de gerar para esse novo funcionário
        // Precisamos disso para que o código da foto (lá embaixo) saiba em qual ID salvar a imagem!
        $idUsuario = mysqli_insert_id($conn);

    } elseif($funcao == "A") {
        if($senha == ''){ 
            $setSenha = ''; 
        } else { 
            $setSenha = " Senha = md5('".$senha."'), ";
        }

        // ATUALIZAÇÃO COM O CAMPO ATIVO
        $sql = "UPDATE funcionario "
              ." SET idCargo = $tipoUsuario, "
              ." Nome = '$nome', "
              ." Email = '$login', "
              ." Cpf = '$cpf', "
              ." Datanasc = '$datanasc', "
              ." Telefone = '$telefone', "
              ." Ativo = '$ativo', "
              .$setSenha 
              ." idFuncionario = idFuncionario " // Truque técnico para gerir a vírgula
              ." WHERE idFuncionario = $idUsuario;";
              
        $result = mysqli_query($conn, $sql);

    } elseif($funcao == "D") {
        // EXCLUSÃO
        $sql = "DELETE FROM funcionario WHERE idFuncionario = $idUsuario;";
        $result = mysqli_query($conn, $sql);
    }

    mysqli_close($conn);

    // ==========================================
    // UPLOAD DA FOTO
    // ==========================================
    if(isset($_FILES['Foto']) && $_FILES['Foto']['tmp_name'] != ""){

        $extensao = pathinfo($_FILES['Foto']['name'], PATHINFO_EXTENSION);
        // Coloquei a função time() no nome para evitar que fotos com o mesmo nome se substituam
        $novoNome = md5(time().$_FILES['Foto']['name']).'.'.$extensao;        
        
        // Recomendo salvar as fotos numa subpasta específica para ficar organizado
        if(!is_dir('../dist/img/')){
            mkdir('../dist/img/', 0777, true);
        }
        $diretorio = '../dist/img/';

        move_uploaded_file($_FILES['Foto']['tmp_name'], $diretorio.$novoNome);
        $dirImagem = 'dist/img/'.$novoNome;

        include("conexao.php");
        
        $sql = "UPDATE funcionario "
              ." SET Foto = '$dirImagem' "
              ." WHERE idFuncionario = $idUsuario;";
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }

    header("location: ../funcionarios.php");
?>