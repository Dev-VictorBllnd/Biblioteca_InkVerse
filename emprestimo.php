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
  <style>
    .livro-item { border:1px solid #dee2e6; border-radius:6px; padding:10px 14px; margin-bottom:8px; cursor:pointer; transition:background .15s; }
    .livro-item:hover { background:#f8f9fa; }
    .livro-item.selecionado { border-color:#2563eb; background:#eff4ff; }
    .livro-item.atrasado.selecionado { border-color:#dc3545; background:#fff5f5; }
    .livro-item input[type=checkbox] { width:16px; height:16px; cursor:pointer; }
    .painel-acao { background:#f8f9fa; border-top:1px solid #dee2e6; padding:12px 16px; }
    .painel-acao .info-selecionados { font-size:.85rem; color:#6c757d; }
    /* Campo de data dentro do item do livro */
    .livro-data-renovar { display:none; margin-top:8px; }
    .livro-item.selecionado .livro-data-renovar { display:block; }
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

            // Livros ativos por cliente (para aviso no modal de novo empréstimo)
            $livrosPorCliente = array();
            $qLivCli = mysqli_query($conn, "
              SELECT e.idCliente, l.Titulo, ehe.idExemplar
              FROM emprestimo e
              INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
              INNER JOIN exemplar ex ON ex.idExemplar = ehe.idExemplar
              INNER JOIN livro l ON l.idLivro = ex.idLivro
              WHERE ehe.Data_devolucao IS NULL
              ORDER BY e.idCliente, l.Titulo
            ");
            if($qLivCli){
              while($lc = mysqli_fetch_assoc($qLivCli)){
                $livrosPorCliente[$lc['idCliente']][] = $lc['Titulo'].' (Cód: '.$lc['idExemplar'].')';
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
                $diasAtr   = ($r['data_prevista'] < $hoje) ? (int)floor((strtotime($hoje) - strtotime($r['data_prevista'])) / 86400) : 0;
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
                    $idEmp  = $emp['idEmprestimo'];
                    $livros = $emp['livros'];
                    $temAtrasado  = false;
                    $temMultaPaga = false;
                    foreach($livros as $lv){
                      if($lv['atrasado'])  $temAtrasado  = true;
                      if($lv['multaPaga']) $temMultaPaga = true;
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
                          <?php if($lv['atrasado']): ?>
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
                        $qtdNoPrazo = $qtdAtrasado = $qtdMultaPaga = 0;
                        foreach($livros as $lv){
                          if($lv['multaPaga'])    $qtdMultaPaga++;
                          elseif($lv['atrasado']) $qtdAtrasado++;
                          else                    $qtdNoPrazo++;
                        }
                      ?>
                      <?php if($qtdAtrasado > 0): ?>
                        <span class="badge badge-danger d-block mb-1"><?php echo $qtdAtrasado; ?> atrasado<?php echo $qtdAtrasado>1?'s':''; ?></span>
                      <?php endif; ?>
                      <?php if($qtdNoPrazo > 0): ?>
                        <span class="badge text-white d-block mb-1" style="background-color:#2563eb;"><?php echo $qtdNoPrazo; ?> no prazo</span>
                      <?php endif; ?>
                      <?php if($qtdMultaPaga > 0): ?>
                        <span class="badge badge-success d-block mb-1"><?php echo $qtdMultaPaga; ?> multa<?php echo $qtdMultaPaga>1?'s':''; ?> paga<?php echo $qtdMultaPaga>1?'s':''; ?></span>
                      <?php endif; ?>
                    </td>
                    <td class="text-center align-middle">
                      <!-- Único botão de ações — abre o painel unificado -->
                      <button class="btn btn-sm btn-link text-secondary" title="Gerenciar livros"
                              data-toggle="modal" data-target="#painelAcoes<?php echo $idEmp; ?>">
                        <i class="fas fa-tasks"></i>
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

        <!-- ══════════════════════════════════════════════════════
             PAINEL UNIFICADO — um por empréstimo
             Checkboxes nos livros + botões de ação no rodapé
        ══════════════════════════════════════════════════════ -->
        <?php foreach($emprestimos as $emp):
          $idEmp  = $emp['idEmprestimo'];
          $livros = $emp['livros'];
          $temAtrasado = false;
          foreach($livros as $lv){ if($lv['atrasado']) $temAtrasado = true; }
        ?>

        <div class="modal fade" id="painelAcoes<?php echo $idEmp; ?>" tabindex="-1">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">

              <div class="modal-header text-white" style="background-color:#0b1a2c;">
                <h5 class="modal-title">
                  <i class="fas fa-tasks mr-1"></i>
                  Empréstimo #<?php echo $idEmp; ?> — <?php echo htmlspecialchars($emp['Cliente']); ?>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
              </div>

              <div class="modal-body pb-0">
                <p class="text-muted small mb-3">Marque os livros e escolha a ação no rodapé.</p>

                <!-- Lista de livros com checkbox -->
                <?php foreach($livros as $lv):
                  $chk = 'chk_'.$idEmp.'_'.$lv['idExemplar'];
                  $diasAtr = $lv['diasAtraso'];
                ?>
                <div class="livro-item <?php echo $lv['atrasado'] ? 'atrasado' : ''; ?>"
                     id="item_<?php echo $idEmp.'_'.$lv['idExemplar']; ?>"
                     onclick="toggleLivro(this, '<?php echo $idEmp; ?>', '<?php echo $lv['idExemplar']; ?>')">
                  <div class="d-flex align-items-start">
                    <input type="checkbox" class="mr-3 mt-1 chk-livro"
                           id="<?php echo $chk; ?>"
                           data-emp="<?php echo $idEmp; ?>"
                           data-exemplar="<?php echo $lv['idExemplar']; ?>"
                           data-atrasado="<?php echo $lv['atrasado'] ? '1' : '0'; ?>"
                           data-multa="<?php echo number_format($lv['valorMulta'],2,'.','.'); ?>"
                           data-prevista="<?php echo substr($lv['data_prevista'],0,10); ?>"
                           onclick="event.stopPropagation();"
                           onchange="toggleLivroByCheck(this)">
                    <div class="flex-grow-1">
                      <div class="font-weight-bold">
                        <?php echo htmlspecialchars($lv['Titulo']); ?>
                        <small class="text-muted font-weight-normal">(Cód: <?php echo $lv['idExemplar']; ?>)</small>
                        <?php if($lv['atrasado']): ?>
                          <span class="badge badge-danger ml-1"><?php echo $diasAtr; ?>d atraso · R$ <?php echo number_format($lv['valorMulta'],2,',','.'); ?></span>
                        <?php elseif($lv['multaPaga']): ?>
                          <span class="badge badge-success ml-1">Multa paga</span>
                        <?php else: ?>
                          <span class="badge ml-1 text-white" style="background-color:#2563eb;">No prazo</span>
                        <?php endif; ?>
                      </div>
                      <small class="text-muted">
                        Retirada: <?php echo date('d/m/Y', strtotime($lv['Data_emprestimo'])); ?> &bull;
                        Dev. prev.: <?php echo date('d/m/Y', strtotime($lv['data_prevista'])); ?>
                      </small>
                      <!-- Campo de nova data (só aparece quando item está selecionado no modo renovar) -->
                      <div class="livro-data-renovar">
                        <label class="small mb-0 mt-1">Nova data de devolução:</label>
                        <input type="date" class="form-control form-control-sm input-nova-data"
                               value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>"
                               min="<?php echo $hoje; ?>"
                               onclick="event.stopPropagation();">
                      </div>
                    </div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div><!-- /.modal-body -->

              <!-- Painel de ações fixo no rodapé -->
              <div class="painel-acao">
                <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap:8px;">
                  <span class="info-selecionados" id="info_<?php echo $idEmp; ?>">Nenhum livro selecionado</span>
                  <div class="d-flex" style="gap:6px;">
                    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Fechar</button>

                    <!-- Devolver selecionados -->
                    <button type="button" class="btn btn-sm text-white btn-acao-painel"
                            style="background-color:#2563eb;"
                            title="Devolver livros selecionados"
                            onclick="executarAcao('D', <?php echo $idEmp; ?>)">
                      <i class="fas fa-undo mr-1"></i>Devolver
                    </button>

                    <!-- Renovar selecionados -->
                    <button type="button" class="btn btn-sm btn-info btn-acao-painel"
                            title="Renovar prazo dos livros selecionados"
                            onclick="toggleModoRenovar(<?php echo $idEmp; ?>)">
                      <i class="fas fa-sync-alt mr-1"></i>Renovar
                    </button>

                    <!-- Confirmar renovação (aparece após clicar Renovar) -->
                    <button type="button" class="btn btn-sm btn-success btn-confirmar-renovar d-none"
                            id="btnConfRenovar_<?php echo $idEmp; ?>"
                            onclick="executarAcao('U', <?php echo $idEmp; ?>)">
                      <i class="fas fa-save mr-1"></i>Confirmar Renovação
                    </button>

                    <!-- Pagar multa selecionados (só aparece se tiver atrasado) -->
                    <?php if($temAtrasado): ?>
                    <button type="button" class="btn btn-sm btn-danger btn-acao-painel"
                            title="Pagar multa dos livros selecionados"
                            onclick="executarAcao('M', <?php echo $idEmp; ?>)">
                      <i class="fas fa-dollar-sign mr-1"></i>Pagar Multa
                    </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>

            </div>
          </div>
        </div>

        <?php endforeach; ?>

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
                      <!-- Campo hidden confirmando posse dos livros -->
                      <input type="hidden" name="nConfirmaPosse" id="confirmaPosse" value="">
                    </div>
                    <div id="blocoLivros" class="col-md-6">
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
                    </div><!-- /#blocoLivros -->
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
var livrosPorCliente    = <?php echo json_encode($livrosPorCliente); ?>;
var LIMITE = <?php echo $LIMITE_CLIENTE; ?>;
var clienteConfirmado  = {}; // guarda quais clientes já confirmaram posse
var modoRenovar = {}; // controla se painel está em modo renovar

// ── Toggle de item pelo clique na div ──
function toggleLivro(el, idEmp, idEx) {
  var chk = document.getElementById('chk_' + idEmp + '_' + idEx);
  chk.checked = !chk.checked;
  atualizaItem(el, chk);
  atualizaInfo(idEmp);
}

// ── Toggle de item pelo próprio checkbox ──
function toggleLivroByCheck(chk) {
  var el = document.getElementById('item_' + chk.dataset.emp + '_' + chk.dataset.exemplar);
  atualizaItem(el, chk);
  atualizaInfo(chk.dataset.emp);
}

function atualizaItem(el, chk) {
  if (chk.checked) {
    el.classList.add('selecionado');
  } else {
    el.classList.remove('selecionado');
  }
}

// ── Atualiza contador de selecionados no rodapé ──
function atualizaInfo(idEmp) {
  var checks   = document.querySelectorAll('#painelAcoes' + idEmp + ' .chk-livro:checked');
  var total    = checks.length;
  var infoEl   = document.getElementById('info_' + idEmp);
  if (total === 0) {
    infoEl.textContent = 'Nenhum livro selecionado';
  } else {
    infoEl.textContent = total + ' livro(s) selecionado(s)';
  }
}

// ── Alterna modo renovar (mostra campos de data) ──
function toggleModoRenovar(idEmp) {
  modoRenovar[idEmp] = !modoRenovar[idEmp];
  var campos  = document.querySelectorAll('#painelAcoes' + idEmp + ' .livro-data-renovar');
  var btnConf = document.getElementById('btnConfRenovar_' + idEmp);
  campos.forEach(function(c) {
    c.style.display = modoRenovar[idEmp] ? 'block' : 'none';
  });
  if (modoRenovar[idEmp]) {
    btnConf.classList.remove('d-none');
  } else {
    btnConf.classList.add('d-none');
  }
}

// ── Executa a ação para os livros marcados ──
function executarAcao(funcao, idEmp) {
  var checks    = document.querySelectorAll('#painelAcoes' + idEmp + ' .chk-livro:checked');
  var todosChks = document.querySelectorAll('#painelAcoes' + idEmp + ' .chk-livro');

  // Verifica se há livro atrasado no empréstimo inteiro (não só selecionados)
  var temAtrasadoNoEmp = false;
  todosChks.forEach(function(chk) { if (chk.dataset.atrasado === '1') temAtrasadoNoEmp = true; });

  if (checks.length === 0) {
    alert('Selecione pelo menos um livro.');
    return;
  }

  // ── DEVOLVER ──
  if (funcao === 'D') {
    // Não deixa devolver livro atrasado (precisa pagar multa antes)
    var tentandoAtrasado = false;
    checks.forEach(function(chk) { if (chk.dataset.atrasado === '1') tentandoAtrasado = true; });
    if (tentandoAtrasado) {
      alert('Não é possível devolver livros com multa em aberto.\nPague a multa primeiro.');
      return;
    }
    if (!confirm('Confirmar devolução de ' + checks.length + ' livro(s)?')) return;
    var pendentes = checks.length;
    checks.forEach(function(chk) {
      var fd = new FormData();
      fd.append('idEmprestimo', idEmp);
      fd.append('idExemplar',   chk.dataset.exemplar);
      fetch('php/salvarEmprestimo.php?funcao=D', { method: 'POST', body: fd })
        .then(function() { pendentes--; if (pendentes === 0) location.reload(); });
    });
    return;
  }

  // ── RENOVAR ──
  if (funcao === 'U') {
    // Bloqueia renovar se o empréstimo tiver qualquer livro com multa
    if (temAtrasadoNoEmp) {
      alert('Não é possível renovar enquanto houver livros com multa em aberto neste empréstimo.\nPague a multa primeiro.');
      return;
    }
    var algumSemData = false;
    checks.forEach(function(chk) {
      var item    = document.getElementById('item_' + idEmp + '_' + chk.dataset.exemplar);
      var inputDt = item.querySelector('.input-nova-data');
      if (!inputDt.value || inputDt.value < '<?php echo $hoje; ?>') algumSemData = true;
    });
    if (algumSemData) {
      alert('Preencha datas válidas (a partir de hoje) para todos os livros selecionados.');
      return;
    }
    if (!confirm('Renovar prazo de ' + checks.length + ' livro(s)?')) return;
    var pendentes = checks.length;
    checks.forEach(function(chk) {
      var item    = document.getElementById('item_' + idEmp + '_' + chk.dataset.exemplar);
      var inputDt = item.querySelector('.input-nova-data');
      var fd = new FormData();
      fd.append('idEmprestimo',  idEmp);
      fd.append('idExemplar',    chk.dataset.exemplar);
      fd.append('nDataPrevista', inputDt.value);
      fetch('php/salvarEmprestimo.php?funcao=U', { method: 'POST', body: fd })
        .then(function() { pendentes--; if (pendentes === 0) location.reload(); });
    });
    return;
  }

  // ── PAGAR MULTA ──
  if (funcao === 'M') {
    var tentandoSemMulta = false;
    checks.forEach(function(chk) { if (chk.dataset.atrasado !== '1') tentandoSemMulta = true; });
    if (tentandoSemMulta) {
      alert('Selecione apenas livros com multa em aberto para usar esta ação.');
      return;
    }
    if (!confirm('Confirmar pagamento de multa e devolução de ' + checks.length + ' livro(s)?')) return;
    var pendentes = checks.length;
    checks.forEach(function(chk) {
      var fd = new FormData();
      fd.append('idEmprestimo', idEmp);
      fd.append('idExemplar',   chk.dataset.exemplar);
      fd.append('nValorMulta',  chk.dataset.multa);
      fetch('php/salvarEmprestimo.php?funcao=M', { method: 'POST', body: fd })
        .then(function() { pendentes--; if (pendentes === 0) location.reload(); });
    });
    return;
  }
}

// ── Limpa seleção ao fechar o modal ──
document.querySelectorAll('[id^="painelAcoes"]').forEach(function(modal) {
  modal.addEventListener('hidden.bs.modal', function() {
    var idEmp = modal.id.replace('painelAcoes', '');
    modal.querySelectorAll('.chk-livro').forEach(function(c) { c.checked = false; });
    modal.querySelectorAll('.livro-item').forEach(function(el) { el.classList.remove('selecionado'); });
    modal.querySelectorAll('.livro-data-renovar').forEach(function(c) { c.style.display = 'none'; });
    var btnConf = document.getElementById('btnConfRenovar_' + idEmp);
    if(btnConf) btnConf.classList.add('d-none');
    modoRenovar[idEmp] = false;
    var infoEl = document.getElementById('info_' + idEmp);
    if(infoEl) infoEl.textContent = 'Nenhum livro selecionado';
  });
});

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
    return LIMITE - ((idCli && pendentesPorCliente[idCli]) ? pendentesPorCliente[idCli] : 0);
  }
  function selecionados() { return $('#caixaSelecionados .item-selecionado').length; }
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
      alert(jaTem > 0 ? 'Este cliente já está com ' + jaTem + ' livro(s). O limite é de ' + LIMITE + ' por cliente.' : 'Limite de ' + LIMITE + ' livros por cliente atingido.');
      return;
    }
    var id = $(this).data('id'), titulo = $(this).data('titulo');
    $(this).remove();
    $('#caixaSelecionados').append('<div class="d-flex justify-content-between align-items-center bg-white border rounded px-2 py-1 mb-1 item-selecionado" data-id="'+id+'" data-titulo="'+titulo+'"><span><i class="fas fa-book text-primary mr-2"></i>'+titulo+' <small class="text-muted">(Cód: '+id+')</small></span><button type="button" class="btn btn-sm btn-link text-danger p-0 remover-item"><i class="fas fa-times-circle"></i></button></div>');
    $('#inputsExemplares').append('<input type="hidden" name="nExemplares[]" value="'+id+'" id="hid'+id+'">');
    atualizaContador();
  });

  $(document).on('click', '.remover-item', function() {
    var item = $(this).closest('.item-selecionado');
    var id = item.data('id'), titulo = item.data('titulo');
    item.remove(); $('#hid'+id).remove();
    $('#listaDisponiveis').append('<button type="button" class="list-group-item list-group-item-action py-2 item-disponivel" data-id="'+id+'" data-titulo="'+titulo+'"><i class="fas fa-plus-circle text-success mr-2"></i>'+titulo+' <small class="text-muted">(Cód: '+id+')</small></button>');
    atualizaContador();
  });

  $('#buscaLivro').on('keyup', function() {
    var termo = $(this).val().toLowerCase();
    $('#listaDisponiveis .item-disponivel').each(function() { $(this).toggle($(this).text().toLowerCase().indexOf(termo) !== -1); });
  });

  $('#iCliente').on('change', function() {
    // Limpa seleção anterior e reseta confirmação
    $('#caixaSelecionados .item-selecionado').each(function(){ $(this).find('.remover-item').click(); });
    $('#confirmaPosse').val('');
    atualizaContador();
  });

  $('#formNovoEmprestimo').on('submit', function(e) {
    if (selecionados() === 0) {
      e.preventDefault();
      alert('Adicione pelo menos um livro para registrar o empéstimo.');
      return;
    }

    var idCli = $('#iCliente').val();
    var jaTem = pendentesPorCliente[idCli] ? pendentesPorCliente[idCli] : 0;

    // Tem livros em mãos e ainda não confirmou posse
    if (jaTem > 0 && $('#confirmaPosse').val() !== 'sim') {
      e.preventDefault();

      // Monta lista dos livros em mãos
      var livros = livrosPorCliente[idCli] || [];
      var lista  = livros.map(function(t){ return '• ' + t; }).join('\n');

      var msg = 'Este cliente já possui ' + jaTem + ' livro(s) emprestado(s):\n' + lista + '\n\nO cliente está com esses livros em mãos?';
      if (confirm(msg)) {
        // Confirmou posse — salva e submete
        $('#confirmaPosse').val('sim');
        $(this).submit();
      }
      // Se cancelou, não faz nada (modal continua aberto)
    }
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