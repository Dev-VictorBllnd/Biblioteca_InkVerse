<?php

session_start();

include('php/conexao.php');

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Dashboard_Biblioteca.xls");
header("Pragma: no-cache");
header("Expires: 0");

/* RESUMO */

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

echo "
<table border='1'>

<tr>
    <th colspan='2'>
        Dashboard Biblioteca InkVerse
    </th>
</tr>

<tr>
    <td>Total de Livros</td>
    <td>$totalLivros</td>
</tr>

<tr>
    <td>Total de Clientes</td>
    <td>$totalClientes</td>
</tr>

<tr>
    <td>Empréstimos Ativos</td>
    <td>$totalEmprestimos</td>
</tr>

<tr>
    <td>Exemplares Emprestados</td>
    <td>$totalExemplares</td>
</tr>

</table>

<br><br>

<table border='1'>

<tr>
    <th colspan='4'>
        Últimos Empréstimos
    </th>
</tr>

<tr>
    <th>Cliente</th>
    <th>Livro</th>
    <th>Data Empréstimo</th>
    <th>Status</th>
</tr>
";

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
LIMIT 100
");

while($dados = mysqli_fetch_assoc($sql)){

    $status = empty($dados['Data_devolucao'])
        ? 'Emprestado'
        : 'Devolvido';

    echo "
    <tr>

        <td>{$dados['Cliente']}</td>

        <td>{$dados['Livro']}</td>

        <td>".date('d/m/Y',strtotime($dados['Data_emprestimo']))."</td>

        <td>$status</td>

    </tr>
    ";
}

echo "</table>";