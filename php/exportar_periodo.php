<?php
session_start();

include('conexao.php');

// Captura as datas enviadas pelo modal. Se vazias, mata a execução para evitar download incorreto.
if (empty($_GET['data_inicio']) || empty($_GET['data_fim'])) {
    die("Período inválido fornecido.");
}

$data_inicio = $_GET['data_inicio'] . " 00:00:00";
$data_fim    = $_GET['data_fim'] . " 23:59:59";

// Configuração dos headers para forçar o download em formato Excel (.xls)
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=Emprestimos_Periodo_" . $_GET['data_inicio'] . "_a_" . $_GET['data_fim'] . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Imprime o marcador BOM do UTF-8 para garantir a acentuação correta no Excel
echo "\xEF\xBB\xBF";

echo "
<table border='1'>
<tr>
    <th colspan='7' style='background-color: #0b1a2c; color: #ffffff; font-weight: bold; text-align: center; height: 30px;'>
        Histórico de Movimentações - Período: " . date('d/m/Y', strtotime($_GET['data_inicio'])) . " até " . date('d/m/Y', strtotime($_GET['data_fim'])) . "
    </th>
</tr>
<tr>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Cód. Controle</th>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Cliente</th>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Livro</th>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Cód. Cópia (Exemplar)</th>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Data da Operação</th>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Prazo Limite</th>
    <th style='background-color: #f2f2f2; font-weight: bold;'>Status / Retorno</th>
</tr>
";

// Consulta filtrando pela coluna Data_emprestimo no range estipulado
$stmt = mysqli_prepare($conn, "
    SELECT 
        ehe.idEmprestimo_controler AS id_controle,
        c.Nome AS Cliente,
        l.Titulo AS Livro,
        ehe.idExemplar AS Copia,
        ehe.Data_emprestimo,
        ehe.data_prevista,
        ehe.Data_devolucao
    FROM emprestimo_has_exemplar ehe
    INNER JOIN emprestimo e ON e.idEmprestimo = ehe.idEmprestimo
    INNER JOIN cliente c ON c.idCliente = e.idCliente
    INNER JOIN exemplar ex ON ex.idExemplar = ehe.idExemplar
    INNER JOIN livro l ON l.idLivro = ex.idLivro
    WHERE ehe.Data_emprestimo BETWEEN ? AND ?
    ORDER BY ehe.Data_emprestimo DESC
");

mysqli_stmt_bind_param($stmt, "ss", $data_inicio, $data_fim);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    while ($dados = mysqli_fetch_assoc($result)) {
        
        // Formatações de status e datas coerentes com as movimentações da tabela do dashboard
        if (empty($dados['Data_devolucao'])) {
            $status = 'Saída (Emprestado)';
        } else {
            $status = 'Retorno (Devolvido em ' . date('d/m/Y H:i', strtotime($dados['Data_devolucao'])) . ')';
        }

        $data_operacao = (!empty($dados['Data_emprestimo'])) ? date('d/m/Y H:i', strtotime($dados['Data_emprestimo'])) : '-';
        $prazo_limite  = (!empty($dados['data_prevista'])) ? date('d/m/Y', strtotime($dados['data_prevista'])) : '-';

        echo "
        <tr>
            <td style='text-align: center;'>#{$dados['id_controle']}</td>
            <td>{$dados['Cliente']}</td>
            <td>{$dados['Livro']}</td>
            <td style='text-align: center;'>{$dados['Copia']}</td>
            <td style='text-align: center;'>{$data_operacao}</td>
            <td style='text-align: center;'>{$prazo_limite}</td>
            <td>{$status}</td>
        </tr>
        ";
    }
} else {
    echo "
    <tr>
        <td colspan='7' style='text-align: center; color: #721c24;'>Nenhum registro encontrado para este período.</td>
    </tr>
    ";
}

echo "</table>";
?>