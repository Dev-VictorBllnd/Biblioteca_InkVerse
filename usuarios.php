<?php 
  session_start();
  include('php/funcoes.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Usuários</title>

  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'administrador';
    $_SESSION['menu-n2'] = 'usuarios';
    include('partes/sidebar.php'); 
  ?>
  <div class="content-wrapper">
    <div class="content-header">
    </div>
    
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">
                  
                  <div class="col-9">
                    <h3 class="card-title">Funcionários</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#novoUsuarioModal">
                      Novo Usuário
                    </button>
                  </div>

                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th>ID</th>
                      <th>Tipo de Usuário</th>
                      <th>Nome</th>
                      <th>Login (E-mail)</th>
                      <th>Ativo</th>                
                      <th>Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php echo listaUsuario(); ?>
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="novoUsuarioModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-success">
              <h4 class="modal-title">Novo Usuário</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarUsuario.php?funcao=I" enctype="multipart/form-data">              
                
                <div class="row">
                  <div class="col-8">
                    <div class="form-group">
                      <label for="iNome">Nome:</label>
                      <input type="text" class="form-control" id="iNome" name="nNome" maxlength="100" required>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group">
                      <label for="iTipoUsuario">Tipo de Usuário:</label>
                      <select name="nTipoUsuario" id="iTipoUsuario" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php echo optionCargo(); ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-8">
                    <div class="form-group">
                      <label for="iLogin">E-mail (Login):</label>
                      <input type="email" class="form-control" id="iLogin" name="nLogin" maxlength="100" required>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group">
                      <label for="iSenha">Senha:</label>
                      <input type="password" class="form-control" id="iSenha" name="nSenha" maxlength="50" required>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group">
                      <label for="iCpf">CPF:</label>
                      <input type="text" class="form-control" id="iCpf" name="nCpf" placeholder="Apenas números" maxlength="11" required>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group">
                      <label for="iTelefone">Telefone:</label>
                      <input type="text" class="form-control" id="iTelefone" name="nTelefone" placeholder="(00) 00000-0000" maxlength="15" required>
                    </div>
                  </div>

                  <div class="col-4">
                    <div class="form-group">
                      <label for="iDatanasc">Data de Nascimento:</label>
                      <input type="date" class="form-control" id="iDatanasc" name="nDatanasc" required>
                    </div>
                  </div>
                  
                  <div class="col-8">
                    <div class="form-group">
                      <label for="iFoto">Foto:</label>
                      <input type="file" class="form-control" id="iFoto" name="Foto" accept="image/*">
                    </div>
                  </div>
                
                  <div class="col-4">
                      <div class="form-group">
                          <label>Situação do Usuário:</label>
                          <select name="nAtivo" class="form-control" required>
                              <option value="S" selected>Ativo (Acesso Permitido)</option>
                              <option value="N">Inativo (Acesso Bloqueado)</option>
                          </select>
                      </div>
                  </div>

                </div>

                <div class="modal-footer mt-3">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
                  <button type="submit" class="btn btn-success">Salvar</button>
                </div>
                
              </form>
            </div>
          </div>
        </div>
      </div>
      </section>
  </div>

  <aside class="control-sidebar control-sidebar-dark">
  </aside>
</div>

<?php include('partes/js.php'); ?>

<script>
  $(function () {
    $('#tabela').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>

</body>
</html>