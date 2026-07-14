<?php
// Função para listar todos os usuários e gerar os Modais de Edição e Exclusão
function listaUsuario($filtro = 'ativos')
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include("conexao.php");

    $idSessaoAtiva = $_SESSION['idLogin'] ?? 0;

    switch ($filtro) {
        case 'inativos':
            $where = " WHERE funcionario.Ativo = 'N' ";
            break;

        case 'todos':
            $where = "";
            break;

        default:
            $where = " WHERE funcionario.Ativo = 'S' ";
            break;
    }

    $sql = "
        SELECT *
        FROM funcionario
        $where
        ORDER BY
            CASE
                WHEN idFuncionario = $idSessaoAtiva THEN 0
                ELSE 1
            END,
            Nome
    ";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        return '<tr><td colspan="6" align="center">Erro SQL: '.mysqli_error($conn).'</td></tr>';
    }

    mysqli_close($conn);

    $lista = '';
    $icone = '';

    if ($result && mysqli_num_rows($result) > 0) {
        
        foreach ($result as $coluna) {

            if ($coluna["Ativo"] == 'S') {
                $icone = '<h5><span class="badge text-white" style="background-color: #2563eb;"><i class="fas fa-check"></i> Ativo</span></h5>';
            } else {
                $icone = '<h5><span class="badge badge-danger"><i class="fas fa-ban"></i> Inativo</span></h5>';
            } 
            
            // 3. Verifica se a linha atual é a do usuário logado
            $isUsuarioLogado = ($coluna["idFuncionario"] == $idSessaoAtiva);
            
            // 4. Aplica estilo levemente destacado e evento de duplo clique se for o próprio usuário
            $estiloLinha = $isUsuarioLogado ? 'style="background-color: #f0f4f8; cursor: pointer;" title="Dê um duplo clique para acessar seu Perfil" ondblclick="window.location.href=\'perfil.php\'"' : '';

            $lista .= 
            '<tr '.$estiloLinha.'>'
                .'<td align="center">'.$coluna["idFuncionario"].'</td>'
                .'<td align="center">'.descrCargo($coluna["idCargo"]).'</td>'
                .'<td>'
                    .'<img src="'.($coluna["Foto"] ? $coluna["Foto"] : 'dist/img/fotoperfil.png').'" '
                        .'alt="Foto" class="img-circle elevation-1 mr-2 foto-ampliar" '
                        .'data-foto="'.($coluna["Foto"] ? $coluna["Foto"] : 'dist/img/fotoperfil.png').'" '
                        .'data-nome="'.htmlspecialchars($coluna["Nome"], ENT_QUOTES).'" '
                        .'style="width: 32px; height: 32px; object-fit: cover; vertical-align: middle; cursor: pointer;">'
                    .$coluna["Nome"]
                .'</td>'
                .'<td>'.$coluna["Email"].'</td>'
                .'<td align="center">'.$icone.'</td>'
                .'<td>';

            // 5. Renderiza botões de ação condicionalmente
            if ($isUsuarioLogado) {
                // Botão de Perfil para o próprio usuário
                $lista .= '<div align="center"><a href="perfil.php" class="btn btn-sm text-white" style="background-color: #2563eb;"><i class="fas fa-user"></i> Meu Perfil</a></div>';
            } else {
                // Botões normais (Editar/Excluir) para os outros usuários
                $lista .= 
                    '<div class="row" align="center">'
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
                    .'</div>';
            }
            
            $lista .= '</td></tr>';
            
            // 6. GERA OS MODAIS APENAS PARA OS OUTROS USUÁRIOS (Economiza processamento e HTML)
            if (!$isUsuarioLogado) {
                $lista .= '
                <div class="modal fade" id="modalEditUsuario'.$coluna["idFuncionario"].'">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header text-white" style="background-color: #0b1a2c;">
                                <h4 class="modal-title">Alterar Usuário</h4>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <form method="POST" action="php/salvarFuncionario.php?funcao=A&codigo='.$coluna["idFuncionario"].'" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label>Nome:</label>
                                                <input type="text" value="'.$coluna["Nome"].'" class="form-control" name="nNome" maxlength="100" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>Tipo de Usuário:</label>
                                                <select name="nTipoUsuario" class="form-control" required>
                                                    <option value="'.$coluna["idCargo"].'">'.descrCargo($coluna["idCargo"]).'</option>
                                                    '. optionCargo() .'
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label>E-mail (Login):</label>
                                                <input type="email" value="'.$coluna["Email"].'" class="form-control" name="nLogin" maxlength="100" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>Senha: (Deixe em branco para não alterar)</label>
                                                <input type="password" class="form-control" name="nSenha" maxlength="50">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>CPF:</label>
                                                <input type="text" value="'.$coluna["Cpf"].'" class="form-control" name="nCpf" maxlength="11" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>Telefone:</label>
                                                <input type="text" value="'.$coluna["Telefone"].'" class="form-control" name="nTelefone" maxlength="15" required>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>Data de Nascimento:</label>
                                                <input type="date" value="'.$coluna["Datanasc"].'" class="form-control" name="nDatanasc" required>
                                            </div>
                                        </div>
                                        <div class="col-8">
                                            <div class="form-group">
                                                <label>Nova Foto:</label>
                                                <input type="file" class="form-control" name="Foto" accept="image/*">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="form-group">
                                                <label>Situação do Usuário:</label>
                                                <select name="nAtivo" class="form-control" required>
                                                    <option value="S" '.($coluna["Ativo"] == 'S' ? 'selected' : '').'>Ativo (Acesso Permitido)</option>
                                                    <option value="N" '.($coluna["Ativo"] == 'N' ? 'selected' : '').'>Inativo (Acesso Bloqueado)</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer mt-3">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn text-white" style="background-color: #2563eb;">Salvar Alterações</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="modalDeleteUsuario'.$coluna["idFuncionario"].'">
                    <div class="modal-dialog">
                        <div class="modal-content bg-danger">
                            <div class="modal-header">
                                <h4 class="modal-title">Excluir Usuário</h4>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p>Deseja realmente excluir o usuário <strong>'.$coluna["Nome"].'</strong>?</p>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>
                                <a href="php/salvarFuncionario.php?funcao=D&codigo='.$coluna["idFuncionario"].'" class="btn btn-outline-light">Excluir</a>
                            </div>
                        </div>
                    </div>
                </div>'; 
            }
        } 
    } else {
        // Não gera uma <tr> manual aqui: uma linha com menos <td> do que
        // colunas no cabeçalho quebra a contagem de colunas do DataTables
        // e trava o restante do JavaScript (inclusive o botão de filtro).
        // O DataTables mostra a mensagem de "vazio" sozinho via "language.emptyTable".
        $lista = '';
    }
    
    return $lista;
}


// =========================================================
// Funções de Perfil (Agora seguras contra Variáveis Vazias)
// =========================================================

function nomeUsuario($id){
    if(empty($id)) return "Visitante"; // Previne erro de sintaxe SQL se o ID sumir
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Nome FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $resp = $coluna["Nome"];
        }        
    } 
    return $resp;
}

function fotoUsuario($id){
    if(empty($id)) return "dist/img/fotoperfil.png"; 
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Foto FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $resp = $coluna["Foto"];
        }        
    }
    if($resp == "") { $resp = "dist/img/fotoperfil.png"; }
    return $resp;
}

function loginUsuario($id){
    if(empty($id)) return "";
    $resp = "";
    include("conexao.php");
    $sql = "SELECT Email FROM funcionario WHERE idFuncionario = $id;";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {            
            $resp = $coluna["Email"];
        }        
    } 
    return $resp;
}

function qtdUsuariosAtivos(){
    $qtd = 0;
    include("conexao.php");
    $sql = "SELECT * FROM funcionario WHERE Ativo = 'S';";        
    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    if ($result && mysqli_num_rows($result) > 0) {
        $qtd = mysqli_num_rows($result);
    }
    return $qtd;
}

// =========================================================
// Funções de Cargos
// =========================================================

function optionCargo(){
    include("conexao.php");
    $sql = "SELECT * FROM cargo ORDER BY Descricao;";
    $result = mysqli_query($conn, $sql);
    mysqli_close($conn);
    
    $opcoes = '';
    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {
            $opcoes .= '<option value="'.$coluna["idCargo"].'">'.$coluna["Descricao"].'</option>';
        }
    }
    return $opcoes;
}

function descrCargo($id){
    if(empty($id)) return ""; // Previne erro SQL se passar cargo em branco
    include("conexao.php");
    $sql = "SELECT Descricao FROM cargo WHERE idCargo = $id;";
    $result = mysqli_query($conn, $sql);
    mysqli_close($conn);
    
    $resp = "";
    if ($result && mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {
            $resp = $coluna["Descricao"];
        }
    }
    return $resp;
}
?>