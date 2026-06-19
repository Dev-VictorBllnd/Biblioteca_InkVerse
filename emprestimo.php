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
  <title>InkVerse - Empréstimos</title>
  <?php include('partes/css.php'); ?>
  <style>
    /* Força os botões de ação a ficarem perfeitamente quadrados, idênticos e juntos */
    .btn-acao-quadrado {
        border-radius: 0px !important;
        margin: 0 2px !important;
        width: 36px !important;
        height: 36px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 0 !important;
    }
  </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'biblioteca';
    $_SESSION['menu-n2'] = 'emprestimos';
    include('partes/sidebar.php'); 
  ?>
  
  <div class="content-wrapper">
    <div class="content-header"></div>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
            <?php if (isset($_GET['sucesso'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 0px;">
                    <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
                    <?php 
                      if($_GET['sucesso'] == 'editado') echo "O empréstimo do livro foi atualizado/renovado com sucesso.";
                      if($_GET['sucesso'] == 'inserido') echo "Novo empréstimo registrado com sucesso.";
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-9">
                    <h3 class="card-title">Controle de Pendências por Livro</h3>
                  </div>
                  <div class="col-3" align="right">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#novoEmprestimoModal" style="border-radius: 0px;">
                      <i class="fas fa-plus"></i> Novo Empréstimo
                    </button>
                  </div>
                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th style="width: 5%;">ID Cli</th>
                      <th style="width: 25%;">Cliente</th>
                      <th style="width: 45%;">Livro Pendente</th>
                      <th style="width: 10%;">Status</th>                
                      <th style="width: 15%; text-align: center;">Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php
                  // =================================================================
                  // CONSULTA SQL: Lista linha por linha cada livro pendente individual
                  // =================================================================
                  $sql = mysqli_query($conn,"
                  SELECT 
                      c.idCliente, 
                      c.Nome AS Cliente, 
                      e.idEmprestimo,
                      ehe.idExemplar,
                      l.Titulo,
                      ehe.Data_emprestimo,
                      ehe.data_prevista
                  FROM cliente c
                  INNER JOIN emprestimo e ON c.idCliente = e.idCliente
                  INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
                  INNER JOIN exemplar ex ON ex.idExemplar = ehe.idExemplar
                  INNER JOIN livro l ON l.idLivro = ex.idLivro
                  WHERE ehe.Data_devolucao IS NULL
                  ORDER BY c.Nome ASC, ehe.Data_emprestimo ASC
                  ");

                  while($dados = mysqli_fetch_assoc($sql)){
                      $chaveUnica = $dados['idEmprestimo'].'_'.$dados['idExemplar'];
                  ?>
                  <tr>
                      <td class="align-middle"><?php echo $dados['idCliente']; ?></td>
                      <td class="align-middle"><strong><?php echo $dados['Cliente']; ?></strong></td>
                      <td class="align-middle">
                          • <strong><?php echo $dados['Titulo']; ?></strong> <br>
                          <small class="text-muted">
                              Pegou em: <?php echo date('d/m/Y', strtotime($dados['Data_emprestimo'])); ?> | 
                              Devolução Prevista: <?php echo date('d/m/Y', strtotime($dados['data_prevista'])); ?>
                          </small>
                      </td>
                      <td class="align-middle">
                          <span class="badge badge-warning">Pendente</span>
                      </td>
                      <td class="text-center align-middle">
                        <div class="d-flex justify-content-center align-items-center">
                            
                            <button class="btn btn-primary btn-acao-quadrado" title="Editar / Reemprestar Livro" data-toggle="modal" data-target="#modalEditar<?php echo $chaveUnica; ?>">
                                <i class="fas fa-pencil-alt"></i>
                            </button>
                            
                            <a href="devolucoes.php?cliente=<?php echo $dados['idCliente']; ?>" 
                               class="btn btn-success btn-acao-quadrado" 
                               title="Fazer Devolução">
                                <i class="fas fa-check"></i>
                            </a>
                            
                        </div>
                      </td>
                  </tr>

                  <div class="modal fade" id="modalEditar<?php echo $chaveUnica; ?>">
                    <div class="modal-dialog">
                      <div class="modal-content" style="border-radius: 0px;">
                        <div class="modal-header bg-primary" style="border-radius: 0px;">
                          <h4 class="modal-title">Editar / Reemprestar Livro</h4>
                          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form method="POST" action="php/salvarEmprestimo.php?funcao=U">              
                          <div class="modal-body text-wrap text-left">
                            <input type="hidden" name="idEmprestimo" value="<?php echo $dados['idEmprestimo']; ?>">
                            <input type="hidden" name="idExemplar" value="<?php echo $dados['idExemplar']; ?>">
                            
                            <p><strong>Cliente:</strong> <?php echo $dados['Cliente']; ?></p>
                            <p><strong>Livro:</strong> <?php echo $dados['Titulo']; ?> <small>(Cód: <?php echo $dados['idExemplar']; ?>)</small></p>
                            
                            <hr>
                            
                            <div class="form-group">
                              <label>Nova Data do Empréstimo:</label>
                              <input type="date" class="form-control" name="nDataEmprestimo" value="<?php echo date('Y-m-d', strtotime($dados['Data_emprestimo'])); ?>" required style="border-radius: 0px;">
                            </div>
                            
                            <div class="form-group">
                              <label>Nova Data Prevista para Devolução (Renovação):</label>
                              <input type="date" class="form-control" name="nDataPrevista" value="<?php echo date('Y-m-d', strtotime($dados['data_prevista'])); ?>" required style="border-radius: 0px;">
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal" style="border-radius: 0px;">Cancelar</button>
                            <button type="submit" class="btn btn-primary" style="border-radius: 0px;"><i class="fas fa-save"></i> Salvar</button>
                          </div>
                        </form>
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

      <div class="modal fade" id="novoEmprestimoModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content" style="border-radius: 0px;">
            <div class="modal-header bg-success" style="border-radius: 0px;">
              <h4 class="modal-title">Novo Empréstimo</h4>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarEmprestimo.php?funcao=I">              
                <div class="row">
                  
                  <div class="col-12 mb-3">
                    <div class="form-group">
                      <label for="iCliente">Cliente:</label>
                      <select name="nCliente" id="iCliente" class="form-control" required style="border-radius: 0px;">
                        <option value="">Selecione o Cliente...</option>
                        <?php
                          $qClientes = mysqli_query($conn, "SELECT idCliente, Nome FROM cliente ORDER BY Nome ASC");
                          if(mysqli_num_rows($qClientes) > 0) {
                              while($cli = mysqli_fetch_assoc($qClientes)){
                                  echo '<option value="'.$cli['idCliente'].'">'.$cli['Nome'].'</option>';
                              }
                          } else {
                              echo '<option value="">Nenhum cliente cadastrado</option>';
                          }
                        ?>
                      </select>
                    </div>
                  </div>

                  <div class="col-12 mb-3">
                    <label>Selecione os Livros (Máximo de 5 itens): <span id="contadorLivros" class="text-danger float-right">0/5</span></label>
                    <div class="border p-2" style="max-height: 200px; overflow-y: scroll; border-radius: 0px; background: #f8f9fa;">
                      <?php
                        $qExemplares = mysqli_query($conn, "SELECT ex.idExemplar, l.Titulo FROM exemplar ex JOIN livro l ON ex.idLivro = l.idLivro WHERE ex.Emprestado = 'nao' ORDER BY l.Titulo ASC");
                        
                        if(mysqli_num_rows($qExemplares) > 0) {
                            while($exem = mysqli_fetch_assoc($qExemplares)){
                                echo '<div class="custom-control custom-checkbox">';
                                echo '<input class="custom-control-input book-check" type="checkbox" name="nExemplares[]" id="cb'.$exem['idExemplar'].'" value="'.$exem['idExemplar'].'">';
                                echo '<label for="cb'.$exem['idExemplar'].'" class="custom-control-label font-weight-normal">'.$exem['Titulo'].' <small class="text-muted">(Cód: '.$exem['idExemplar'].')</small></label>';
                                echo '</div>';
                            }
                        } else {
                            echo '<div class="text-muted text-center py-3">Nenhum livro disponível no momento.</div>';
                        }
                      ?>
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="form-group">
                      <label>Data do Empréstimo:</label>
                      <input type="date" class="form-control" name="nDataEmprestimo" value="<?php echo date('Y-m-d'); ?>" required style="border-radius: 0px;">
                    </div>
                  </div>

                  <div class="col-6">
                    <div class="form-group">
                      <label>Data Prevista para Devolução:</label>
                      <input type="date" class="form-control" name="nDataPrevista" required style="border-radius: 0px;">
                    </div>
                  </div>
                  
                </div>

                <div class="modal-footer mt-3">
                  <button type="button" class="btn btn-danger" data-dismiss="modal" style="border-radius: 0px;">Cancelar</button>
                  <button type="submit" class="btn btn-success" style="border-radius: 0px;">Salvar Empréstimo</button>
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
  $(document).ready(function () {
    $('.book-check').on('change', function() {
        let maxAllowed = 5;
        let countChecked = $('.book-check:checked').length;
        if (countChecked > maxAllowed) {
            this.checked = false;
            countChecked = maxAllowed;
            alert('Atenção: Você só pode selecionar até 5 livros por empréstimo!');
        }
        $('#contadorLivros').text(countChecked + '/5');
    });

    $('#tabela').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
      }
    });
  });
</script>

</body>
</html>