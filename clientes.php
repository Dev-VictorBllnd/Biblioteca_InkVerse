<?php 
  session_start();
  include('php/funcoes.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Clientes</title>

  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'administrador';
    $_SESSION['menu-n2'] = 'clientes';
    include('partes/sidebar.php'); 
  ?>
  <div class="content-wrapper">
    <div class="content-header">
    </div>
    
    <?php if(isset($_GET['erro']) && $_GET['erro'] == 'cpf_existe'): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Atenção!</h5>
              O CPF informado já está cadastrado no sistema. Por favor, verifique os dados.
            </div>
            <?php endif; ?>
            
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">

                  <div class="col-9">
                    <h3 class="card-title">Clientes</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novoClienteModal">
                    <i class="fas fa-plus"></i>  Novo Cliente
                    </button>
                  </div>

                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Email</th>
                      <th>CPF</th>
                      <th>Telefone</th>
                      <th>Ativo</th>                
                      <th>Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php echo listaClientes(); ?>
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="novoClienteModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title">Novo Cliente</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                X
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarCliente.php?funcao=I" enctype="multipart/form-data">              
                
              <h5 class="mb-3 text-info border-bottom pb-2">Dados Pessoais</h5>

                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="iNome">Nome Completo:</label>
                      <input type="text" class="form-control" id="iNome" name="nNome" maxlength="100" required placeholder="Digite o seu nome">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="iCpf">CPF:</label>
                      <input type="text" class="form-control" id="iCpf" name="nCpf" placeholder="000.000.000-00" maxlength="11" required>
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label for="iLogin">E-mail:</label>
                      <input type="email" class="form-control" id="iLogin" name="nEmail" maxlength="100" required placeholder="exemplo@email.com">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="iDatanasc">Data de Nascimento:</label>
                      <input type="date" class="form-control" id="iDatanasc" name="nDatanasc" required>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="iTelefone">Telefone:</label>
                      <input type="text" class="form-control" id="iTelefone" name="nTelefone" placeholder="(00) 00000-0000" maxlength="15" required>
                    </div>
                  </div>
                </div> 

                <h5 class="mt-3 mb-3 text-info border-bottom pb-2">Endereço</h5>
                
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>CEP</label>
                      <input required name="CEP" type="text" class="form-control cep" placeholder="00000-000">
                    </div>
                  </div>

                  <div class="col-md-9">
                    <div class="form-group">
                      <label>Endereço</label>
                      <input required name="Endereco" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Número</label>
                      <input required name="Numero" type="text" maxlength="8" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-9">
                    <div class="form-group">
                      <label>Complemento</label>
                      <input name="Complemento" type="text" maxlength="50" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label>Bairro</label>
                      <input required name="Bairro" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label>Cidade</label>
                      <input required name="Cidade" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-2">
                    <div class="form-group">
                      <label>UF</label>
                      <input required name="UF" type="text" class="form-control">
                    </div>
                  </div>
                </div> 
                
                <h5 class="mt-3 mb-3 text-info border-bottom pb-2">Outros</h5>
                  
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="iFoto">Foto:</label>
                      <input type="file" class="form-control" id="iFoto" name="Foto" accept="image/*">
                    </div>
                  </div>
                
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Situação do Cliente:</label>
                          <select name="nAtivo" class="form-control" required>
                              <option value="S" selected>Ativo (Acesso Permitido)</option>
                              <option value="N">Inativo (Acesso Bloqueado)</option>
                          </select>
                      </div>
                  </div>
                </div>

                <div class="modal-footer mt-3">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
                  <button type="submit" class="btn text-white" style="background-color: #2563eb;">Salvar</button>
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