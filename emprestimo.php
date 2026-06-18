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

  <?php include('partes/css.php'); ?>
  </head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'biblioteca'; // Ajustado para manter o menu da biblioteca aberto
    $_SESSION['menu-n2'] = 'emprestimos';
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
                    <h3 class="card-title">Controle de Empréstimos</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#novoEmprestimoModal">
                      <i class="fas fa-plus"></i> Novo Empréstimo
                    </button>
                  </div>

                </div>
              </div>

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
                  // Consulta baseada no dashboard para listar os empréstimos
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
                      // Verifica se já foi devolvido para gerar o status visual
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
                        <button class="btn btn-sm btn-primary" title="Editar" data-toggle="modal" data-target="#editarEmprestimoModal<?php echo $dados['idEmprestimo']; ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        
                        <?php if(empty($dados['Data_devolucao'])) { ?>
                            <a href="php/salvarEmprestimo.php?funcao=devolver&id=<?php echo $dados['idEmprestimo']; ?>" 
                               class="btn btn-sm btn-success" 
                               title="Registrar Devolução"
                               onclick="return confirm('Tem certeza que deseja registrar a devolução deste livro?');">
                                <i class="fas fa-check"></i>
                            </a>
                        <?php } ?>
                      </td>
                  </tr>

                  <div class="modal fade" id="editarEmprestimoModal<?php echo $dados['idEmprestimo']; ?>">
                    <div class="modal-dialog modal-lg">
                      <div class="modal-content">
                        <div class="modal-header bg-primary">
                          <h4 class="modal-title">Editar Empréstimo #<?php echo $dados['idEmprestimo']; ?></h4>
                          <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                          </button>
                        </div>
                        <div class="modal-body">
                          <form method="POST" action="php/salvarEmprestimo.php?funcao=U">              
                            <input type="hidden" name="idEmprestimo" value="<?php echo $dados['idEmprestimo']; ?>">

                            <div class="row">
                              <div class="col-12">
                                <p><strong>Cliente:</strong> <?php echo $dados['Cliente']; ?></p>
                                <p><strong>Livro:</strong> <?php echo $dados['Livro']; ?></p>
                              </div>
                              <div class="col-6">
                                <div class="form-group">
                                  <label>Data do Empréstimo:</label>
                                  <input type="date" class="form-control" name="nDataEmprestimo" value="<?php echo date('Y-m-d', strtotime($dados['Data_emprestimo'])); ?>" required>
                                </div>
                              </div>
                              <div class="col-6">
                                <div class="form-group">
                                  <label>Data de Devolução (Deixe em branco se não devolvido):</label>
                                  <input type="date" class="form-control" name="nDataDevolucao" value="<?php echo !empty($dados['Data_devolucao']) ? date('Y-m-d', strtotime($dados['Data_devolucao'])) : ''; ?>">
                                </div>
                              </div>
                            </div>
                            <div class="modal-footer mt-3">
                              <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                              <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  </div>
                  <?php } ?>
                  
                  </tbody>
                </table>
              </div>
              </div>
            </div>
          </div>
        </div>
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
                      </select>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="form-group">
                      <label for="iExemplar">Livro/Exemplar:</label>
                      <select name="nExemplar" id="iExemplar" class="form-control" required>
                        <option value="">Selecione o Livro...</option>
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
          </div>
        </div>
      </section>
    </div>

  <aside class="control-sidebar control-sidebar-dark">
    </aside>
  </div>
<?php include('partes/js.php'); ?>
<script>
  // Inicialização padrão do DataTable com pesquisa e paginação
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