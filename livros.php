<?php
  session_start();
  include('php/funcoes.php');
  include('php/funcaoLivro.php');

  $dadosLivro = listaLivro();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Livros</title>
  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php
    $_SESSION['menu-n1'] = 'biblioteca';
    $_SESSION['menu-n2'] = 'livros';
    include('partes/sidebar.php');
  ?>

  <div class="content-wrapper">
    <div class="content-header"></div>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <?php if(isset($_GET['sucesso_cad'])): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> Livro cadastrado com sucesso!
              </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-9">
                    <h3 class="card-title">Gestão de Exemplares do Acervo</h3>
                  </div>
                  <div class="col-3" align="right">
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novoLivroModal">
                      <i class="fas fa-plus"></i> Novo Livro
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <table id="tabela" class="table table-bordered table-striped w-100">
                  <thead>
                    <tr>
                      <th style="width: 8%;">ID Cópia</th>
                      <th>Título</th>
                      <th>Autor</th>
                      <th>Gênero</th>
                      <th>Editora</th>
                      <th style="width: 8%;">Ano</th>
                      <th>ISBN</th>
                      <th style="width: 12%;">Situação</th>
                      <th style="width: 10%;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php echo $dadosLivro['linhas']; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Modal Cadastro de Livro -->
      <div class="modal fade" id="novoLivroModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title">Novo Livro</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarLivro.php?funcao=I">

                <div class="form-group">
                  <label for="iTitulo">Título:</label>
                  <input type="text" class="form-control" id="iTitulo" name="nTitulo" maxlength="200" placeholder="Título completo do livro" required>
                </div>

                <div class="form-group">
                  <label for="iAutor">Autor:</label>
                  <input type="text" class="form-control" id="iAutor" name="nAutor" maxlength="150" placeholder="Nome do autor" required>
                </div>

                <div class="row">
                  <div class="col-6">
                    <div class="form-group">
                      <label for="iGenero">Gênero:</label>
                      <select name="nGenero" id="iGenero" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php echo optionGenero(); ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="form-group">
                      <label for="iEditora">Editora:</label>
                      <select name="nEditora" id="iEditora" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php echo optionEditora(); ?>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-4">
                    <div class="form-group">
                      <label for="iAno">Ano de Publicação:</label>
                      <input type="number" class="form-control" id="iAno" name="nAno" min="1000" max="<?php echo date('Y'); ?>" placeholder="<?php echo date('Y'); ?>" required>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="form-group">
                      <label for="iIsbn">ISBN:</label>
                      <input type="text" class="form-control" id="iIsbn" name="nIsbn" maxlength="20" placeholder="Ex: 978-85-333-0227-3" required>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="form-group">
                      <label for="iQtd">Qtd. de Exemplares:</label>
                      <input type="number" class="form-control" id="iQtd" name="nQtd" min="1" max="99" value="1" required>
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

<?php echo $dadosLivro['modals']; ?>

<div class="modal fade" id="modalFalhaInativar" data-backdrop="static" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-warning">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Não foi possível excluir!</h4>
      </div>
      <div class="modal-body text-dark">
        <p>Este exemplar não pode ser excluído permanentemente porque possui históricos de empréstimos vinculados a ele.</p>
        <p><strong>Deseja inativar este livro?</strong> Ao inativar, ele e os seus exemplares deixarão de aparecer nas listagens do sistema.</p>
      </div>
      <div class="modal-footer justify-content-between">
        <a href="livros.php" class="btn btn-outline-dark">Cancelar</a>
        <a href="" id="linkInativarConfirmado" class="btn btn-danger font-weight-bold">Sim, Inativar Livro</a>
      </div>
    </div>
  </div>
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

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('erro_excluir') && urlParams.has('idLivro')) {
        const idLivro = urlParams.get('idLivro');
        $('#linkInativarConfirmado').attr('href', 'php/salvarExemplar.php?funcao=I&idLivro=' + idLivro);
        $('#modalFalhaInativar').modal('show');
    }
  });
</script>
</body>
</html>