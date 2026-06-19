<?php 
  session_start();
  include('php/funcoes.php');
  include('php/funcaoLivro.php');
  
  // Executa a função e obtém as duas strings separadas
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
            
            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-9">
                    <h3 class="card-title">Gestão de Exemplares do Acervo</h3>
                  </div>
                  <div class="col-3" align="right">
                    <button type="button" class="btn btn-success" onclick="window.location.href='cadastro_livro.php'">
                      <i class="fas fa-plus"></i> Cadastrar Livro
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

    // Código Inteligente para capturar falha de exclusão vinda do back-end
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('erro_excluir') && urlParams.has('idLivro')) {
        const idLivro = urlParams.get('idLivro');
        // Define o link correto apontando para a função de inativação
        $('#linkInativarConfirmado').attr('href', 'php/salvarExemplar.php?funcao=I&idLivro=' + idLivro);
        // Dispara o modal de confirmação de inativação
        $('#modalFalhaInativar').modal('show');
    }
  });
</script>
</body>
</html>