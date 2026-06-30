<?php
  session_start();
  include('php/funcoes.php');
  include('php/funcaoEditora.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Editoras</title>

  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php
    $_SESSION['menu-n1'] = 'biblioteca';
    $_SESSION['menu-n2'] = 'editoras';
    include('partes/sidebar.php');
  ?>
  <div class="content-wrapper">
    <div class="content-header"></div>

    <section class="content">
      <div class="container-fluid">

        <?php if(isset($_GET['sucesso'])): ?>
          <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
            Operação realizada com sucesso.
          </div>
        <?php endif; ?>

        <?php if(isset($_GET['erro']) && $_GET['erro'] == 'vinculo'): ?>
          <div class="alert alert-danger alert-dismissible">
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <h5><i class="icon fas fa-ban"></i> Atenção!</h5>
            Não é possível excluir esta editora porque existem livros vinculados a ela.
          </div>
        <?php endif; ?>

        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">

                  <div class="col-9">
                    <h3 class="card-title">Editoras</h3>
                  </div>

                  <div class="col-3" align="right">
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novaEditoraModal">
                    <i class="fas fa-plus"></i>  Nova Editora
                    </button>
                  </div>

                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th style="width: 8%;">ID</th>
                      <th>Nome</th>
                      <th>E-mail</th>
                      <th>Telefone</th>
                      <th style="width: 12%;">Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php echo listaEditoras(); ?>

                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Nova Editora -->
      <div class="modal fade" id="novaEditoraModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title">Nova Editora</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                X
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarEditora.php?funcao=I">

                <h5 class="mb-3 text-info border-bottom pb-2">Dados da Editora</h5>

                <div class="row">
                  <div class="col-md-12">
                    <div class="form-group">
                      <label for="iNome">Nome:</label>
                      <input type="text" class="form-control" id="iNome" name="nNome" maxlength="100" required placeholder="Nome da editora">
                    </div>
                  </div>

                  <div class="col-md-7">
                    <div class="form-group">
                      <label for="iEmail">E-mail:</label>
                      <input type="email" class="form-control" id="iEmail" name="nEmail" maxlength="100" placeholder="contato@editora.com">
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label for="iTelefone">Telefone:</label>
                      <input type="text" class="form-control" id="iTelefone" name="nTelefone" maxlength="20" placeholder="(00) 0000-0000">
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

  <aside class="control-sidebar control-sidebar-dark"></aside>
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
