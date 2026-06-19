<?php
    include('funcoes.php');

    // Captura os comandos da URL
    $funcao      = $_GET["funcao"] ?? '';
    $idCliente   = $_GET["codigo"] ?? 0; // idCliente

    // Captura os dados do POST de forma segura (evita erro quando é apenas Delete)
    $nome        = $_POST["nNome"] ?? '';
    $email       = $_POST["nEmail"] ?? '';       
    
    // CAMPOS DO INKVERSE:
    $cpf         = $_POST["nCpf"] ?? '';
    $datanasc    = $_POST["nDatanasc"] ?? '';
    $telefone    = $_POST["nTelefone"] ?? '';
    $ativo       = $_POST["nAtivo"] ?? 'S'; // Se não vier nada, cadastra como 'S' (Ativo)

    include("conexao.php");

    if($funcao == "I"){
        // INSERÇÃO COM O CAMPO ATIVO
        $sql = "INSERT INTO cliente (Nome, Email, Cpf, Datanasc, Telefone, Ativo) "
              ." VALUES ("
              ."'$nome', "
              ."'$email', "
              ."'$cpf', "
              ."'$datanasc', "
              ."'$telefone', "
              ."'$ativo');";
        $result = mysqli_query($conn, $sql);    
        
        // CORREÇÃO CRÍTICA: Pega o ID que o banco de dados acabou de gerar para esse novo funcionário
        // Precisamos disso para que o código da foto (lá embaixo) saiba em qual ID salvar a imagem!
        $idCliente = mysqli_insert_id($conn);

    } elseif($funcao == "A") {
        // ATUALIZAÇÃO COM O CAMPO ATIVO
        $sql = "UPDATE cliente "
              ." SET Nome = '$nome', "
              ." Email = '$email', "
              ." Cpf = '$cpf', "
              ." Datanasc = '$datanasc', "
              ." Telefone = '$telefone', "
              ." Ativo = '$ativo', "
              .$setSenha 
              ." idCliente = idCliente " // Truque técnico para gerir a vírgula
              ." WHERE idCliente = $idCliente;";
              
        $result = mysqli_query($conn, $sql);

    } elseif($funcao == "D") {
        // EXCLUSÃO
        $sql = "DELETE FROM cliente WHERE idCliente = $idCliente;";
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
        if(!is_dir('../dist/img/clientes/')){
            mkdir('../dist/img/clientes/', 0777, true);
        }
        $diretorio = '../dist/img/clientes/';

        move_uploaded_file($_FILES['Foto']['tmp_name'], $diretorio.$novoNome);
        $dirImagem = 'dist/img/clientes/'.$novoNome;

        include("conexao.php");
        
        $sql = "UPDATE cliente "
              ." SET Foto = '$dirImagem' "
              ." WHERE idCliente = $idCliente;";
        $result = mysqli_query($conn,$sql);
        mysqli_close($conn);
    }

    header("location: ../clientes.php");
?>