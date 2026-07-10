<?php
session_start();

include('php/conexao.php');
include('php/funcaoFuncionario.php');
include('php/funcaoMenu.php');

$_SESSION['menu-n1'] = 'biblioteca';
$_SESSION['menu-n2'] = 'dashboard';

/* CARDS INDICADORES */
$qLivros = mysqli_query($conn,"SELECT COUNT(*) total FROM exemplar");
$totalLivros = mysqli_fetch_assoc($qLivros)['total'];

$qClientes = mysqli_query($conn,"SELECT COUNT(*) total FROM cliente");
$totalClientes = mysqli_fetch_assoc($qClientes)['total'];

$qEmprestimos = mysqli_query($conn,"SELECT COUNT(*) total FROM emprestimo_has_exemplar WHERE Data_devolucao IS NULL");
$totalEmprestimos = mysqli_fetch_assoc($qEmprestimos)['total'];

// Conta os exemplares fisicamente fora pelos registros reais de empréstimo (não pela flag, que pode desencontrar)
$qExemplares = mysqli_query($conn,"SELECT COUNT(DISTINCT idExemplar) total FROM emprestimo_has_exemplar WHERE Data_devolucao IS NULL");
$totalExemplares = mysqli_fetch_assoc($qExemplares)['total'];

// Nova Consulta: Conta empréstimos onde a data prevista já passou e não foi devolvido
$qAtrasos = mysqli_query($conn, "SELECT COUNT(*) total FROM emprestimo_has_exemplar WHERE Data_devolucao IS NULL AND data_prevista < NOW()");
$totalAtrasos = mysqli_fetch_assoc($qAtrasos)['total'];


/* DADOS PARA OS GRÁFICOS */

// 1. Empréstimos por Gênero (Donut)
$g_labels = []; $g_valores = [];
$qGeneros = mysqli_query($conn, "SELECT g.Descricao, COUNT(ehe.idEmprestimo_controler) AS total FROM emprestimo_has_exemplar ehe JOIN exemplar ex ON ehe.idExemplar = ex.idExemplar JOIN livro l ON ex.idLivro = l.idLivro JOIN genero g ON l.idGenero = g.idGenero GROUP BY g.idGenero");
while($r = mysqli_fetch_assoc($qGeneros)) { $g_labels[] = $r['Descricao']; $g_valores[] = (int)$r['total']; }

// 2. Evolução Mensal (Linha)
$m_labels = []; $m_valores = [];
$qMeses = mysqli_query($conn, "SELECT DATE_FORMAT(Data_emprestimo, '%m/%Y') as mes_ano, COUNT(*) as total FROM emprestimo_has_exemplar GROUP BY mes_ano ORDER BY Data_emprestimo ASC LIMIT 6");
while($r = mysqli_fetch_assoc($qMeses)) { $m_labels[] = $r['mes_ano']; $m_valores[] = (int)$r['total']; }

// 3. Top Clientes (Barras Horizontais)
$c_labels = []; $c_valores = [];
$qTopClientes = mysqli_query($conn, "SELECT c.Nome, COUNT(e.idEmprestimo) as total FROM emprestimo e JOIN cliente c ON e.idCliente = c.idCliente GROUP BY c.idCliente ORDER BY total DESC LIMIT 5");
while($r = mysqli_fetch_assoc($qTopClientes)) { $c_labels[] = $r['Nome']; $c_valores[] = (int)$r['total']; }

// 4. Atendimentos por Funcionário (Radar)
$f_labels = []; $f_valores = [];
$qTopFunc = mysqli_query($conn, "SELECT f.Nome, COUNT(e.idEmprestimo) as total FROM emprestimo e JOIN funcionario f ON e.idFuncionario = f.idFuncionario GROUP BY f.idFuncionario");
while($r = mysqli_fetch_assoc($qTopFunc)) { $f_labels[] = $r['Nome']; $f_valores[] = (int)$r['total']; }

// 5. Livros Adicionados por Ano de Publicação (Barras Verticais)
$a_labels = []; $a_valores = [];
$qAnosLivros = mysqli_query($conn, "SELECT ano, COUNT(*) as total FROM livro GROUP BY ano ORDER BY ano ASC LIMIT 6");
while($r = mysqli_fetch_assoc($qAnosLivros)) { $a_labels[] = $r['ano']; $a_valores[] = (int)$r['total']; }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>InkVerse - Dashboard</title>
    <?php include('partes/css.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Source Sans Pro', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }
        .card { box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.1); border-radius: 0.25rem; margin-bottom: 20px; }
        .card-sistema { background-color: #0b1a2c !important; color: #ffffff !important; position: relative; display: block; margin-bottom: 20px; border-radius: 0.25rem; box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.3); transition: transform 0.2s; }
        .card-sistema:hover { transform: translateY(-3px); text-decoration: none; color: #fff !important; filter: brightness(115%); }
        .card-sistema .inner { padding: 15px 20px; }
        .card-sistema h3 { font-size: 2.2rem; font-weight: 700; margin: 0 0 5px 0; }
        .card-sistema p { font-size: 1rem; margin-bottom: 0; opacity: 0.8; }
        .card-sistema .icon { position: absolute; top: 15px; right: 20px; font-size: 40px; color: rgba(58, 137, 222, 0.25); }
        
        /* Cor de destaque avermelhada e suave para o card de atraso mantendo a identidade visual */
        .card-atraso { background-color: #721c24 !important; border-left: 5px solid #dc3545; }
        .card-atraso .icon { color: rgba(220, 53, 69, 0.25) !important; }

        /* Ajustes visuais para os componentes do DataTables dentro dos Modais */
        .modal .dataTables_wrapper { padding: 15px; }
        .modal .dataTables_filter { text-align: right; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    <?php include('partes/navbar.php'); ?>
    <?php include('partes/sidebar.php'); ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: #0b1a2c; font-weight: 600;">Painel de Controle Estatístico</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <label for="filtroMesOculos" class="mr-2 font-weight-bold text-muted small"><i class="fas fa-filter mr-1"></i> FILTRAR MÊS:</label>
                        <input type="month" id="filtroMesOculos" class="form-control d-inline-block" style="max-width: 200px; color: #0b1a2c; font-weight: 500;">
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="row">
                    <!-- Card Livros - Redireciona para livros.php -->
                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="livros.php" class="card-sistema" style="cursor: pointer;">
                            <div class="inner"><h3><?php echo $totalLivros; ?></h3><p>Livros</p></div>
                            <div class="icon"><i class="fas fa-book"></i></div>
                        </a>
                    </div>
                    <!-- Card Clientes - Redireciona para clientes.php -->
                    <div class="col-lg-2 col-md-4 col-6">
                        <a href="clientes.php" class="card-sistema" style="cursor: pointer;">
                            <div class="inner"><h3><?php echo $totalClientes; ?></h3><p>Clientes</p></div>
                            <div class="icon"><i class="fas fa-users"></i></div>
                        </a>
                    </div>
                    <!-- Card Empréstimos Ativos - Abre Modal Empréstimos Ativos -->
                    <div class="col-lg-3 col-md-4 col-6" data-toggle="modal" data-target="#modalEmprestimosAtivos" style="cursor: pointer;">
                        <div class="card-sistema">
                            <div class="inner"><h3><?php echo $totalEmprestimos; ?></h3><p>Empréstimos Ativos</p></div>
                            <div class="icon"><i class="fas fa-exchange-alt"></i></div>
                        </div>
                    </div>
                    <!-- Card Exemplares Fora - Abre Modal Exemplares Fora -->
                    <div class="col-lg-3 col-md-6 col-6" data-toggle="modal" data-target="#modalExemplaresFora" style="cursor: pointer;">
                        <div class="card-sistema">
                            <div class="inner"><h3><?php echo $totalExemplares; ?></h3><p>Exemplares Fora</p></div>
                            <div class="icon"><i class="fas fa-book-reader"></i></div>
                        </div>
                    </div>
                    <!-- Card Em Atraso - Abre Modal Atrasos -->
                    <div class="col-lg-2 col-md-6 col-12" data-toggle="modal" data-target="#modalAtrasos" style="cursor: pointer;">
                        <div class="card-sistema card-atraso">
                            <div class="inner"><h3><?php echo $totalAtrasos; ?></h3><p>Em Atraso</p></div>
                            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-7">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title" style="color: #0b1a2c; font-weight:600;"><i class="fas fa-chart-line mr-1"></i> Histórico de Empréstimos</h3></div>
                            <div class="card-body"><canvas id="graficoMensal" style="height: 220px;"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title" style="color: #0b1a2c; font-weight:600;"><i class="fas fa-chart-pie mr-1"></i> Gêneros Mais Procurados</h3></div>
                            <div class="card-body"><canvas id="graficoGeneros" style="height: 220px;"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title" style="color: #0b1a2c; font-weight:600;"><i class="fas fa-user-chart mr-1"></i> Top Clientes</h3></div>
                            <div class="card-body"><canvas id="graficoTopClientes" style="height: 220px;"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title" style="color: #0b1a2c; font-weight:600;"><i class="fas fa-id-card mr-1"></i> Envios por Funcionário</h3></div>
                            <div class="card-body"><canvas id="graficoFuncionarios" style="height: 220px;"></canvas></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title" style="color: #0b1a2c; font-weight:600;"><i class="fas fa-calendar-alt mr-1"></i> Acervo por Ano</h3></div>
                            <div class="card-body"><canvas id="graficoAnos" style="height: 220px;"></canvas></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title" style="color: #0b1a2c; font-weight: 600;">
                                    <i class="fas fa-history mr-1"></i> Fluxo de Movimentações Recentes (Empréstimos e Devoluções)
                                </h3>
                                <div class="card-tools">
                                    <a href="php/exportar_dashboard.php" class="btn btn-success btn-sm" style="font-weight: 500;">
                                        <i class="fas fa-file-excel mr-1"></i> Exportar Completo
                                    </a>
                                    <button type="button" class="btn btn-info btn-sm" style="font-weight: 500;" data-toggle="modal" data-target="#modalPeriodoExcel">
                                        <i class="fas fa-calendar-alt mr-1"></i> Exportar por Período
                                    </button>
                                </div>
                            </div>

                            <!-- Modal Excel por Período -->
                            <div class="modal fade" id="modalPeriodoExcel" tabindex="-1" role="dialog" aria-labelledby="modalPeriodoExcelLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #0b1a2c; color: white;">
                                            <h5 class="modal-title" id="modalPeriodoExcelLabel"><i class="fas fa-file-excel mr-2"></i>Exportar Histórico por Período</h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <form action="php/exportar_periodo.php" method="GET">
                                            <div class="modal-body">
                                                <p class="text-muted small">Escolha o intervalo de datas baseado na **Data do Empréstimo**.</p>
                                                <div class="form-group row">
                                                    <div class="col-md-6">
                                                        <label for="data_inicio">Data Inicial:</label>
                                                        <input type="date" id="data_inicio" name="data_inicio" class="form-control" required>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label for="data_fim">Data Final:</label>
                                                        <input type="date" id="data_fim" name="data_fim" class="form-control" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                                <button type="submit" class="btn btn-success"><i class="fas fa-download mr-1"></i> Gerar Excel</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- NOVO MODAL: Empréstimos Ativos -->
                            <div class="modal fade" id="modalEmprestimosAtivos" tabindex="-1" role="dialog" aria-labelledby="modalEmprestimosAtivosLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #0b1a2c; color: white;">
                                            <h5 class="modal-title" id="modalEmprestimosAtivosLabel">
                                                <i class="fas fa-exchange-alt mr-2"></i> Listagem de Empréstimos Ativos
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0" style="max-height: 520px; overflow-y: auto;">
                                            <table id="tabelaEmprestimosAtivos" class="table table-hover m-0" style="width: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Cliente</th>
                                                        <th>Livro (Cópia)</th>
                                                        <th>Data Retirada</th>
                                                        <th>Prazo Limite</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $qAtivos = mysqli_query($conn, "
                                                        SELECT c.Nome as cliente, l.Titulo as livro, ehe.idExemplar as copia, ehe.Data_emprestimo as data_acao, ehe.data_prevista as prazo 
                                                        FROM emprestimo_has_exemplar ehe
                                                        JOIN emprestimo e ON ehe.idEmprestimo = e.idEmprestimo
                                                        JOIN cliente c ON e.idCliente = c.idCliente
                                                        JOIN exemplar ex ON ehe.idExemplar = ex.idExemplar
                                                        JOIN livro l ON ex.idLivro = l.idLivro
                                                        WHERE ehe.Data_devolucao IS NULL
                                                        ORDER BY ehe.Data_emprestimo DESC
                                                    ");
                                                    while($atv = mysqli_fetch_assoc($qAtivos)){
                                                    ?>
                                                        <tr>
                                                            <td><strong><?php echo $atv['cliente']; ?></strong></td>
                                                            <td><?php echo $atv['livro']; ?> <small class="text-muted">(Cód. <?php echo $atv['copia']; ?>)</small></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($atv['data_acao'])); ?></td>
                                                            <td><?php echo date('d/m/Y', strtotime($atv['prazo'])); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar Janela</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- NOVO MODAL: Exemplares Fora -->
                            <div class="modal fade" id="modalExemplaresFora" tabindex="-1" role="dialog" aria-labelledby="modalExemplaresForaLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #1a3350; color: white;">
                                            <h5 class="modal-title" id="modalExemplaresForaLabel">
                                                <i class="fas fa-book-reader mr-2"></i> Livros Atualmente Fora do Acervo
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0" style="max-height: 520px; overflow-y: auto;">
                                            <table id="tabelaExemplaresFora" class="table table-hover m-0" style="width: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Código Cópia</th>
                                                        <th>Título do Livro</th>
                                                        <th>Retirado por</th>
                                                        <th>Data de Saída</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $qFora = mysqli_query($conn, "
                                                        SELECT ehe.idExemplar as copia, l.Titulo as livro, c.Nome as cliente, ehe.Data_emprestimo as data_acao
                                                        FROM emprestimo_has_exemplar ehe
                                                        JOIN emprestimo e ON ehe.idEmprestimo = e.idEmprestimo
                                                        JOIN cliente c ON e.idCliente = c.idCliente
                                                        JOIN exemplar ex ON ehe.idExemplar = ex.idExemplar
                                                        JOIN livro l ON ex.idLivro = l.idLivro
                                                        WHERE ehe.Data_devolucao IS NULL
                                                        ORDER BY ehe.idExemplar ASC
                                                    ");
                                                    while($fra = mysqli_fetch_assoc($qFora)){
                                                    ?>
                                                        <tr>
                                                            <td><span class="badge badge-dark">#<?php echo $fra['copia']; ?></span></td>
                                                            <td><strong><?php echo $fra['livro']; ?></strong></td>
                                                            <td><?php echo $fra['cliente']; ?></td>
                                                            <td><?php echo date('d/m/Y H:i', strtotime($fra['data_acao'])); ?></td>
                                                        </tr>
                                                    <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar Janela</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Modal de Clientes em Atraso -->
                            <div class="modal fade" id="modalAtrasos" tabindex="-1" role="dialog" aria-labelledby="modalAtrasosLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #721c24; color: white;">
                                            <h5 class="modal-title" id="modalAtrasosLabel">
                                                <i class="fas fa-exclamation-triangle mr-2"></i> Relatório Prático de Empréstimos em Atraso
                                            </h5>
                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body p-0" style="max-height: 520px; overflow-y: auto;">
                                            <table id="tabelaAtrasos" class="table table-hover m-0" style="width: 100%;">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Cliente</th>
                                                        <th>Livro / Cópia</th>
                                                        <th>Data Empréstimo</th>
                                                        <th>Prazo Limite</th>
                                                        <th class="text-center">Ação</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $qListaAtrasos = mysqli_query($conn, "
                                                        SELECT 
                                                            c.Nome AS cliente,
                                                            c.Telefone AS telefone,
                                                            l.Titulo AS livro,
                                                            ehe.idExemplar AS copia,
                                                            ehe.Data_emprestimo AS data_acao,
                                                            ehe.data_prevista AS prazo
                                                        FROM emprestimo_has_exemplar ehe
                                                        JOIN emprestimo e ON ehe.idEmprestimo = e.idEmprestimo
                                                        JOIN cliente c ON e.idCliente = c.idCliente
                                                        JOIN exemplar ex ON ehe.idExemplar = ex.idExemplar
                                                        JOIN livro l ON ex.idLivro = l.idLivro
                                                        WHERE ehe.Data_devolucao IS NULL AND ehe.data_prevista < NOW()
                                                        ORDER BY ehe.data_prevista ASC
                                                    ");

                                                    if(mysqli_num_rows($qListaAtrasos) > 0) {
                                                        while($atraso = mysqli_fetch_assoc($qListaAtrasos)){
                                                            $diasAtraso = floor((time() - strtotime($atraso['prazo'])) / (60 * 60 * 24));
                                                            
                                                            $primeiroNome = explode(' ', trim($atraso['cliente']))[0];
                                                            $dataPrazoStr = date('d/m/Y', strtotime($atraso['prazo']));
                                                            
                                                            $numeroLimpo = preg_replace('/[^0-9]/', '', $atraso['telefone']);
                                                            if(strlen($numeroLimpo) == 10 || strlen($numeroLimpo) == 11) {
                                                                $numeroLimpo = '55' . $numeroLimpo;
                                                            }

                                                            $mensagem = "Olá, {$primeiroNome}! Passando para lembrar sobre a devolução do livro *{$atraso['livro']}*, que estava prevista para {$dataPrazoStr}. Qualquer dúvida, estamos à disposição na biblioteca!";
                                                            $linkWhats = "https://api.whatsapp.com/send?phone={$numeroLimpo}&text=" . urlencode($mensagem);
                                                    ?>
                                                            <tr>
                                                                <td><strong><?php echo $atraso['cliente']; ?></strong></td>
                                                                <td><?php echo $atraso['livro']; ?> <small class="text-muted">(Cód. <?php echo $atraso['copia']; ?>)</small></td>
                                                                <td><?php echo date('d/m/Y', strtotime($atraso['data_acao'])); ?></td>
                                                                <td>
                                                                    <span class="text-danger font-weight-bold">
                                                                        <?php echo $dataPrazoStr; ?>
                                                                    </span>
                                                                    <small class="badge badge-danger ml-1"><?php echo $diasAtraso; ?>d atrás</small>
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="<?php echo $linkWhats; ?>" target="_blank" class="btn btn-xs btn-outline-danger" title="Notificar no WhatsApp">
                                                                        <i class="fab fa-whatsapp"></i> Cobrar
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                    <?php 
                                                        }
                                                    } 
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar Janela</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card-body table-responsive p-0">
                                <table id="tabelaMovimentacoes" class="table table-hover text-nowrap">
                                    <thead>
                                        <tr>
                                            <th>Cód. Controle</th>
                                            <th>Cliente</th>
                                            <th>Livro (Cópia)</th>
                                            <th>Data da Operação</th>
                                            <th>Prazo Limite</th>
                                            <th>Tipo / Movimento</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $queryMovimentacoes = mysqli_query($conn, "
                                            SELECT 
                                                ehe.idEmprestimo_controler AS id_controle,
                                                c.Nome AS cliente,
                                                l.Titulo AS livro,
                                                ehe.idExemplar AS copia,
                                                ehe.Data_emprestimo AS data_acao,
                                                ehe.data_prevista AS prazo,
                                                ehe.Data_devolucao AS data_retorno
                                            FROM emprestimo_has_exemplar ehe
                                            JOIN emprestimo e ON ehe.idEmprestimo = e.idEmprestimo
                                            JOIN cliente c ON e.idCliente = c.idCliente
                                            JOIN exemplar ex ON ehe.idExemplar = ex.idExemplar
                                            JOIN livro l ON ex.idLivro = l.idLivro
                                            ORDER BY ehe.Data_emprestimo DESC 
                                        ");

                                        while($mov = mysqli_fetch_assoc($queryMovimentacoes)){
                                        ?>
                                        <tr>
                                            <td><strong>#<?php echo $mov['id_controle']; ?></strong></td>
                                            <td><?php echo $mov['cliente']; ?></td>
                                            <td><?php echo $mov['livro']; ?> <small class="text-muted">(Cód. <?php echo $mov['copia']; ?>)</small></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($mov['data_acao'])); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($mov['prazo'])); ?></td>
                                            <td>
                                                <?php if(empty($mov['data_retorno'])): ?>
                                                    <span class="badge" style="background-color: rgba(11, 26, 44, 0.1); color: #0b1a2c; border: 1px solid rgba(11, 26, 44, 0.2);">
                                                        <i class="fas fa-arrow-up mr-1"></i> Saída (Emprestado)
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge" style="background-color: rgba(40, 167, 69, 0.1); color: #28a745; border: 1px solid rgba(40, 167, 69, 0.2);">
                                                        <i class="fas fa-arrow-down mr-1"></i> Retorno (Devolvido em <?php echo date('d/m', strtotime($mov['data_retorno'])); ?>)
                                                    </span>
                                                <?php endif; ?>
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
</div>

<?php include('partes/js.php'); ?>

<link class="script-dependency" rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
<script class="script-dependency" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script class="script-dependency" src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    // --- SCRIPT DO VALIDADOR DE EXCEL ---
    const formExcel = document.querySelector('#modalPeriodoExcel form');
    if(formExcel) {
        formExcel.addEventListener('submit', function(e) {
            const inicio = document.getElementById('data_inicio').value;
            const fim = document.getElementById('data_fim').value;
            if (inicio && fim && inicio > fim) {
                e.preventDefault();
                alert('A data inicial não pode ser maior que a data final!');
            }
        });
    }

    // --- INICIALIZAÇÃO DO DATATABLES - MOVIMENTAÇÕES ---
    const tabelaMov = $('#tabelaMovimentacoes').DataTable({
        "pageLength": 10,
        "order": [[ 0, "desc" ]],
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" }
    });

    // --- LÓGICA DO FILTRO POR MÊS ---
    document.getElementById('filtroMesOculos').addEventListener('change', function() {
        const val = this.value; 
        if (!val) { tabelaMov.column(3).search('').draw(); return; }
        const partes = val.split('-');
        const mesAnoFormatado = partes[1] + '/' + partes[0];
        tabelaMov.column(3).search(mesAnoFormatado).draw();
    });

    // --- INICIALIZAÇÃO DOS NOVOS DATATABLES ---
    $('#tabelaEmprestimosAtivos').DataTable({
        "pageLength": 5,
        "order": [[ 2, "desc" ]],
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" }
    });

    $('#tabelaExemplaresFora').DataTable({
        "pageLength": 5,
        "order": [[ 0, "asc" ]],
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" }
    });

    $('#tabelaAtrasos').DataTable({
        "pageLength": 5, 
        "order": [[ 3, "asc" ]], 
        "language": { "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json" }
    });

    // Ajustar o layout do grid interno dos tabelas quando qualquer modal carregar
    $('.modal').on('shown.bs.modal', function () {
        $(this).find('table').DataTable().columns.adjust();
    });


    // --- GRÁFICOS ---
    const azulSistema = '#0b1a2c';
    const paletaDeAzuis = ['#0b1a2c', '#1a3350', '#2e4f77', '#466fa1', '#6393cc', '#8cb4e6'];

    // 1. GRÁFICO LINHA
    new Chart(document.getElementById('graficoMensal').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($m_labels); ?>,
            datasets: [{ label: 'Empréstimos', data: <?php echo json_encode($m_valores); ?>, borderColor: azulSistema, backgroundColor: 'rgba(11, 26, 44, 0.05)', borderWidth: 2.5, fill: true, tension: 0.25 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 2. GRÁFICO DONUT
    new Chart(document.getElementById('graficoGeneros').getContext('2d'), {
        type: 'doughnut',
        data: { labels: <?php echo json_encode($g_labels); ?>, datasets: [{ data: <?php echo json_encode($g_valores); ?>, backgroundColor: paletaDeAzuis }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { boxWidth: 10 } } } }
    });

    // 3. BARRAS HORIZONTAIS
    new Chart(document.getElementById('graficoTopClientes').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($c_labels); ?>,
            datasets: [{ data: <?php echo json_encode($c_valores); ?>, backgroundColor: '#2e4f77', borderRadius: 4 }]
        },
        options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    // 4. RADAR
    new Chart(document.getElementById('graficoFuncionarios').getContext('2d'), {
        type: 'radar',
        data: {
            labels: <?php echo json_encode($f_labels); ?>,
            datasets: [{ label: 'Atendimentos', data: <?php echo json_encode($f_valores); ?>, borderColor: azulSistema, backgroundColor: 'rgba(11, 26, 44, 0.15)', borderWidth: 2 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { r: { grid: { color: '#e2e8f0' }, angleLines: { color: '#e2e8f0' } } } }
    });

    // 5. BARRAS VERTICAIS
    new Chart(document.getElementById('graficoAnos').getContext('2d'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($a_labels); ?>,
            datasets: [{ data: <?php echo json_encode($a_valores); ?>, backgroundColor: paletaDeAzuis, borderRadius: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
});
</script>
</body>
</html>