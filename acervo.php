<?php
include('php/conexao.php');

$sql = "SELECT
            l.idLivro,
            l.Titulo,
            l.Autor,
            g.Descricao AS Genero,
            ed.Nome AS Editora,
            l.ano,
            COUNT(e.idExemplar) AS total_copias,
            SUM(CASE WHEN (e.Emprestado IS NULL OR e.Emprestado = 'N' OR e.Emprestado = '0') THEN 1 ELSE 0 END) AS disponiveis
        FROM livro l
        LEFT JOIN exemplar e ON e.idLivro = l.idLivro AND (e.Ativo IS NULL OR e.Ativo != 'N')
        LEFT JOIN genero g ON l.idGenero = g.idGenero
        LEFT JOIN editora ed ON l.idEditora = ed.idEditora
        GROUP BY l.idLivro, l.Titulo, l.Autor, g.Descricao, ed.Nome, l.ano
        ORDER BY l.Titulo ASC;";

$result = mysqli_query($conn, $sql);
$livros = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $livros[] = $row;
    }
}
mysqli_close($conn);

$busca = strtolower(trim($_GET['busca'] ?? ''));
if ($busca !== '') {
    $livros = array_filter($livros, function($l) use ($busca) {
        return str_contains(strtolower($l['Titulo']), $busca)
            || str_contains(strtolower($l['Autor']), $busca)
            || str_contains(strtolower($l['Genero'] ?? ''), $busca);
    });
}

$totalTitulos = count($livros);
$totalCopias  = array_sum(array_column($livros, 'total_copias'));
$totalDisp    = array_sum(array_column($livros, 'disponiveis'));
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acervo - InkVerse</title>
    <?php include('partes/css.php'); ?>
    <style>
        body { background-color: #f4f6f9; }
        .acervo-navbar {
            background-color: #0b1a2c;
            padding: 12px 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .acervo-navbar .marca {
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
        }
        .acervo-navbar .marca img { height: 38px; }
        .acervo-navbar .marca h1 { font-size: 22px; font-weight: bold; margin: 0; }
        .acervo-navbar .marca span { font-size: 12px; opacity: 0.6; display: block; }
    </style>
</head>
<body>

<div class="acervo-navbar">
    <div class="marca">
        <img src="dist/img/logo.png" alt="Logo InkVerse">
        <div>
            <h1>InkVerse</h1>
            <span>Acervo da Biblioteca</span>
        </div>
    </div>
    <a href="index.php" class="btn btn-outline-light btn-sm">
        <i class="fas fa-arrow-left mr-1"></i> Voltar ao Login
    </a>
</div>

<div class="container-fluid mt-4 px-4">

    <div class="row mb-4">
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3><?php echo $totalTitulos; ?></h3>
                    <p>Titulos no Acervo</p>
                </div>
                <div class="icon"><i class="fas fa-book-open"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3><?php echo $totalCopias; ?></h3>
                    <p>Copias Totais</p>
                </div>
                <div class="icon"><i class="fas fa-copy"></i></div>
            </div>
        </div>
        <div class="col-md-4 col-sm-6 mb-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3><?php echo $totalDisp; ?></h3>
                    <p>Disponiveis para Emprestimo</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>Lista de Livros
                    </h3>
                </div>
                <div class="col-md-6">
                    <form method="GET" action="acervo.php" class="input-group input-group-sm float-right" style="max-width:360px;">
                        <input type="text" name="busca" class="form-control"
                               placeholder="Buscar por titulo, autor ou genero..."
                               value="<?php echo htmlspecialchars($_GET['busca'] ?? ''); ?>">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                            <?php if (!empty($_GET['busca'])): ?>
                                <a href="acervo.php" class="btn btn-default">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-bordered table-striped table-hover mb-0">
                <thead>
                    <tr>
                        <th>Titulo</th>
                        <th>Autor</th>
                        <th>Genero</th>
                        <th>Editora</th>
                        <th style="width:70px;" class="text-center">Ano</th>
                        <th style="width:80px;" class="text-center">Copias</th>
                        <th style="width:140px;" class="text-center">Situacao</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($livros)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-search mr-1"></i> Nenhum livro encontrado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($livros as $livro): ?>
                            <?php
                                $disp  = (int)$livro['disponiveis'];
                                $total = (int)$livro['total_copias'];
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($livro['Titulo']); ?></strong></td>
                                <td><?php echo htmlspecialchars($livro['Autor']); ?></td>
                                <td><?php echo htmlspecialchars($livro['Genero'] ?? '—'); ?></td>
                                <td><?php echo htmlspecialchars($livro['Editora'] ?? '—'); ?></td>
                                <td class="text-center"><?php echo htmlspecialchars($livro['ano']); ?></td>
                                <td class="text-center">
                                    <span class="badge badge-secondary"><?php echo $disp; ?>/<?php echo $total; ?></span>
                                </td>
                                <td class="text-center">
                                    <?php if ($disp > 0): ?>
                                        <span class="badge badge-success">Disponivel</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Indisponivel</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer text-muted text-sm">
            <?php echo $totalTitulos; ?> titulo<?php echo $totalTitulos != 1 ? 's' : ''; ?> encontrado<?php echo $totalTitulos != 1 ? 's' : ''; ?>
        </div>
    </div>

</div>

<?php include('partes/js.php'); ?>
</body>
</html>