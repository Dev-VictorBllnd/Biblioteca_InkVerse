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
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
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

            $emprestimos = array();
            $qEmp = mysqli_query($conn, "
              SELECT
                c.idCliente,
                c.Nome AS Cliente,
                e.idEmprestimo,
                ehe.idExemplar,
                l.Titulo,
                ehe.Data_emprestimo,
                ehe.data_prevista,
                ehe.multa AS multaExemplar
              FROM cliente c
              INNER JOIN emprestimo e ON c.idCliente = e.idCliente
              INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
              INNER JOIN exemplar ex ON ex.idExemplar = ehe.idExemplar
              INNER JOIN livro l ON l.idLivro = ex.idLivro
              WHERE ehe.Data_devolucao IS NULL
              ORDER BY e.idEmprestimo ASC, ehe.data_prevista ASC
            ");
            if($qEmp){
              while($r = mysqli_fetch_assoc($qEmp)){
                $id = $r['idEmprestimo'];
                if(!isset($emprestimos[$id])){
                  $emprestimos[$id] = [
                    'idEmprestimo' => $id,
                    'idCliente'    => $r['idCliente'],
                    'Cliente'      => $r['Cliente'],
                    'livros'       => []
                  ];
                }
                $diasAtr = ($r['data_prevista'] < $hoje)
                  ? (int)floor((strtotime($hoje) - strtotime($r['data_prevista'])) / 86400)
                  : 0;
                $multaPaga = ((float)$r['multaExemplar'] > 0);
                $emprestimos[$id]['livros'][] = [
                  'idExemplar'      => $r['idExemplar'],
                  'Titulo'          => $r['Titulo'],
                  'Data_emprestimo' => $r['Data_emprestimo'],
                  'data_prevista'   => $r['data_prevista'],
                  'atrasado'        => ($r['data_prevista'] < $hoje && !$multaPaga),
                  'diasAtraso'      => $diasAtr,
                  'valorMulta'      => $diasAtr * 1.00,
                  'multaPaga'       => $multaPaga,
                ];
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
                    <button type="button" class="btn text-white" style="background-color: #2563eb;"
                            data-toggle="modal" data-target="#novoEmprestimoModal">
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
                      <th style="width:33%;">Livros</th>
                      <th style="width:10%;">Status</th>
                      <th style="width:11%; text-align:center;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>

                  <?php foreach($emprestimos as $emp):
                    $idEmp = $emp['idEmprestimo'];
                    $livros = $emp['livros'];

                    $temAtrasado  = false;
                    $temMultaPaga = false;
                    foreach($livros as $lv){
                      if($lv['atrasado'])   $temAtrasado  = true;
                      if($lv['multaPaga'])  $temMultaPaga = true;
                    }
                  ?>
                  <tr class="<?php echo $temAtrasado ? 'table-danger' : ''; ?>">
                    <td class="align-middle"><?php echo $idEmp; ?></td>
                    <td class="align-middle"><?php echo htmlspecialchars($emp['Cliente']); ?></td>
                    <td class="align-middle">
                      <?php foreach($livros as $lv): ?>
                        <div>
                          <i class="fas fa-book text-muted mr-1"></i>
                          <?php echo htmlspecialchars($lv['Titulo']); ?>
                          <small class="text-muted">(Cód: <?php echo $lv['idExemplar']; ?>)</small>
                          <?php if($lv['atrasado'] && !$lv['multaPaga']): ?>
                            <span class="badge badge-danger ml-1"><?php echo $lv['diasAtraso']; ?>d atraso</span>
                          <?php elseif($lv['multaPaga']): ?>
                            <span class="badge badge-success ml-1">Multa paga</span>
                          <?php else: ?>
                            <span class="badge ml-1 text-white" style="background-color:#2563eb;">No prazo</span>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </td>
                    <td class="align-middle">
                      <?php
                        $qtdNoPrazo   = 0;
                        $qtdAtrasado  = 0;
                        $qtdMultaPaga = 0;
                        foreach($livros as $lv){
                          if($lv['multaPaga'])       $qtdMultaPaga++;
                          elseif($lv['atrasado'])    $qtdAtrasado++;
                          else                       $qtdNoPrazo++;
                        }
                      ?>
                      <?php if($qtdAtrasado > 0): ?>
                        <span class="badge badge-danger d-block mb-1">
                          <?php echo $qtdAtrasado; ?> atrasado<?php echo $qtdAtrasado > 1 ? 's' : ''; ?>
                        </span>
                      <?php endif; ?>
                      <?php if($qtdNoPrazo > 0): ?>
                        <span class="badge text-white d-block mb-1" style="background-color:#2563eb;">
                          <?php echo $qtdNoPrazo; ?> no prazo
                        </span>
                      <?php endif; ?>
                      <?php if($qtdMultaPaga > 0): ?>
                        <span class="badge badge-success d-block mb-1">
                          <?php echo $qtdMultaPaga; ?> multa<?php echo $qtdMultaPaga > 1 ? 's' : ''; ?> paga<?php echo $qtdMultaPaga > 1 ? 's' : ''; ?>
                        </span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <?php if($temAtrasado): ?>
                        <button class="btn btn-sm btn-link text-danger mr-1" title="Pagar Multa"
                                data-toggle="modal" data-target="#seletorMulta<?php echo $idEmp; ?>">
                          <i class="fas fa-dollar-sign"></i>
                        </button>
                      <?php else: ?>
                        <button class="btn btn-sm btn-link text-info mr-1" title="Renovar Prazo"
                                data-toggle="modal" data-target="#seletorRenovar<?php echo $idEmp; ?>">
                          <i class="fas fa-sync-alt"></i>
                        </button>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-link text-success" title="Registrar Devolução"
                              data-toggle="modal" data-target="#seletorDevolver<?php echo $idEmp; ?>">
                        <i class="fas fa-undo"></i>
                      </button>
                    </td>
                  </tr>
                  <?php endforeach; ?>

                  <?php if(empty($emprestimos)): ?>
                    <tr>
                      <td colspan="5" class="text-center text-muted py-3">
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

        <?php foreach($emprestimos as $emp):
          $idEmp = $emp['idEmprestimo'];
          $livros = $emp['livros'];
          $temAtrasado = false;
          foreach($livros as $lv){ if($lv['atrasado']) $temAtrasado = true; }
        ?>

        <!-- ══ SELETOR RENOVAR ══ -->
        <div class="modal fade" id="seletorRenovar<?php echo $idEmp; ?>">
          <div class="modal-dialog" style="max-width:430px;">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h5 class="modal-title"><i class="fas fa-sync-alt mr-1"></i> Renovar — Empréstimo #<?php echo $idEmp; ?> · <?php echo htmlspecialchars($emp['Cliente']); ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body py-3">
                <p class="text-muted small mb-3">Escolha o livro cujo prazo deseja renovar:</p>
                <?php foreach($livros as $lv):
                  $chave = $idEmp.'_'.$lv['idExemplar'];
                ?>
                <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2"
                     style="cursor:pointer;"
                     onmouseover="this.style.background='#e8f0fe'" onmouseout="this.style.background=''"
                     onclick="$('#seletorRenovar<?php echo $idEmp; ?>').modal('hide');setTimeout(function(){$('#modalRenovar<?php echo $chave; ?>').modal('show');},400);">
                  <div>
                    <div class="font-weight-bold"><?php echo htmlspecialchars($lv['Titulo']); ?></div>
                    <small class="text-muted">
                      Retirada: <?php echo date('d/m/Y', strtotime($lv['Data_emprestimo'])); ?> &bull;
                      Dev. prev.: <?php echo date('d/m/Y', strtotime($lv['data_prevista'])); ?>
                    </small>
                  </div>
                  <i class="fas fa-chevron-right text-muted ml-3"></i>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="modal-footer py-2">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ══ SELETOR DEVOLVER ══ -->
        <div class="modal fade" id="seletorDevolver<?php echo $idEmp; ?>">
          <div class="modal-dialog" style="max-width:430px;">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h5 class="modal-title"><i class="fas fa-undo mr-1"></i> Devolver — Empréstimo #<?php echo $idEmp; ?> · <?php echo htmlspecialchars($emp['Cliente']); ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body py-3">
                <p class="text-muted small mb-3">Escolha o livro a ser devolvido:</p>
                <?php foreach($livros as $lv):
                  $chave = $idEmp.'_'.$lv['idExemplar'];
                ?>
                <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2"
                     style="cursor:pointer;"
                     onmouseover="this.style.background='#d4edda'" onmouseout="this.style.background=''"
                     onclick="$('#seletorDevolver<?php echo $idEmp; ?>').modal('hide');setTimeout(function(){$('#modalDevolver<?php echo $chave; ?>').modal('show');},400);">
                  <div>
                    <div class="font-weight-bold"><?php echo htmlspecialchars($lv['Titulo']); ?></div>
                    <small class="text-muted">
                      Retirada: <?php echo date('d/m/Y', strtotime($lv['Data_emprestimo'])); ?> &bull;
                      Dev. prev.: <?php echo date('d/m/Y', strtotime($lv['data_prevista'])); ?>
                      <?php if($lv['atrasado']): ?>
                        &bull; <span class="text-danger font-weight-bold"><?php echo $lv['diasAtraso']; ?>d atraso</span>
                      <?php endif; ?>
                    </small>
                  </div>
                  <i class="fas fa-chevron-right text-muted ml-3"></i>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="modal-footer py-2">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
              </div>
            </div>
          </div>
        </div>

        <!-- ══ SELETOR MULTA ══ -->
        <?php if($temAtrasado): ?>
        <div class="modal fade" id="seletorMulta<?php echo $idEmp; ?>">
          <div class="modal-dialog" style="max-width:430px;">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h5 class="modal-title"><i class="fas fa-dollar-sign mr-1"></i> Multas — Empréstimo #<?php echo $idEmp; ?> · <?php echo htmlspecialchars($emp['Cliente']); ?></h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body py-3">
                <p class="text-muted small mb-3">Escolha o livro para pagar a multa:</p>
                <?php foreach($livros as $lv):
                  if(!$lv['atrasado'] || $lv['multaPaga']) continue;
                  $chave = $idEmp.'_'.$lv['idExemplar'];
                ?>
                <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2 mb-2"
                     style="cursor:pointer;"
                     onmouseover="this.style.background='#fff3cd'" onmouseout="this.style.background=''"
                     onclick="$('#seletorMulta<?php echo $idEmp; ?>').modal('hide');setTimeout(function(){$('#modalMulta<?php echo $chave; ?>').modal('show');},400);">
                  <div>
                    <div class="font-weight-bold"><?php echo htmlspecialchars($lv['Titulo']); ?></div>
                    <small class="text-muted">
                      Dev. prev.: <?php echo date('d/m/Y', strtotime($lv['data_prevista'])); ?> &bull;
                      <span class="text-danger"><?php echo $lv['diasAtraso']; ?> dia(s) em atraso</span>
                    </small>
                  </div>
                  <div class="text-right ml-3">
                    <span class="text-danger font-weight-bold">R$ <?php echo number_format($lv['valorMulta'],2,',','.'); ?></span>
                    <br><i class="fas fa-chevron-right text-muted small"></i>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
              <div class="modal-footer py-2">
                <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Fechar</button>
              </div>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <!-- ══ MODAIS DE AÇÃO POR LIVRO ══ -->
        <?php foreach($livros as $lv):
          $chave = $idEmp.'_'.$lv['idExemplar'];
        ?>

        <!-- Modal Renovar -->
        <div class="modal fade" id="modalRenovar<?php echo $chave; ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h4 class="modal-title">Renovar Empréstimo</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <form method="POST" action="php/salvarEmprestimo.php?funcao=U">
                <div class="modal-body">
                  <input type="hidden" name="idEmprestimo" value="<?php echo $idEmp; ?>">
                  <input type="hidden" name="idExemplar"   value="<?php echo $lv['idExemplar']; ?>">
                  <p><strong>Cliente:</strong> <?php echo htmlspecialchars($emp['Cliente']); ?></p>
                  <p><strong>Livro:</strong> <?php echo htmlspecialchars($lv['Titulo']); ?></p>
                  <hr>
                  <div class="form-group">
                    <label>Data do Empréstimo:</label>
                    <input type="text" class="form-control bg-light"
                           value="<?php echo date('d/m/Y', strtotime($lv['Data_emprestimo'])); ?>"
                           disabled readonly>
                    <small class="text-muted">A data de retirada não pode ser alterada.</small>
                  </div>
                  <div class="form-group">
                    <label>Nova Data Prevista para Devolução:</label>
                    <input type="date" class="form-control" name="nDataPrevista"
                           value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>"
                           min="<?php echo $hoje; ?>" required>
                    <small class="text-muted">Não é possível selecionar uma data anterior a hoje.</small>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn text-white" style="background-color:#2563eb;"><i class="fas fa-save"></i> Renovar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal Devolver -->
        <div class="modal fade" id="modalDevolver<?php echo $chave; ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h4 class="modal-title">Confirmar Devolução</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <div class="modal-body">
                <p>Confirmar a devolução do livro abaixo?</p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($emp['Cliente']); ?></p>
                <p><strong>Livro:</strong> <?php echo htmlspecialchars($lv['Titulo']); ?> <small class="text-muted">(Cód: <?php echo $lv['idExemplar']; ?>)</small></p>
                <p><strong>Retirada:</strong> <?php echo date('d/m/Y', strtotime($lv['Data_emprestimo'])); ?></p>
                <p><strong>Devolução prevista:</strong> <?php echo date('d/m/Y', strtotime($lv['data_prevista'])); ?></p>
                <?php if($lv['atrasado']): ?>
                  <p class="text-danger"><strong>Atraso:</strong> <?php echo $lv['diasAtraso']; ?> dia(s)</p>
                <?php endif; ?>
              </div>
              <form method="POST" action="php/salvarEmprestimo.php?funcao=D">
                <input type="hidden" name="idEmprestimo" value="<?php echo $idEmp; ?>">
                <input type="hidden" name="idExemplar"   value="<?php echo $lv['idExemplar']; ?>">
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn text-white" style="background-color:#2563eb;"><i class="fas fa-check"></i> Confirmar Devolução</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- Modal Multa -->
        <?php if($lv['atrasado'] && !$lv['multaPaga']): ?>
        <div class="modal fade" id="modalMulta<?php echo $chave; ?>">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h4 class="modal-title"><i class="fas fa-dollar-sign mr-1"></i> Pagamento de Multa</h4>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>
              <form method="POST" action="php/salvarEmprestimo.php?funcao=M">
                <div class="modal-body">
                  <input type="hidden" name="idEmprestimo" value="<?php echo $idEmp; ?>">
                  <input type="hidden" name="idExemplar"   value="<?php echo $lv['idExemplar']; ?>">
                  <input type="hidden" name="nValorMulta"  value="<?php echo number_format($lv['valorMulta'],2,'.','.'); ?>">
                  <p><strong>Cliente:</strong> <?php echo htmlspecialchars($emp['Cliente']); ?></p>
                  <p><strong>Livro:</strong> <?php echo htmlspecialchars($lv['Titulo']); ?> <small class="text-muted">(Cód: <?php echo $lv['idExemplar']; ?>)</small></p>
                  <p><strong>Devolução prevista:</strong> <?php echo date('d/m/Y', strtotime($lv['data_prevista'])); ?></p>
                  <p><strong>Dias em atraso:</strong> <?php echo $lv['diasAtraso']; ?> dia(s)</p>
                  <hr>
                  <div class="text-center">
                    <small class="text-muted d-block">Valor da multa (R$ 1,00 por dia de atraso)</small>
                    <h2 class="text-danger font-weight-bold mb-0">R$ <?php echo number_format($lv['valorMulta'],2,',','.'); ?></h2>
                  </div>
                  <p class="text-center text-muted mt-3 mb-0"><small><i class="fas fa-info-circle mr-1"></i>Ao confirmar, a multa é paga e o livro é devolvido automaticamente.</small></p>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                  <button type="submit" class="btn text-white" style="background-color:#0b1a2c;"><i class="fas fa-check"></i> Pagar e Devolver</button>
                </div>
              </form>
            </div>
          </div>
        </div>
        <?php endif; ?>

        <?php endforeach; // livros ?>
        <?php endforeach; // emprestimos ?>

        <!-- ══ Modal Novo Empréstimo ══ -->
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
<script src="plugins/select2/js/select2.full.min.js"></script>

<script>
var pendentesPorCliente = <?php echo json_encode($pendentesPorCliente); ?>;
var LIMITE = <?php echo $LIMITE_CLIENTE; ?>;

$(document).ready(function () {

  $('#tabela').DataTable({
    "order": [[0, "asc"]],
    "language": { "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Portuguese-Brasil.json" }
  });

  $('#iCliente').select2({
    theme: 'bootstrap4',
    placeholder: 'Digite ou selecione o cliente...',
    language: { noResults: function(){ return 'Nenhum cliente encontrado'; } },
    dropdownParent: $('#novoEmprestimoModal')
  });

  function limiteDisponivel() {
    var idCli = $('#iCliente').val();
    var jaTem = (idCli && pendentesPorCliente[idCli]) ? pendentesPorCliente[idCli] : 0;
    return LIMITE - jaTem;
  }

  function selecionados() {
    return $('#caixaSelecionados .item-selecionado').length;
  }

  function atualizaContador() {
    var idCli = $('#iCliente').val();
    var jaTem = (idCli && pendentesPorCliente[idCli]) ? pendentesPorCliente[idCli] : 0;
    $('#contadorLivros').text((jaTem + selecionados()) + '/' + LIMITE);
    if (selecionados() === 0) { $('#msgVazio').show(); } else { $('#msgVazio').hide(); }
  }

  $(document).on('click', '.item-disponivel', function() {
    if (!$('#iCliente').val()) { alert('Selecione o cliente antes de escolher os livros.'); return; }
    if (selecionados() >= limiteDisponivel()) {
      var jaTem = pendentesPorCliente[$('#iCliente').val()] || 0;
      alert(jaTem > 0
        ? 'Este cliente já está com ' + jaTem + ' livro(s). O limite é de ' + LIMITE + ' por cliente.'
        : 'Limite de ' + LIMITE + ' livros por cliente atingido.');
      return;
    }
    var id = $(this).data('id'), titulo = $(this).data('titulo');
    $(this).remove();
    $('#caixaSelecionados').append(
      '<div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 mb-1 item-selecionado" data-id="'+id+'" data-titulo="'+titulo+'">' +
        '<span><i class="fas fa-book text-primary mr-2"></i>'+titulo+' <small class="text-muted">(Cód: '+id+')</small></span>' +
        '<button type="button" class="btn btn-sm btn-link text-danger p-0 remover-item"><i class="fas fa-times-circle"></i></button>' +
      '</div>'
    );
    $('#inputsExemplares').append('<input type="hidden" name="nExemplares[]" value="'+id+'" id="hid'+id+'">');
    atualizaContador();
  });

  $(document).on('click', '.remover-item', function() {
    var item = $(this).closest('.item-selecionado');
    var id = item.data('id'), titulo = item.data('titulo');
    item.remove();
    $('#hid'+id).remove();
    $('#listaDisponiveis').append(
      '<button type="button" class="list-group-item list-group-item-action py-2 item-disponivel" data-id="'+id+'" data-titulo="'+titulo+'">' +
        '<i class="fas fa-plus-circle text-success mr-2"></i>'+titulo+' <small class="text-muted">(Cód: '+id+')</small>' +
      '</button>'
    );
    atualizaContador();
  });

  $('#buscaLivro').on('keyup', function() {
    var termo = $(this).val().toLowerCase();
    $('#listaDisponiveis .item-disponivel').each(function() {
      $(this).toggle($(this).text().toLowerCase().indexOf(termo) !== -1);
    });
  });

  $('#iCliente').on('change', function() {
    $('#caixaSelecionados .item-selecionado').each(function(){ $(this).find('.remover-item').click(); });
    atualizaContador();
  });

  $('#formNovoEmprestimo').on('submit', function(e) {
    if (selecionados() === 0) { e.preventDefault(); alert('Adicione pelo menos um livro para registrar o empréstimo.'); }
  });

  $('#iDataEmprestimo').on('change', function() {
    var d = new Date(this.value + 'T00:00:00');
    d.setDate(d.getDate() + 7);
    $('#iDataPrevista').val(d.getFullYear()+'-'+String(d.getMonth()+1).padStart(2,'0')+'-'+String(d.getDate()).padStart(2,'0'));
  });

});
</script>

</body>
</html>