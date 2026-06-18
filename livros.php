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
    // Define as variáveis de sessão para que o menu lateral (sidebar.php) saiba qual item destacar
    $_SESSION['menu-n1'] = 'biblioteca'; // Ajuste conforme a árvore de menus do seu sidebar
    $_SESSION['menu-n2'] = 'livros';
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
                    <h3 class="card-title">Livros</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn btn-success" onclick="window.location.href='cadastro_livro.php'">
                      <i class="fas fa-plus"></i> Cadastrar Livro
                    </button>
                  </div>

                </div>
              </div>
              <div class="card-body">
                <table id="tabela" class="table table-bordered table-striped">
                  <thead>
                  <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Autor</th>
                    <th>Gênero</th>
                    <th>Editora</th>
                    <th>Ano</th>
                    <th>ISBN</th>
                    <th style="width: 120px;">Ações</th>
                  </tr>
                  </thead>
                  <tbody>
                    
                    <?php echo listaLivro(); ?>
                    
                  </tbody>
                </table>
              </div>
              </div>
            </div>
          </div>
        </div></section>
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