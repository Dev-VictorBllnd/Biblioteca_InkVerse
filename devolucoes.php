<?php 
  session_start();
  include('php/conexao.php'); 
  include('php/funcoes.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>InkVerse - Registrar Devoluções</title>

  <?php include('partes/css.php'); ?>
  </head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'biblioteca'; 
    $_SESSION['menu-n2'] = 'devolucoes'; // Destaque no menu para a nova tela
    include('partes/sidebar.php'); 
  ?>
  <div class="content-wrapper">
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Registrar Devoluções</h1>
          </div>
        </div>
      </div>
    </div>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
            <div class="card card-outline card-success">
              <div class="card-header">
                <h3 class="card-title">Livros Pendentes de Devolução</h3>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-striped text-nowrap">
                  <thead>
                  <tr>
                      <th>ID</th>
                      <th>Cliente</th>
                      <th>Livro</th>
                      <th>Data Empréstimo</th>
                      <th>Status</th>                
                      <th class="text-center">Ação</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php
                  // Consulta filtrando APENAS empréstimos sem data de devolução (Ativos)
                  $sql = mysqli_query($conn,"
                  SELECT
                      e.idEmprestimo,
                      c.Nome AS Cliente,
                      l.Titulo AS Livro,
                      ehe.Data_emprestimo,
                      ehe.Data_devolucao
                  FROM emprestimo_has_exemplar ehe
                  INNER JOIN emprestimo e ON e.idEmprestimo = ehe.idEmprestimo
                  INNER JOIN cliente c ON c.idCliente = e.idCliente
                  INNER JOIN exemplar ex ON ex.idExemplar = ehe.idExemplar
                  INNER JOIN livro l ON l.idLivro = ex.idLivro
                  WHERE ehe.Data_devolucao IS NULL OR ehe.Data_devolucao = '0000-00-00'
                  ORDER BY ehe.Data_emprestimo ASC
                  ");

                  while($dados = mysqli_fetch_assoc($sql)){
                  ?>
                  <tr>
                      <td><?php echo $dados['idEmprestimo']; ?></td>
                      <td><?php echo $dados['Cliente']; ?></td>
                      <td><?php echo $dados['Livro']; ?></td>
                      <td><?php echo date('d/m/Y', strtotime($dados['Data_emprestimo'])); ?></td>
                      <td><span class="badge badge-warning">Aguardando Devolução</span></td>
                      <td class="text-center">
                        <a href="php/salvarEmprestimo.php?funcao=devolver&id=<?php echo $dados['idEmprestimo']; ?>&origem=devolucoes" 
                           class="btn btn-md btn-success" 
                           title="Registrar Devolução"
                           onclick="return confirm('Confirmar o recebimento do livro: <?php echo addslashes($dados['Livro']); ?>?');">
                            <i class="fas fa-undo-alt"></i> Receber Livro
                        </a>
                      </td>
                  </tr>
                  <?php } ?>
                  
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
  // Inicialização padrão do DataTable
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
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
      }
    });
  });
</script>

</body>
</html>