<?php 
  session_start();
  include('php/funcoes.php');

  // Consulta os dados do usuário atualmente logado no banco de dados
  $idLogado = $_SESSION['idLogin'];
  include('php/conexao.php');
  $sql = "SELECT * FROM funcionario WHERE idFuncionario = $idLogado;";
  $result = mysqli_query($conn, $sql);
  $dadosUsuario = mysqli_fetch_array($result, MYSQLI_ASSOC);
  mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Meu Perfil</title>

  <?php include('partes/css.php'); ?>
  <style>
    .foto-perfil { position: relative; width: 150px; height: 150px; border-radius: 50%; overflow: hidden; border: 3px solid #ced4da; }
    .foto-perfil img { width: 100%; height: 100%; object-fit: cover; }
    .trocar-imagem { position: absolute; bottom: 0; left: 0; right: 0; background: rgba(0,0,0,0.6); color: white; text-align: center; padding-top: 5px; padding-bottom: 5px; cursor: pointer; opacity: 0; transition: opacity 0.3s; }
    .foto-perfil:hover .trocar-imagem { opacity: 1; }
    .trocar-imagem input { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; }
    .trocar-imagem p { margin: 0; font-size: 12px; }
  </style>

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  
  <?php 
    $_SESSION['menu-n1'] = 'administrador';
    $_SESSION['menu-n2'] = 'perfil';
    include('partes/sidebar.php'); 
  ?>

  <div class="content-wrapper">
    <div class="content-header"></div>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Meu Perfil</h3>
              </div>

              <div class="card-body">
                <form method="POST" action="php/salvarPerfil.php" enctype="multipart/form-data">
                  <div class="card-body">
                      <div class="row"> 
                          
                          <div class="col-12">
                              <div class="row"> 
                                <div class="col-3 text-center">
                                  <div class="foto-perfil mx-auto">
                                    <img alt="Sua Foto" src="<?php echo fotoUsuario($idLogado); ?>" class="foto">
                                    <div class="trocar-imagem">
                                      <i class="fas fa-camera upload-button"></i>
                                      <p>Alterar Foto</p>
                                      <input class="arquivo" name="Foto" type="file" accept="image/*"/>
                                    </div>
                                  </div>
                                </div>  

                                <div class="col-9">
                                  <div class="row">                     
                                    
                                    <div class="col-7">
                                      <div class="form-group">
                                        <label for="iNome">Nome</label>
                                        <input name="nNome" id="iNome" type="text" maxlength="100" class="form-control" value="<?php echo $dadosUsuario['Nome']; ?>" required>
                                      </div>
                                    </div>                      
                                    
                                    <div class="col-5">
                                      <div class="form-group">
                                        <label>Cargo (Nível de Acesso)</label>
                                        <input readonly type="text" class="form-control bg-light" value="<?php echo descrCargo($dadosUsuario['idCargo']); ?>">
                                      </div>
                                    </div>  
                                    
                                    <div class="col-7">
                                      <div class="form-group">
                                        <label>E-mail (Login)</label>
                                        <input name="nEmail" type="email" maxlength="100" class="form-control" value="<?php echo $dadosUsuario['Email']; ?>" required>
                                      </div>
                                    </div>    
                                    
                                    <div class="col-5">
                                      <div class="form-group">
                                        <label>CPF</label>
                                        <input name="nCpf" type="text" maxlength="11" class="form-control" value="<?php echo $dadosUsuario['Cpf']; ?>" required>
                                      </div>
                                    </div>

                                    <div class="col-6">
                                      <div class="form-group">
                                        <label>Telefone</label>
                                        <input name="nTelefone" type="text" maxlength="15" class="form-control" value="<?php echo $dadosUsuario['Telefone']; ?>" required>
                                      </div>
                                    </div>

                                    <div class="col-6">
                                      <div class="form-group">
                                        <label>Data de Nascimento</label>
                                        <input name="nDatanasc" type="date" class="form-control" value="<?php echo $dadosUsuario['Datanasc']; ?>" required>
                                      </div>
                                    </div>

                                    <div class="col-5">
                                      <div class="form-group">
                                        <label for="iNovaSenha">Nova Senha</label>
                                        <input name="nNovaSenha" id="iNovaSenha" type="password" maxlength="50" class="form-control" placeholder="Deixe em branco para manter a atual">
                                      </div>
                                    </div>

                                    <div class="col-5">
                                      <div class="form-group">
                                        <label for="iConfirmarSenha">Confirmar Nova Senha</label>
                                        <input name="nConfirmarSenha" id="iConfirmarSenha" type="password" maxlength="50" class="form-control" placeholder="Confirme a senha">
                                      </div>
                                    </div>

                                    <div class="col-2 d-flex align-items-center">
                                      <div class="form-group form-check mb-0 mt-3">
                                        <input type="checkbox" class="form-check-input" id="mostrarSenhaPerfil">
                                        <label class="form-check-label" for="mostrarSenhaPerfil" style="cursor: pointer; font-size: 14px;">Mostrar senhas</label>
                                      </div>
                                    </div>

                                  </div>
                                </div>
                              </div>
                          </div>
                      </div>
                  </div>  

                  <div class="card-footer bg-white text-right">
                    <a href="index.php" class="btn btn-danger">Cancelar</a>
                    <button type="submit" class="btn btn-success">Salvar Alterações</button>
                  </div>
                </form>
              </div>
              
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<?php include('partes/js.php'); ?>
<script>
  // Script para atualizar a foto de perfil dinamicamente
  document.querySelector('input[name="Foto"]').addEventListener('change', function() {
    if (this.files && this.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        document.querySelector('.foto-perfil img').src = e.target.result;
      };
      reader.readAsDataURL(this.files[0]);
    }
  });

  // Scripts de validação e exibição de senha
  $(function () {
    const check = document.getElementById("mostrarSenhaPerfil");
    const senha = document.getElementById("iNovaSenha");
    const confirma = document.getElementById("iConfirmarSenha");

    // Lógica para mostrar/ocultar senha
    if (check) {
      check.addEventListener("change", function () {
        const tipo = this.checked ? "text" : "password";
        senha.type = tipo;
        confirma.type = tipo;
      });
    }

    // Validação antes de enviar o formulário
    $('form').on('submit', function (e) {
      if (senha.value !== "" || confirma.value !== "") {
        if (senha.value !== confirma.value) {
          e.preventDefault(); // Bloqueia o envio do form
          alert("Atenção: A nova senha e a confirmação não coincidem!");
          confirma.focus();
        }
      }
    });
  });
</script>
</body>
</html>