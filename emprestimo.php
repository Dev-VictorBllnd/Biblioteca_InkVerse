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
              <div class="alert alert-success alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <h5><i class="icon fas fa-check"></i> Sucesso!</h5>
                <?php
                  $s = $_GET['sucesso'];
                  if($s == 'inserido')  echo "Novo empréstimo registrado com sucesso.";
                  if($s == 'editado')   echo "Empréstimo renovado com sucesso.";
                  if($s == 'devolvido') echo "Livro devolvido e disponível na prateleira novamente.";
                ?>
              </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro']) && $_GET['erro'] == 'sem_livro'): ?>
              <div class="alert alert-danger alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5>
                Selecione pelo menos um livro para registrar o empréstimo.
              </div>
            <?php endif; ?>

            <?php if (isset($_GET['erro']) && $_GET['erro'] == 'limite'): ?>
              <div class="alert alert-warning alert-dismissible fade show">
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                <h5><i class="icon fas fa-exclamation-triangle"></i> Limite atingido!</h5>
                Cada cliente pode ter no máximo 5 livros emprestados ao mesmo tempo.
              </div>
            <?php endif; ?>

            <?php
            $hoje = date('Y-m-d');
            $LIMITE_CLIENTE = 5;

            // Quantos livros cada cliente já tem em mãos (não devolvidos)
            $pendentesPorCliente = array();
            $qPend = mysqli_query($conn, "
              SELECT e.idCliente, COUNT(*) AS qtd
              FROM emprestimo e
              INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
              WHERE ehe.Data_devolucao IS NULL
              GROUP BY e.idCliente
            ");
            if($qPend){
              while($p = mysqli_fetch_assoc($qPend)){
                $pendentesPorCliente[$p['idCliente']] = (int)$p['qtd'];
              }
            }
            ?>

            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-9">
                    <h3 class="card-title">Controle de Empréstimos Ativos</h3>
                  </div>
                  <div class="col-3 text-right">
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novoEmprestimoModal">
                      <i class="fas fa-plus"></i> Novo Empréstimo
                    </button>
                  </div>
                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th style="width:5%;">ID</th>
                      <th style="width:20%;">Cliente</th>
                      <th style="width:30%;">Livro (Exemplar)</th>
                      <th style="width:12%;">Retirada</th>
                      <th style="width:12%;">Devolução Prev.</th>
                      <th style="width:10%;">Status</th>
                      <th style="width:11%; text-align:center;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>

                  <?php
                  $sql = mysqli_query($conn, "
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
                    ORDER BY ehe.data_prevista ASC, c.Nome ASC
                  ");

                  if($sql && mysqli_num_rows($sql) > 0):
                    while($dados = mysqli_fetch_assoc($sql)):
                      $chave    = $dados['idEmprestimo'].'_'.$dados['idExemplar'];
                      $atrasado = $dados['data_prevista'] < $hoje;
                  ?>
                  <tr class="<?php echo $atrasado ? 'table-danger' : ''; ?>">
                    <td class="align-middle"><?php echo $dados['idCliente']; ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($dados['Cliente']); ?></td>
                    <td class="align-middle">
                      <?php echo htmlspecialchars($dados['Titulo']); ?>
                      <small class="text-muted">(Cód: <?php echo $dados['idExemplar']; ?>)</small>
                    </td>
                    <td class="align-middle"><?php echo date('d/m/Y', strtotime($dados['Data_emprestimo'])); ?></td>
                    <td class="align-middle"><?php echo date('d/m/Y', strtotime($dados['data_prevista'])); ?></td>
                    <td class="align-middle">
                      <?php if($atrasado): ?>
                        <h5><span class="badge badge-danger">Atrasado</span></h5>
                      <?php else: ?>
                        <h5><span class="badge text-white" style="background-color: #2563eb;">No Prazo</span></h5>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <button class="btn btn-sm btn-link text-info mr-1" title="Renovar Prazo" data-toggle="modal" data-target="#modalEditar<?php echo $chave; ?>">
                        <i class="fas fa-sync-alt"></i>
                      </button>
                      <button class="btn btn-sm btn-link text-success" title="Registrar Devolução" data-toggle="modal" data-target="#modalDevolver<?php echo $chave; ?>">
                        <i class="fas fa-undo"></i>
                      </button>
                    </td>
                  </tr>

                  <div class="modal fade" id="modalEditar<?php echo $chave; ?>">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #0b1a2c;">
                          <h4 class="modal-title">Renovar Empréstimo</h4>
                          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <form method="POST" action="php/salvarEmprestimo.php?funcao=U">
                          <div class="modal-body">
                            <input type="hidden" name="idEmprestimo" value="<?php echo $dados['idEmprestimo']; ?>">
                            <input type="hidden" name="idExemplar"   value="<?php echo $dados['idExemplar']; ?>">
                            <p><strong>Cliente:</strong> <?php echo htmlspecialchars($dados['Cliente']); ?></p>
                            <p><strong>Livro:</strong> <?php echo htmlspecialchars($dados['Titulo']); ?></p>
                            <hr>
                            <div class="form-group">
                              <label>Data do Empréstimo (Renovação):</label>
                              <input type="date" class="form-control" name="nDataEmprestimo" value="<?php echo $hoje; ?>" required>
                            </div>
                            <div class="form-group">
                              <label>Nova Data Prevista para Devolução (+7 dias):</label>
                              <input type="date" class="form-control" name="nDataPrevista" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn text-white" style="background-color: #2563eb;"><i class="fas fa-save"></i> Renovar</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  <div class="modal fade" id="modalDevolver<?php echo $chave; ?>">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <div class="modal-header text-white" style="background-color: #0b1a2c;">
                          <h4 class="modal-title">Confirmar Devolução</h4>
                          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                        </div>
                        <div class="modal-body">
                          <p>Confirmar a devolução do livro abaixo?</p>
                          <p><strong>Cliente:</strong> <?php echo htmlspecialchars($dados['Cliente']); ?></p>
                          <p><strong>Livro:</strong> <?php echo htmlspecialchars($dados['Titulo']); ?> <small class="text-muted">(Cód: <?php echo $dados['idExemplar']; ?>)</small></p>
                          <p><strong>Devolução prevista:</strong> <?php echo date('d/m/Y', strtotime($dados['data_prevista'])); ?></p>
                        </div>
                        <form method="POST" action="php/salvarEmprestimo.php?funcao=D">
                          <input type="hidden" name="idEmprestimo" value="<?php echo $dados['idEmprestimo']; ?>">
                          <input type="hidden" name="idExemplar"   value="<?php echo $dados['idExemplar']; ?>">
                          <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn text-white" style="background-color: #2563eb;"><i class="fas fa-check"></i> Confirmar Devolução</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>

                  <?php endwhile;
                  else: ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted py-3">
                        <i class="fas fa-book-open mr-2"></i>Nenhum empréstimo ativo no momento.
                      </td>
                    </tr>
                  <?php endif; ?>

                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

        <div class="modal fade" id="novoEmprestimoModal">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color: #0b1a2c;">
                <h4 class="modal-title">Novo Empréstimo</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <form method="POST" action="php/salvarEmprestimo.php?funcao=I" id="formNovoEmprestimo">
                  <div class="row">

                    <div class="col-12">
                      <div class="form-group">
                        <label for="iCliente">Cliente:</label>
                        <select name="nCliente" id="iCliente" class="form-control" required>
                          <option value="">Selecione o Cliente...</option>
                          <?php
                            $qCli = mysqli_query($conn, "SELECT idCliente, Nome FROM cliente WHERE Ativo = 'S' ORDER BY Nome ASC");
                            if($qCli && mysqli_num_rows($qCli) > 0){
                              while($cli = mysqli_fetch_assoc($qCli)){
                                echo '<option value="'.$cli['idCliente'].'">'.htmlspecialchars($cli['Nome']).'</option>';
                              }
                            } else {
                              echo '<option value="" disabled>Nenhum cliente ativo cadastrado</option>';
                            }
                          ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label>Livros Disponíveis:</label>
                      <input type="text" id="buscaLivro" class="form-control form-control-sm mb-2" placeholder="Buscar livro...">
                      <div id="listaDisponiveis" class="list-group" style="max-height: 220px; overflow-y: auto;">
                        <?php
                          $qExe = mysqli_query($conn, "
                            SELECT ex.idExemplar, l.Titulo
                            FROM exemplar ex
                            JOIN livro l ON ex.idLivro = l.idLivro
                            WHERE (ex.Emprestado IS NULL OR ex.Emprestado NOT IN ('sim', 'S'))
                            ORDER BY l.Titulo ASC
                          ");
                          if($qExe && mysqli_num_rows($qExe) > 0){
                            while($exem = mysqli_fetch_assoc($qExe)){
                              $titulo = htmlspecialchars($exem['Titulo'], ENT_QUOTES);
                              echo '<button type="button" class="list-group-item list-group-item-action py-2 item-disponivel" '
                                  .'data-id="'.$exem['idExemplar'].'" data-titulo="'.$titulo.'">'
                                  .'<i class="fas fa-plus-circle text-success mr-2"></i>'.$titulo
                                  .' <small class="text-muted">(Cód: '.$exem['idExemplar'].')</small>'
                                  .'</button>';
                            }
                          } else {
                            echo '<div class="text-muted text-center py-3"><i class="fas fa-info-circle mr-1"></i>Nenhum livro disponível.</div>';
                          }
                        ?>
                      </div>
                    </div>

                    <div class="col-md-6">
                      <label>Livros para Empréstimo: <span id="contadorLivros" class="badge badge-success float-right">0/5</span></label>
                      <div id="caixaSelecionados" class="border rounded p-2" style="min-height: 220px; max-height: 256px; overflow-y: auto; background: #f8f9fa;">
                        <div id="msgVazio" class="text-muted text-center py-5">
                          <i class="fas fa-arrow-left mr-1"></i> Clique nos livros ao lado para adicioná-los aqui.
                        </div>
                      </div>
                      <div id="inputsExemplares"></div>
                    </div>

                    <div class="col-6">
                      <div class="form-group">
                        <label>Data do Empréstimo:</label>
                        <input type="date" class="form-control" id="iDataEmprestimo" name="nDataEmprestimo" value="<?php echo date('Y-m-d'); ?>" required>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="form-group">
                        <label>Data Prevista para Devolução <small class="text-muted">(+7 dias automático)</small>:</label>
                        <input type="date" class="form-control" id="iDataPrevista" name="nDataPrevista" value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                      </div>
                    </div>

                  </div>

                  <div class="modal-footer mt-2 px-0">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white" style="background-color: #2563eb;"><i class="fas fa-save"></i> Registrar Empréstimo</button>
                  </div>
                </form>
              </div>
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
// Quantos livros cada cliente já tem em mãos (vindo do PHP)
var pendentesPorCliente = <?php echo json_encode($pendentesPorCliente); ?>;
var LIMITE = <?php echo $LIMITE_CLIENTE; ?>;

$(document).ready(function () {

  // Inicializa DataTable
  $('#tabela').DataTable({
    "order": [[4, "asc"]],
    "language": {
      "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json"
    }
  });

  // ---- Limite disponível conforme o cliente já tem em mãos ----
  function limiteDisponivel() {
    var idCli = $('#iCliente').val();
    var jaTem = (idCli && pendentesPorCliente[idCli]) ? pendentesPorCliente[idCli] : 0;
    return LIMITE - jaTem;
  }

  function selecionados() {
    return $('#caixaSelecionados .item-selecionado').length;
  }

  function atualizaContador() {
    var idCli   = $('#iCliente').val();
    var jaTem   = (idCli && pendentesPorCliente[idCli]) ? pendentesPorCliente[idCli] : 0;
    var total   = jaTem + selecionados();
    $('#contadorLivros').text(total + '/' + LIMITE);

    if (selecionados() === 0) {
      $('#msgVazio').show();
    } else {
      $('#msgVazio').hide();
    }
  }

  // ---- Adicionar livro (da lista para a caixa) ----
  $(document).on('click', '.item-disponivel', function() {
    if (!$('#iCliente').val()) {
      alert('Selecione o cliente antes de escolher os livros.');
      return;
    }
    if (selecionados() >= limiteDisponivel()) {
      var jaTem = pendentesPorCliente[$('#iCliente').val()] || 0;
      if (jaTem > 0) {
        alert('Este cliente já está com ' + jaTem + ' livro(s). O limite é de ' + LIMITE + ' por cliente.');
      } else {
        alert('Limite de ' + LIMITE + ' livros por cliente atingido.');
      }
      return;
    }

    var id     = $(this).data('id');
    var titulo = $(this).data('titulo');

    // Remove da lista de disponíveis
    $(this).remove();

    // Adiciona na caixa de selecionados
    $('#caixaSelecionados').append(
      '<div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 mb-1 item-selecionado" data-id="' + id + '" data-titulo="' + titulo + '">' +
        '<span><i class="fas fa-book text-primary mr-2"></i>' + titulo + ' <small class="text-muted">(Cód: ' + id + ')</small></span>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-0 remover-item" title="Remover"><i class="fas fa-times-circle"></i></button>' +
      '</div>'
    );

    // Cria input oculto para envio
    $('#inputsExemplares').append('<input type="hidden" name="nExemplares[]" value="' + id + '" id="hid' + id + '">');

    atualizaContador();
  });

  // ---- Remover livro (da caixa de volta para a lista) ----
  $(document).on('click', '.remover-item', function() {
    var item   = $(this).closest('.item-selecionado');
    var id     = item.data('id');
    var titulo = item.data('titulo');

    item.remove();
    $('#hid' + id).remove();

    // Devolve para a lista de disponíveis
    $('#listaDisponiveis').append(
      '<button type="button" class="list-group-item list-group-item-action py-2 item-disponivel" data-id="' + id + '" data-titulo="' + titulo + '">' +
        '<i class="fas fa-plus-circle text-success mr-2"></i>' + titulo + ' <small class="text-muted">(Cód: ' + id + ')</small>' +
      '</button>'
    );

    atualizaContador();
  });

  // ---- Busca na lista de disponíveis ----
  $('#buscaLivro').on('keyup', function() {
    var termo = $(this).val().toLowerCase();
    $('#listaDisponiveis .item-disponivel').each(function() {
      var txt = $(this).text().toLowerCase();
      $(this).toggle(txt.indexOf(termo) !== -1);
    });
  });

  // ---- Ao trocar de cliente, limpa a seleção e recalcula ----
  $('#iCliente').on('change', function() {
    $('#caixaSelecionados .item-selecionado').each(function() {
      $(this).find('.remover-item').click();
    });
    atualizaContador();
  });

  // ---- Validação no envio ----
  $('#formNovoEmprestimo').on('submit', function(e) {
    if (selecionados() === 0) {
      e.preventDefault();
      alert('Adicione pelo menos um livro para registrar o empréstimo.');
    }
  });

  // Auto-preenche devolução = empréstimo + 7 dias
  $('#iDataEmprestimo').on('change', function() {
    var d = new Date(this.value + 'T00:00:00');
    d.setDate(d.getDate() + 7);
    var yyyy = d.getFullYear();
    var mm   = String(d.getMonth() + 1).padStart(2, '0');
    var dd   = String(d.getDate()).padStart(2, '0');
    $('#iDataPrevista').val(yyyy + '-' + mm + '-' + dd);
  });

});
</script>

</body>
</html>