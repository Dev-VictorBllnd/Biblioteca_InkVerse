<?php 
  session_start();
  include('php/funcoes.php');
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
    // Ajuste as variáveis de sessão conforme a estrutura do seu menu no sidebar.php
    $_SESSION['menu-n1'] = 'biblioteca'; 
    $_SESSION['menu-n2'] = 'livros';
    include('partes/sidebar.php'); 
  ?>
  
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Livros</h1>
            <p class="text-muted">Gerencie o acervo da biblioteca</p>
          </div>
        </div>
      </div>
    </div>
    
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card shadow-sm rounded">
              <div class="card-header">
                <div class="row align-items-center">
                  
                  <div class="col-9">
                    <h3 class="card-title"><i class="fas fa-book-open mr-2"></i> Acervo de Livros</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <a href="cadastro_livro.php" class="btn btn-primary">
                      <i class="fas fa-plus"></i> Novo Livro
                    </a>
                  </div>

                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-striped table-hover align-middle">
                  <thead>
                  <tr>
                      <th width="5%">ID</th>
                      <th width="20%">Título</th>
                      <th width="15%">Autor</th>
                      <th width="15%">Gênero</th>
                      <th width="15%">Editora</th>
                      <th width="15%">ISBN</th>
                      <th width="5%">Ano</th>                
                      <th width="10%" class="text-center">Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php echo listaLivros(); ?>
                  
                  </tbody>
                  
                </table>
              </div>
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
      "language": {
          "search": "Pesquisar:",
          "lengthMenu": "Mostrar _MENU_ livros por página",
          "zeroRecords": "Nenhum livro encontrado",
          "info": "Mostrando página _PAGE_ de _PAGES_",
          "infoEmpty": "Nenhum registro disponível",
          "infoFiltered": "(filtrado de _MAX_ registros no total)",
          "paginate": {
              "first": "Primeiro",
              "last": "Último",
              "next": "Próximo",
              "previous": "Anterior"
          }
      }
    });
  });
</script>

</body>
</html>