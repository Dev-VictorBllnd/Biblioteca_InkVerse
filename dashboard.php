<?php
session_start();

include('php/conexao.php');
include('php/funcaoFuncionario.php');
include('php/funcaoMenu.php');

$_SESSION['menu-n1'] = 'biblioteca';
$_SESSION['menu-n2'] = 'dashboard';

/* CARDS INDICADORES */
$qLivros = mysqli_query($conn,"SELECT COUNT(*) total FROM livro");
$totalLivros = mysqli_fetch_assoc($qLivros)['total'];

$qClientes = mysqli_query($conn,"SELECT COUNT(*) total FROM cliente");
$totalClientes = mysqli_fetch_assoc($qClientes)['total'];

$qEmprestimos = mysqli_query($conn,"SELECT COUNT(*) total FROM emprestimo_has_exemplar WHERE Data_devolucao IS NULL");
$totalEmprestimos = mysqli_fetch_assoc($qEmprestimos)['total'];

// Conta os exemplares fisicamente fora pelos registros reais de empréstimo (não pela flag, que pode desencontrar)
// DISTINCT evita contar a mesma cópia duas vezes caso existam registros duplicados antigos
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
        .card-sistema:hover { transform: translateY(-3px); }
        .card-sistema .inner { padding: 15px 20px; }
        .card-sistema h3 { font-size: 2.2rem; font-weight: 700; margin: 0 0 5px 0; }
        .card-sistema p { font-size: 1rem; margin-bottom: 0; opacity: 0.8; }
        .card-sistema .icon { position: absolute; top: 15px; right: 20px; font-size: 40px; color: rgba(58, 137, 222, 0.25); }
        
        /* Cor de destaque avermelhada e suave para o card de atraso mantendo a identidade visual */
        .card-atraso { background-color: #721c24 !important; border-left: 5px solid #dc3545; }
        .card-atraso .icon { color: rgba(220, 53, 69, 0.25) !important; }
    </style>
</head>
<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

    <?php include('partes/navbar.php'); ?>
    <?php include('partes/sidebar.php'); ?>

    <div class="content-wrapper">
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0" style="color: #0b1a2c; font-weight: 600;">Painel de Controle Estatístico</h1>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">

                <div class="row">
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card-sistema"><div class="inner"><h3><?php echo $totalLivros; ?></h3><p>Livros</p></div><div class="icon"><i class="fas fa-book"></i></div></div>
                    </div>
                    <div class="col-lg-2 col-md-4 col-6">
                        <div class="card-sistema"><div class="inner"><h3><?php echo $totalClientes; ?></h3><p>Clientes</p></div><div class="icon"><i class="fas fa-users"></i></div></div>
                    </div>
                    <div class="col-lg-3 col-md-4 col-6">
                        <div class="card-sistema"><div class="inner"><h3><?php echo $totalEmprestimos; ?></h3><p>Empréstimos Ativos</p></div><div class="icon"><i class="fas fa-exchange-alt"></i></div></div>
                    </div>
                    <div class="col-lg-3 col-md-6 col-6">
                        <div class="card-sistema"><div class="inner"><h3><?php echo $totalExemplares; ?></h3><p>Exemplares Fora</p></div><div class="icon"><i class="fas fa-book-reader"></i></div></div>
                    </div>
                    <div class="col-lg-2 col-md-6 col-12">
                        <div class="card-sistema card-atraso"><div class="inner"><h3><?php echo $totalAtrasos; ?></h3><p>Em Atraso</p></div><div class="icon"><i class="fas fa-exclamation-triangle"></i></div></div>
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

<script>
// Opcional: Validação básica para impedir que a data final seja menor que a inicial
document.querySelector('#modalPeriodoExcel form').addEventListener('submit', function(e) {
    const inicio = document.getElementById('data_inicio').value;
    const fim = document.getElementById('data_fim').value;
    if (inicio && fim && inicio > fim) {
        e.preventDefault();
        alert('A data inicial não pode ser maior que a data final!');
    }
});
</script>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover text-nowrap table-striped">
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
                                            LIMIT 10
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

<script>
document.addEventListener("DOMContentLoaded", function () {
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