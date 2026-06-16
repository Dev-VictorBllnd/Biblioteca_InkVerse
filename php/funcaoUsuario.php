<?php
//Função para listar todos os usuários (agora funcionários)
function listaUsuario(){

    include("conexao.php");
    $sql = "SELECT * FROM funcionario ORDER BY idFuncionario;";
            
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    $lista = '';
    $ativo = '';
    $icone = '';

    if (mysqli_num_rows($result) > 0) {
        
        foreach ($result as $coluna) {

            // Como não há FlgAtivo na tabela funcionario, assumimos sempre ativo para manter o layout
            // Agora lê se está 'S' (Sim) ou 'N' (Não) no banco de dados
            if ($coluna["Ativo"] == 'S') {
                $icone = '<h5><span class="badge badge-success"><i class="fas fa-check"></i> Ativo</span></h5>';
            } else {
                $icone = '<h5><span class="badge badge-danger"><i class="fas fa-ban"></i> Inativo</span></h5>';
            } 
            
            $lista .= 
            '<tr>'
                .'<td align="center">'.$coluna["idFuncionario"].'</td>'
                .'<td align="center">'.descrTipoUsuario($coluna["idCargo"]).'</td>'
                .'<td>'.$coluna["Nome"].'</td>'
                .'<td>'.$coluna["Email"].'</td>' // Login agora é Email
                .'<td align="center">'.$icone.'</td>'
                .'<td>'
                    .'<div class="row" align="center">'
                        .'<div class="col-6">'
                            .'<a href="#modalEditUsuario'.$coluna["idFuncionario"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-edit text-info" data-toggle="tooltip" title="Alterar usuário"></i></h6>'
                            .'</a>'
                        .'</div>'
                        
                        .'<div class="col-6">'
                            .'<a href="#modalDeleteUsuario'.$coluna["idFuncionario"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-trash text-danger" data-toggle="tooltip" title="Excluir usuário"></i></h6>'
                            .'</a>'
                        .'</div>'
                    .'</div>'
                .'</td>'
            .'</tr>'
            
            .'<div class="modal fade" id="modalEditUsuario'.$coluna["idFuncionario"].'">'
                .'<div class="modal-dialog modal-lg">'
                    .'<div class="modal-content">'
                        .'<div class="modal-header bg-info">'
                            .'<h4 class="modal-title">Alterar Usuário</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">'
                                .'<span aria-hidden="true">&times;</span>'
                            .'</button>'
                        .'</div>'
                        .'<div class="modal-body">'

                            .'<form method="POST" action="php/salvarUsuario.php?funcao=A&codigo='.$coluna["idFuncionario"].'" enctype="multipart/form-data">'              
                
                                .'<div class="row">'
                                    .'<div class="col-8">'
                                        .'<div class="form-group">'
                                            .'<label for="iNome">Nome:</label>'
                                            .'<input type="text" value="'.$coluna["Nome"].'" class="form-control" id="iNome" name="nNome" maxlength="50">'
                                        .'</div>'
                                    .'</div>'
                    
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label for="iNome">Tipo de Usuário (Cargo):</label>'
                                            .'<select name="nTipoUsuario" class="form-control" required>'
                                                .'<option value="'.$coluna["idCargo"].'">'.descrTipoUsuario($coluna["idCargo"]).'</option>'
                                                .optionTipoUsuario()
                                            .'</select>'
                                        .'</div>'
                                    .'</div>'
                    
                                    .'<div class="col-8">'
                                        .'<div class="form-group">'
                                            .'<label for="iLogin">Login (E-mail):</label>'
                                            .'<input type="email" value="'.$coluna["Email"].'" class="form-control" id="iLogin" name="nLogin" maxlength="50">'
                                        .'</div>'
                                    .'</div>'
                    
                                    .'<div class="col-4">'
                                        .'<div class="form-group">'
                                            .'<label for="iSenha">Senha:</label>'
                                            .'<input type="text" value="" class="form-control" id="iSenha" name="nSenha" maxlength="6">'
                                        .'</div>'
                                    .'</div>'
                                    
                                    .'<div class="col-12">'
                                        .'<div class="form-group">'
                                            .'<label for="iFoto">Foto:</label>'
                                            .'<input type="file" class="form-control" id="iFoto" name="Foto" accept="image/*">'
                                        .'</div>'
                                    .'</div>'
                                    
                                    .'<div class="col-12">'
                                        .'<div class="form-group">'
                                            .'<input type="checkbox" id="iAtivo" name="nAtivo" '.$ativo.' disabled>'
                                            .'<label for="iAtivo">Usuário Ativo</label>'
                                        .'</div>'
                                    .'</div>'
                
                                .'</div>'
                
                                .'<div class="modal-footer">'
                                    .'<button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>'
                                    .'<button type="submit" class="btn btn-success">Salvar</button>'
                                .'</div>'
                                
                            .'</form>'
                            
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>'
            
            .'<div class="modal fade" id="modalDeleteUsuario'.$coluna["idFuncionario"].'">'
                .'<div class="modal-dialog">'
                    .'<div class="modal-content">'
                        .'<div class="modal-header bg-danger">'
                            .'<h4 class="modal-title">Excluir Usuário: '.$coluna["idFuncionario"].'</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">'
                                .'<span aria-hidden="true">&times;</span>'
                            .'</button>'
                        .'</div>'
                        .'<div class="modal-body">'

                            .'<form method="POST" action="php/salvarUsuario.php?funcao=D&codigo='.$coluna["idFuncionario"].'" enctype="multipart/form-data">'              

                                .'<div class="row">'
                                    .'<div class="col-12">'
                                        .'<h5>Deseja EXCLUIR o usuário '.$coluna["Nome"].'?</h5>'
                                    .'</div>'
                                .'</div>'
                                
                                .'<div class="modal-footer">'
                                    .'<button type="button" class="btn btn-danger" data-dismiss="modal">Não</button>'
                                    .'<button type="submit" class="btn btn-success">Sim</button>'
                                .'</div>'
                                
                            .'</form>'
                            
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>';            
        }    
    }
    
    return $lista;
}

function proxIdUsuario(){
    $id = "";
    include("conexao.php");
    $sql = "SELECT MAX(idFuncionario) AS Maior FROM funcionario;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if (mysqli_num_rows($result) > 0) {
        $array = array();
        while ($linha = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($array,$linha);
        }
        foreach ($array as $coluna) {            
            $id = $coluna["Maior"] + 1;
        }        
    } 
    return $id;
}

function tipoAcessoUsuario($id){
    $resp = "";
    include("conexao.php");
    $sql = "SELECT idCargo FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if (mysqli_num_rows($result) > 0) {
        $array = array();
        while ($linha = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($array,$linha);
        }
        foreach ($array as $coluna) {            
            if($coluna["idCargo"] == 1){
                $resp = '<option value="1">Admin</option>'
                        .'<option value="2">Empresa</option>'
                        .'<option value="3">Comum</option>';
            }else if($coluna["idCargo"] == 2){
                $resp = '<option value="2">Empresa</option>'
                        .'<option value="1">Admin</option>'
                        .'<option value="3">Comum</option>';
            }else{
                $resp = '<option value="3">Comum</option>'
                        .'<option value="1">Admin</option>'
                        .'<option value="2">Empresa</option>';
            }
        }        
    } 
    return $resp;
}

function fotoUsuario($id){
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Foto FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if (mysqli_num_rows($result) > 0) {
        $array = array();
        while ($linha = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($array,$linha);
        }
        foreach ($array as $coluna) {            
            $resp = $coluna["Foto"];
        }        
    } 
    return $resp;
}

function nomeUsuario($id){
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Nome FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if (mysqli_num_rows($result) > 0) {
        $array = array();
        while ($linha = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($array,$linha);
        }
        foreach ($array as $coluna) {            
            $resp = $coluna["Nome"];
        }        
    } 
    return $resp;
}

function loginUsuario($id){
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Email FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if (mysqli_num_rows($result) > 0) {
        $array = array();
        while ($linha = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            array_push($array,$linha);
        }
        foreach ($array as $coluna) {            
            $resp = $coluna["Email"];
        }        
    } 
    return $resp;
}

function ativoUsuario($id){
    // Removida a lógica do banco pois não existe FlgAtivo
    return 'checked';
}

function qtdUsuariosAtivos(){
    $qtd = 0;
    include("conexao.php");
    // Conta todos pois não existe FlgAtivo para filtrar
    $sql = "SELECT COUNT(*) AS Qtd FROM funcionario;";

    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if (mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $qtd = $coluna['Qtd'];
        }        
    }
    return $qtd;
}
?>