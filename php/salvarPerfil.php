<?php
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $idUsuario = $_SESSION['idLogin'];
    
    // Capturando os novos campos que agora são editáveis
    $nome      = $_POST['nNome'] ?? '';
    $email     = $_POST['nEmail'] ?? '';
    $cpf       = $_POST['nCpf'] ?? '';
    $telefone  = $_POST['nTelefone'] ?? '';
    $datanasc  = $_POST['nDatanasc'] ?? '';
    
    // Capturando as senhas
    $novaSenha      = $_POST['nNovaSenha'] ?? '';
    $confirmarSenha = $_POST['nConfirmarSenha'] ?? '';

    include('funcoes.php');
    $diretorioImg = '';

    // ==========================================
    // ATUALIZAÇÃO DA FOTO
    // ==========================================
    if(isset($_FILES['Foto']) && $_FILES['Foto']['tmp_name'] != ''){

        $ext       = pathinfo($_FILES['Foto']["name"], PATHINFO_EXTENSION);
        $novo_nome = "foto-".$idUsuario."-".time().'.'.$ext;

        if(!is_dir('../dist/img/usuarios/')){
            mkdir('../dist/img/usuarios/', 0777, true);
        }
        $diretorio = '../dist/img/usuarios/';

        if(move_uploaded_file($_FILES['Foto']['tmp_name'], $diretorio.$novo_nome)){
            $diretorioImg = 'dist/img/usuarios/'.$novo_nome;

            include('conexao.php');
            $sql = "UPDATE funcionario SET Foto = '".$diretorioImg."' WHERE idFuncionario = ".$idUsuario.";";
            $result = mysqli_query($conn,$sql);
            mysqli_close($conn);
            
            $_SESSION['FotoLogin'] = $diretorioImg;
        }
    }

    // ==========================================
    // ATUALIZAÇÃO DOS DADOS (Nome, Email, CPF, DataNasc, Telefone e Senha)
    // ==========================================
    include('conexao.php');
    
    // Inicia a query com os campos normais
    $sql = "UPDATE funcionario 
            SET Nome = '$nome', 
                Email = '$email', 
                Cpf = '$cpf', 
                Telefone = '$telefone', 
                Datanasc = '$datanasc' ";

    // Verifica se o usuário quis alterar a senha (se o campo não está vazio)
    if (!empty($novaSenha)) {
        if ($novaSenha === $confirmarSenha) {
            $senhaCriptografada = md5($novaSenha);
            $sql .= ", Senha = '$senhaCriptografada' ";
        } else {
            mysqli_close($conn);
            header("location: ../perfil.php?erro=senha_diferente");
            exit;
        }
    }

    // Finaliza a query
    $sql .= " WHERE idFuncionario = $idUsuario;";
    
    $result = mysqli_query($conn, $sql);
    mysqli_close($conn);

    if ($result) {
        // Atualiza o nome na sessão para que mude no canto superior instantaneamente
        $_SESSION['NomeLogin'] = $nome; 
    }

    // Retorna para a tela de perfil
    header('location: '.$_SERVER['HTTP_REFERER']);
?>