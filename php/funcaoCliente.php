<?php
// Função para listar todos os clientes e gerar os Modais de Edição e Exclusão
function listaClientes($status = 'ativos', $multa = ''){

    include("conexao.php");
  
 // Filtro pelo status do cliente
 $where = [];

 // STATUS
 if ($status == 'ativos') {
 
     $where[] = "(c.Ativo IS NULL OR c.Ativo != 'N')";
 
 } elseif ($status == 'inativos') {
 
     $where[] = "c.Ativo = 'N'";
 
 }
 
 // MULTA
 if ($multa == 'com_multa') {
 
     $where[] = "(
         (
             SELECT COALESCE(SUM(e.multa),0)
             FROM emprestimo e
             WHERE e.idCliente = c.idCliente
         ) +
         (
             SELECT COALESCE(SUM((DATEDIFF(CURDATE(), ehe.data_prevista)+1)*1.00),0)
             FROM emprestimo e
             INNER JOIN emprestimo_has_exemplar ehe
                 ON e.idEmprestimo = ehe.idEmprestimo
             WHERE e.idCliente = c.idCliente
               AND ehe.Data_devolucao IS NULL
               AND ehe.data_prevista < CURDATE()
         )
     ) > 0";
 
 }
 elseif ($multa == 'sem_multa') {
 
     $where[] = "(
         (
             SELECT COALESCE(SUM(e.multa),0)
             FROM emprestimo e
             WHERE e.idCliente = c.idCliente
         ) +
         (
             SELECT COALESCE(SUM((DATEDIFF(CURDATE(), ehe.data_prevista)+1)*1.00),0)
             FROM emprestimo e
             INNER JOIN emprestimo_has_exemplar ehe
                 ON e.idEmprestimo = ehe.idEmprestimo
             WHERE e.idCliente = c.idCliente
               AND ehe.Data_devolucao IS NULL
               AND ehe.data_prevista < CURDATE()
         )
     ) = 0";
 
 }
 
 $where = count($where) ? "WHERE ".implode(" AND ", $where) : "";

$sql = "SELECT c.*, 
        (
            SELECT COALESCE(SUM(e.multa), 0) 
            FROM emprestimo e 
            WHERE e.idCliente = c.idCliente
        ) 
        + 
        (
            SELECT COALESCE(SUM( (DATEDIFF(CURDATE(), ehe.data_prevista) + 1) * 1.00), 0)
            FROM emprestimo e
            INNER JOIN emprestimo_has_exemplar ehe ON e.idEmprestimo = ehe.idEmprestimo
            WHERE e.idCliente = c.idCliente
              AND ehe.Data_devolucao IS NULL 
              AND ehe.data_prevista < CURDATE()
        ) AS TotalMulta
        
        FROM cliente c
        $where
        ORDER BY c.idCliente;";
            
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    $lista = '';
    $icone = '';

    // O SEGREDINHO AQUI: O "$result &&" previne o Erro Fatal!
    if ($result && mysqli_num_rows($result) > 0) {
        
        foreach ($result as $coluna) {

            // Formata o valor da multa para o padrão brasileiro (R$ 0,00)
            $valorMultaFormatado = 'R$ ' . number_format($coluna["TotalMulta"], 2, ',', '.');

            if ($coluna["Ativo"] == 'S') {
                $icone = '<h5><span class="badge text-white" style="background-color: #2563eb;"><i class="fas fa-check"></i> Ativo</span></h5>';
            } else {
                $icone = '<h5><span class="badge badge-danger"><i class="fas fa-ban"></i> Inativo</span></h5>';
            } 
                
            $lista .= 
            '<tr>'
                .'<td align="center">'.$coluna["idCliente"].'</td>'
                .'<td>'.$coluna["Nome"].'</td>'
                .'<td>'.$coluna["Email"].'</td>'
                .'<td>'.$coluna["Cpf"].'</td>'
                .'<td>'.$coluna["Telefone"].'</td>'
                .'<td align="center" class="text-danger font-weight-bold">'.$valorMultaFormatado.'</td>' /* NOVA COLUNA DA MULTA AQUI */
                .'<td align="center">'.$icone.'</td>'
                .'<td>'
                    .'<div class="row" align="center">'
                        .'<div class="col-6">'
                            .'<a href="#modalEditCliente'.$coluna["idCliente"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-edit text-info" data-toggle="tooltip" title="Alterar cliente"></i></h6>'
                            .'</a>'
                        .'</div>'
                        
                        .'<div class="col-6">'
                            .'<a href="#modalDeleteCliente'.$coluna["idCliente"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-trash text-danger" data-toggle="tooltip" title="Excluir cliente"></i></h6>'
                            .'</a>'
                        .'</div>'
                    .'</div>'
                .'</td>'
            .'</tr>'
            
            // MODAL DE EDIÇÃO
            .'<div class="modal fade" id="modalEditCliente'.$coluna["idCliente"].'">'
                .'<div class="modal-dialog modal-lg">'
                    .'<div class="modal-content">'
                        .'<div class="modal-header text-white" style="background-color: #0b1a2c;">'
                            .'<h4 class="modal-title">Alterar Cliente</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">'
                                .'<span aria-hidden="true">&times;</span>'
                            .'</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<form method="POST" action="php/salvarCliente.php?funcao=A&codigo='.$coluna["idCliente"].'" enctype="multipart/form-data">'              
                                .'<div class="row">'
                                    .'<div class="col-8">'
                                        .'<div class="form-group">'
                                            .'<label>Nome:</label>'
                                            .'<input type="text" value="'.$coluna["Nome"].'" class="form-control" name="nNome" maxlength="100" required>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>CPF:</label>'
                                            .'<input type="text" value="'.$coluna["Cpf"].'" class="form-control" name="nCpf" maxlength="11" required>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-8">'
                                        .'<div class="form-group">'
                                            .'<label>E-mail:</label>'
                                            .'<input type="email" value="'.$coluna["Email"].'" class="form-control" name="nEmail" maxlength="100" required>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>Telefone:</label>'
                                            .'<input type="text" value="'.$coluna["Telefone"].'" class="form-control" name="nTelefone" maxlength="15" required>'
                                        .'</div>'
                                    .'</div>'

                                    // ==========================================
                                    // INÍCIO DOS NOVOS CAMPOS DE ENDEREÇO
                                    // ==========================================
                                    .'<div class="col-3">'
                                        .'<div class="form-group">'
                                            .'<label>CEP:</label>'
                                            .'<input type="text" value="'.$coluna["Cep"].'" class="form-control" name="CEP" maxlength="10">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-7">'
                                        .'<div class="form-group">'
                                            .'<label>Endereço (Rua):</label>'
                                            .'<input type="text" value="'.$coluna["Endereco"].'" class="form-control" name="Endereco" maxlength="150">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-2">'
                                        .'<div class="form-group">'
                                            .'<label>Nº:</label>'
                                            .'<input type="text" value="'.$coluna["Numero"].'" class="form-control" name="Numero">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>Complemento:</label>'
                                            .'<input type="text" value="'.$coluna["Complemento"].'" class="form-control" name="Complemento" maxlength="50">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>Bairro:</label>'
                                            .'<input type="text" value="'.$coluna["Bairro"].'" class="form-control" name="Bairro" maxlength="50">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-2">'
                                        .'<div class="form-group">'
                                            .'<label>Cidade:</label>'
                                            .'<input type="text" value="'.$coluna["Cidade"].'" class="form-control" name="Cidade" maxlength="50">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-2">'
                                        .'<div class="form-group">'
                                            .'<label>UF:</label>'
                                            .'<input type="text" value="'.$coluna["UF"].'" class="form-control" name="UF" maxlength="2">'
                                        .'</div>'
                                    .'</div>'
                                    // ==========================================
                                    // FIM DOS CAMPOS DE ENDEREÇO
                                    // ==========================================

                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>Data de Nascimento:</label>'
                                            .'<input type="date" value="'.$coluna["Datanasc"].'" class="form-control" name="nDatanasc" required>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>Nova Foto (Opcional):</label>'
                                            .'<input type="file" class="form-control" name="Foto" accept="image/*">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label>Situação do Usuário:</label>'
                                            .'<select name="nAtivo" class="form-control" required>'
                                                .'<option value="S" '.($coluna["Ativo"] == 'S' ? 'selected' : '').'>Ativo (Acesso Permitido)</option>'
                                                .'<option value="N" '.($coluna["Ativo"] == 'N' ? 'selected' : '').'>Inativo (Acesso Bloqueado)</option>'
                                            .'</select>'
                                        .'</div>'
                                    .'</div>'
                                .'</div>'
                                .'<div class="modal-footer mt-3">'
                                    .'<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>'
                                    .'<button type="submit" class="btn text-white" style="background-color: #2563eb;">Salvar Alterações</button>'
                                .'</div>'
                            .'</form>'
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>' // Fim modal edição
            
            // MODAL DE EXCLUSÃO
            .'<div class="modal fade" id="modalDeleteCliente'.$coluna["idCliente"].'">'
                .'<div class="modal-dialog">'
                    .'<div class="modal-content bg-danger">'
                        .'<div class="modal-header">'
                            .'<h4 class="modal-title">Excluir Cliente</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">'
                                .'<span aria-hidden="true">&times;</span>'
                            .'</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<p>Deseja realmente excluir o cliente <strong>'.$coluna["Nome"].'</strong>?</p>'
                        .'</div>'
                        .'<div class="modal-footer justify-content-between">'
                            .'<button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>'
                            .'<a href="php/salvarCliente.php?funcao=D&codigo='.$coluna["idCliente"].'" class="btn btn-outline-light">Excluir</a>'
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>'; // Fim modal exclusão
            
        } 
    } else {
        
        $lista = '';
    }
    
    return $lista;
}

// =========================================================
// Funções de Perfil (Agora seguras contra Variáveis Vazias)
// =========================================================

function nomeCliente($id){
    if(empty($id)) return "Visitante"; // Previne erro de sintaxe SQL se o ID sumir
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Nome FROM cliente WHERE idCliente = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $resp = $coluna["Nome"];
        }        
    } 
    return $resp;
}

function fotoCliente($id){
    if(empty($id)) return "dist/img/avatar.png"; 
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Foto FROM cliente WHERE idCliente = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $resp = $coluna["Foto"];
        }        
    }
    if($resp == "") { $resp = "dist/img/avatar.png"; }
    return $resp;
}

function loginCliente($id){
    if(empty($id)) return "";
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Email FROM cliente WHERE idCliente = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $resp = $coluna["Email"];
        }        
    } 
    return $resp;
}

function qtdClientesAtivos(){
    $qtd = 0;
    include("conexao.php");
    $sql = "SELECT * FROM cliente WHERE Ativo = 'S';";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        $qtd = mysqli_num_rows($result);
    }
    return $qtd;
}
