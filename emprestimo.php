<?php 
  session_start();
  include('php/conexao.php'); // Necessário para a consulta SQL direta na página
  include('php/funcoes.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>InkVerse - Empréstimos</title>

  <!-- CSS -->
  <?php include('partes/css.php'); ?>
  <!-- Fim CSS -->

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <?php include('partes/navbar.php'); ?>
  <!-- Fim Navbar -->

  <!-- Sidebar -->
  <?php 
    $_SESSION['menu-n1'] = 'biblioteca'; // Ajustado para manter o menu da biblioteca aberto[cite: 10]
    $_SESSION['menu-n2'] = 'emprestimos';
    include('partes/sidebar.php'); 
  ?>
  <!-- Fim Sidebar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <!-- Espaço -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">
                  
                  <div class="col-9">
                    <h3 class="card-title">Controle de Empréstimos</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#novoEmprestimoModal">
                      <i class="fas fa-plus"></i> Novo Empréstimo
                    </button>
                  </div>

                </div>
              </div>

              <!-- /.card-header -->
              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover text-nowrap">
                  <thead>
                  <tr>
                      <th>ID</th>
                      <th>Cliente</th>
                      <th>Livro</th>
                      <th>Data Empréstimo</th>
                      <th>Data Devolução</th>
                      <th>Status</th>                
                      <th>Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php
                  // Consulta baseada no dashboard para listar os empréstimos[cite: 10]
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
                  ORDER BY ehe.Data_emprestimo DESC
                  ");

                  while($dados = mysqli_fetch_assoc($sql)){
                      // Verifica se já foi devolvido para gerar o status visual[cite: 10]
                      if(empty($dados['Data_devolucao'])){
                          $status = '<span class="badge badge-warning">Emprestado</span>';
                          $dataDevolucao = '-';
                      } else {
                          $status = '<span class="badge badge-success">Devolvido</span>';
                          $dataDevolucao = date('d/m/Y', strtotime($dados['Data_devolucao']));
                      }
                  ?>
                  <tr>
                      <td><?php echo $dados['idEmprestimo']; ?></td>
                      <td><?php echo $dados['Cliente']; ?></td>
                      <td><?php echo $dados['Livro']; ?></td>
                      <td><?php echo date('d/m/Y', strtotime($dados['Data_emprestimo'])); ?></td>
                      <td><?php echo $dataDevolucao; ?></td>
                      <td><?php echo $status; ?></td>
                      <td>
                        <button class="btn btn-sm btn-primary" title="Editar"><i class="fas fa-edit"></i></button>
                        <?php if(empty($dados['Data_devolucao'])) { ?>
                            <button class="btn btn-sm btn-success" title="Registrar Devolução"><i class="fas fa-check"></i></button>
                        <?php } ?>
                      </td>
                  </tr>
                  <?php } ?>
                  
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->

      <!-- Modal Novo Empréstimo -->
      <div class="modal fade" id="novoEmprestimoModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header bg-success">
              <h4 class="modal-title">Novo Empréstimo</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarEmprestimo.php?funcao=I">              
                
                <div class="row">
                  <div class="col-6">
                    <div class="form-group">
                      <label for="iCliente">Cliente:</label>
                      <select name="nCliente" id="iCliente" class="form-control" required>
                        <option value="">Selecione o Cliente...</option>
                        <!-- Aqui você pode usar uma função do PHP para listar clientes como feito nos usuários[cite: 14] -->
                      </select>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="form-group">
                      <label for="iExemplar">Livro/Exemplar:</label>
                      <select name="nExemplar" id="iExemplar" class="form-control" required>
                        <option value="">Selecione o Livro...</option>
                        <!-- Listar exemplares disponíveis -->
                      </select>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="form-group">
                      <label for="iDataEmprestimo">Data do Empréstimo:</label>
                      <input type="date" class="form-control" id="iDataEmprestimo" name="nDataEmprestimo" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="form-group">
                      <label for="iDataPrevista">Data Prevista para Devolução:</label>
                      <input type="date" class="form-control" id="iDataPrevista" name="nDataPrevista" required>
                    </div>
                  </div>
                  
                  <div class="col-12">
                    <div class="form-group">
                      <label for="iObservacoes">Observações:</label>
                      <textarea class="form-control" id="iObservacoes" name="nObservacoes" rows="3"></textarea>
                    </div>
                  </div>

                </div>

                <div class="modal-footer mt-3">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn btn-success">Salvar Empréstimo</button>
                </div>
                
              </form>

            </div>
          </div>
          <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
      </div>
      <!-- /.modal -->

    </section>
    <!-- /.content -->
  </div>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- JS -->
<?php include('partes/js.php'); ?>
<!-- Fim JS -->

<script>
  // Inicialização padrão do DataTable com pesquisa e paginação[cite: 14]
  $(function () {
    $('#tabela').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "language": { // Opcional: para traduzir o datatable caso queira
            "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
      }
    });
  });
</script>

</body>
</html>