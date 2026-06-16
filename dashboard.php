<?php
session_start();

include('php/conexao.php');
include('php/funcaoUsuario.php');
include('php/funcaoMenu.php');

$_SESSION['menu-n1'] = 'biblioteca';
$_SESSION['menu-n2'] = 'dashboard';

/* CARDS */

$qLivros = mysqli_query($conn,"
SELECT COUNT(*) total
FROM livro
");
$totalLivros = mysqli_fetch_assoc($qLivros)['total'];

$qClientes = mysqli_query($conn,"
SELECT COUNT(*) total
FROM cliente
");
$totalClientes = mysqli_fetch_assoc($qClientes)['total'];

$qEmprestimos = mysqli_query($conn,"
SELECT COUNT(*) total
FROM emprestimo_has_exemplar
WHERE Data_devolucao IS NULL
");
$totalEmprestimos = mysqli_fetch_assoc($qEmprestimos)['total'];

$qExemplares = mysqli_query($conn,"
SELECT COUNT(*) total
FROM exemplar
WHERE Emprestado='Sim'
");
$totalExemplares = mysqli_fetch_assoc($qExemplares)['total'];
?>

<!DOCTYPE html>

<html lang="pt-br">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>InkVerse - Dashboard</title>

<?php include('partes/css.php'); ?>

</head>

<body class="hold-transition sidebar-mini layout-fixed">

<div class="wrapper">

```
<!-- Navbar -->
<?php include('partes/navbar.php'); ?>
<!-- Fim Navbar -->

<!-- Sidebar -->
<?php include('partes/sidebar.php'); ?>
<!-- Fim Sidebar -->

<!-- Content Wrapper -->
<div class="content-wrapper">

    <div class="content-header">
        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">
                    <h1 class="m-0">
                        Dashboard Biblioteca
                    </h1>
                </div>

            </div>

        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <div class="container-fluid">

            <!-- CARDS -->

            <div class="row">

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-info">

                        <div class="inner">
                            <h3><?php echo $totalLivros; ?></h3>
                            <p>Livros</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-book"></i>
                        </div>

                    </div>

                </div>

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-success">

                        <div class="inner">
                            <h3><?php echo $totalClientes; ?></h3>
                            <p>Clientes</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>

                    </div>

                </div>

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-warning">

                        <div class="inner">
                            <h3><?php echo $totalEmprestimos; ?></h3>
                            <p>Empréstimos Ativos</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-exchange-alt"></i>
                        </div>

                    </div>

                </div>

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-danger">

                        <div class="inner">
                            <h3><?php echo $totalExemplares; ?></h3>
                            <p>Exemplares Emprestados</p>
                        </div>

                        <div class="icon">
                            <i class="fas fa-book-reader"></i>
                        </div>

                    </div>

                </div>

            </div>

            <!-- TABELA -->

            <div class="card-header">

    <div class="row">

        <div class="col-6">
            <h3 class="card-title">
                Últimos Empréstimos
            </h3>
        </div>

        <div class="col-6 text-right">

            <a href="php/exportar_dashboard.php"
               class="btn btn-success">

                <i class="fas fa-file-excel"></i>
                Exportar Excel

            </a>

        </div>

    </div>

</div>

                        <div class="card-body table-responsive p-0">

                            <table class="table table-hover text-nowrap">

                                <thead>

                                <tr>
                                    <th>Cliente</th>
                                    <th>Livro</th>
                                    <th>Data Empréstimo</th>
                                    <th>Status</th>
                                </tr>

                                </thead>

                                <tbody>

                                <?php

                                $sql = mysqli_query($conn,"
                                SELECT
                                    c.Nome AS Cliente,
                                    l.Titulo AS Livro,
                                    ehe.Data_emprestimo,
                                    ehe.Data_devolucao

                                FROM emprestimo_has_exemplar ehe

                                INNER JOIN emprestimo e
                                    ON e.idEmprestimo = ehe.idEmprestimo

                                INNER JOIN cliente c
                                    ON c.idCliente = e.idCliente

                                INNER JOIN exemplar ex
                                    ON ex.idExemplar = ehe.idExemplar

                                INNER JOIN livro l
                                    ON l.idLivro = ex.idLivro

                                ORDER BY ehe.Data_emprestimo DESC
                                LIMIT 10
                                ");

                                while($dados = mysqli_fetch_assoc($sql)){
                                ?>

                                <tr>

                                    <td><?php echo $dados['Cliente']; ?></td>

                                    <td><?php echo $dados['Livro']; ?></td>

                                    <td>
                                        <?php echo date('d/m/Y',strtotime($dados['Data_emprestimo'])); ?>
                                    </td>

                                    <td>

                                        <?php

                                        if(empty($dados['Data_devolucao'])){
                                            echo '<span class="badge badge-warning">Emprestado</span>';
                                        }else{
                                            echo '<span class="badge badge-success">Devolvido</span>';
                                        }

                                        ?>

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

<aside class="control-sidebar control-sidebar-dark"></aside>
```

</div>

<?php include('partes/js.php'); ?>

</body>
</html>
