<?php

function listaLivro(){
    include("conexao.php");
    
    // AGORA FILTRA PELO e.Ativo (Exemplar) e não pelo l.Ativo (Livro)
    $sql = "SELECT 
                e.idExemplar, 
                l.idLivro, 
                l.Titulo, 
                l.Autor, 
                l.idGenero,
                l.idEditora,
                g.Descricao as Genero, 
                ed.Nome as Editora, 
                l.Isbn, 
                l.ano, 
                e.Emprestado,
                e.Ativo 
            FROM exemplar e
            INNER JOIN livro l ON e.idLivro = l.idLivro
            LEFT JOIN genero g ON l.idGenero = g.idGenero
            LEFT JOIN editora ed ON l.idEditora = ed.idEditora
            WHERE e.Ativo IS NULL OR e.Ativo != 'N'
            ORDER BY l.Titulo ASC, e.idExemplar ASC;";
            
    $result = mysqli_query($conn, $sql);

    if(!$result){
        return array("linhas" => '<tr><td colspan="9" class="text-danger text-center"><b>Erro:</b> '.mysqli_error($conn).'</td></tr>', "modals" => '');
    }

    mysqli_close($conn);

    $lista = '';
    $modals = '';

    if (mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {
            
            $emprestado = strtoupper((string)$coluna["Emprestado"]);
            if($emprestado == 'S' || $emprestado == 'SIM' || $emprestado == '1' || $emprestado == 'EMPRESTADO') {
                $badgeStatus = '<h5><span class="badge badge-warning text-dark">Emprestado</span></h5>';
            } else {
                $badgeStatus = '<h5><span class="badge badge-success">Disponível</span></h5>';
            }

            $nomeGenero = $coluna["Genero"] ? $coluna["Genero"] : 'Sem Gênero';
            $nomeEditora = $coluna["Editora"] ? $coluna["Editora"] : 'Sem Editora';

            $lista .= 
            '<tr>'
                .'<td align="center" class="font-weight-bold text-primary">'.$coluna["idExemplar"].'</td>'
                .'<td>'.$coluna["Titulo"].'</td>'
                .'<td>'.$coluna["Autor"].'</td>'
                .'<td>'.$nomeGenero.'</td>'
                .'<td>'.$nomeEditora.'</td>'
                .'<td align="center">'.$coluna["ano"].'</td>'
                .'<td align="center">'.$coluna["Isbn"].'</td>'
                .'<td align="center">'.$badgeStatus.'</td>'
                .'<td>'
                    .'<div class="row" align="center">'
                        .'<div class="col-6">'
                            .'<a href="#modalEditExemplar'.$coluna["idExemplar"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-edit text-info" data-toggle="tooltip" title="Editar dados do Livro"></i></h6>'
                            .'</a>'
                        .'</div>'
                        .'<div class="col-6">'
                            .'<a href="#modalDeleteExemplar'.$coluna["idExemplar"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-trash text-danger" data-toggle="tooltip" title="Excluir Exemplar"></i></h6>'
                            .'</a>'
                        .'</div>'
                    .'</div>'
                .'</td>'
            .'</tr>';
            
            $modals .= 
            '<div class="modal fade" id="modalEditExemplar'.$coluna["idExemplar"].'">'
                .'<div class="modal-dialog modal-lg">'
                    .'<div class="modal-content">'
                        .'<div class="modal-header bg-info">'
                            .'<h4 class="modal-title">Editar Dados da Obra (Afeta todos os exemplares)</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal">&times;</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<form method="POST" action="php/salvarExemplar.php?funcao=A&codigo='.$coluna["idExemplar"].'">'              
                                .'<input type="hidden" name="nIdLivro" value="'.$coluna["idLivro"].'">'
                                .'<div class="form-group">'
                                    .'<label>Título do Livro:</label>'
                                    .'<input type="text" name="nTitulo" class="form-control" value="'.$coluna["Titulo"].'" required>'
                                .'</div>'
                                .'<div class="form-group">'
                                    .'<label>Autor:</label>'
                                    .'<input type="text" name="nAutor" class="form-control" value="'.$coluna["Autor"].'" required>'
                                .'</div>'
                                .'<div class="row">'
                                    .'<div class="col-6">'
                                        .'<div class="form-group">'
                                            .'<label>Gênero:</label>'
                                            .'<select name="nGenero" class="form-control" required>'
                                                .optionGenero($coluna["idGenero"])
                                            .'</select>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-6">'
                                        .'<div class="form-group">'
                                            .'<label>Editora:</label>'
                                            .'<select name="nEditora" class="form-control" required>'
                                                .optionEditora($coluna["idEditora"])
                                            .'</select>'
                                        .'</div>'
                                    .'</div>'
                                .'</div>'
                                .'<div class="row">'
                                    .'<div class="col-6">'
                                        .'<div class="form-group">'
                                            .'<label>Ano:</label>'
                                            .'<input type="number" name="nAno" class="form-control" value="'.$coluna["ano"].'" required>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-6">'
                                        .'<div class="form-group">'
                                            .'<label>ISBN:</label>'
                                            .'<input type="text" name="nIsbn" class="form-control" value="'.$coluna["Isbn"].'" required>'
                                        .'</div>'
                                    .'</div>'
                                .'</div>'
                                .'<div class="modal-footer mt-3">'
                                    .'<button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>'
                                    .'<button type="submit" class="btn btn-success">Atualizar Obra</button>'
                                .'</div>'
                            .'</form>'
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>' 
            
            .'<div class="modal fade" id="modalDeleteExemplar'.$coluna["idExemplar"].'">'
                .'<div class="modal-dialog">'
                    .'<div class="modal-content bg-danger">'
                        .'<div class="modal-header">'
                            .'<h4 class="modal-title">Excluir Exemplar</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal">&times;</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<p>Deseja realmente eliminar o exemplar código <strong>#'.$coluna["idExemplar"].'</strong> de <em>'.$coluna["Titulo"].'</em>?</p>'
                        .'</div>'
                        .'<div class="modal-footer justify-content-between">'
                            .'<button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>'
                            .'<a href="php/salvarExemplar.php?funcao=D&codigo='.$coluna["idExemplar"].'&idLivro='.$coluna["idLivro"].'" class="btn btn-outline-light">Excluir Permanente</a>'
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>';
        } 
    } else {
        $lista = '<tr><td colspan="9" align="center">Nenhum exemplar ativo encontrado no acervo.</td></tr>';
    }
    
    return array("linhas" => $lista, "modals" => $modals);
}

function optionGenero($idSelecionado = null){
    include("conexao.php");
    $sql = "SELECT * FROM genero ORDER BY Descricao ASC;";
    $res = mysqli_query($conn, $sql);
    $opts = '';
    if($res){
        while($r = mysqli_fetch_assoc($res)){
            $sel = ($idSelecionado != null && $r['idGenero'] == $idSelecionado) ? 'selected' : '';
            $opts .= '<option value="'.$r['idGenero'].'" '.$sel.'>'.$r['Descricao'].'</option>';
        }
    }
    return $opts;
}

function optionEditora($idSelecionado = null){
    include("conexao.php");
    $sql = "SELECT * FROM editora ORDER BY Nome ASC;";
    $res = mysqli_query($conn, $sql);
    $opts = '';
    if($res){
        while($r = mysqli_fetch_assoc($res)){
            $sel = ($idSelecionado != null && $r['idEditora'] == $idSelecionado) ? 'selected' : '';
            $opts .= '<option value="'.$r['idEditora'].'" '.$sel.'>'.$r['Nome'].'</option>';
        }
    }
    return $opts;
}
?>