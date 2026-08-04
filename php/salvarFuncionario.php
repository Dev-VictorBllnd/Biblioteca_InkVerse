<?php
    // Inicia a sessão e garante que só um usuário logado acesse este script.
    // Como a guarda já bloqueia quem não está logado, idLogin sempre existe
    // aqui — por isso não usamos mais o "?? 1" (que assumia o funcionário
    // de ID 1 como fallback perigoso quando não havia sessão).
    include('autenticacao.php');
    $idSessaoAtiva = $_SESSION['idLogin'];

    include('funcoes.php');

    $funcao      = $_GET["funcao"] ?? '';
    $idUsuario   = $_GET["codigo"] ?? 0;

    // ==========================================
    // BLOQUEIO DE AUTOEXCLUSÃO (Segurança Backend)
    // ==========================================
    if($funcao == "D" && $idUsuario == $idSessaoAtiva) {
        // Redireciona com um erro caso o usuário tente forçar a exclusão da própria conta pela URL
        header("location: ../funcionarios.php?erro=auto_exclusao");
        exit;
    }
    // ==========================================

    $tipoUsuario = $_POST["nTipoUsuario"] ?? null; 
    $nome        = $_POST["nNome"] ?? '';
    $login       = $_POST["nLogin"] ?? '';       
    $senha       = $_POST["nSenha"] ?? '';
    
    $cpf         = $_POST["nCpf"] ?? '';
    $datanasc    = $_POST["nDatanasc"]      ?? '';
    $telefone    = $_POST["nTelefone"] ?? '';
    $ativo       = $_POST["nAtivo"] ?? 'S';

    // Defesa extra no servidor: garante que o CPF seja gravado só com dígitos
    // mesmo que, por algum motivo, chegue formatado (ex.: JS desabilitado no navegador).
    $cpf = preg_replace('/\D/', '', $cpf);

    include("conexao.php");

    // ==========================================
    // VERIFICAÇÃO DE CPF DUPLICADO
    // ==========================================
    if($funcao == "I" || $funcao == "A"){
        
        // Se for atualização ("A"), precisamos ignorar o CPF do próprio usuário que está sendo editado ARRUMADO
        $filtroProprioUsuario = "";
        if($funcao == "A") {
            $filtroProprioUsuario = " AND idFuncionario != $idUsuario";
        }

        // Remove pontos e traço também do lado do banco antes de comparar:
        // assim a checagem funciona mesmo se algum registro mais antigo
        // ainda estiver salvo com CPF formatado (ex.: dados de seed antigos).
        $sqlVerificaCpf = "SELECT idFuncionario FROM funcionario "
                         . "WHERE REPLACE(REPLACE(Cpf, '.', ''), '-', '') = '$cpf' $filtroProprioUsuario;";
        $resultadoCpf = mysqli_query($conn, $sqlVerificaCpf);

        // Se encontrou algum registro, o CPF já está em uso
        if(mysqli_num_rows($resultadoCpf) > 0){
            mysqli_close($conn);
            // Redireciona de volta com um parâmetro de erro na URL e encerra a execução
            header("location: ../funcionarios.php?erro=cpf_existe");
            exit; 
        }
    }
    // ==========================================

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
              ." idFuncionario = idFuncionario " 
              ." WHERE idFuncionario = $idUsuario;";
              
        $result = mysqli_query($conn, $sql);

    } elseif($funcao == "D") {
        // EXCLUSÃO
        $sql = "UPDATE funcionario SET Ativo = 'N' WHERE idFuncionario = $idUsuario;";
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

    // Se tudo correr bem, redireciona com sucesso
    header("location: ../funcionarios.php?sucesso=1");
?>