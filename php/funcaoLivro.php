<?php

function listaLivro($filtro = 'ativos'){
    include("conexao.php");

    // Monta o filtro pelo estado do exemplar
    if ($filtro == 'inativos') {
        $where = "WHERE e.Ativo = 'N'";
    } elseif ($filtro == 'todos') {
        $where = "";
    } else { // ativos (padrão)
        $where = "WHERE (e.Ativo IS NULL OR e.Ativo != 'N')";
    }

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
                l.Foto,
                e.Emprestado,
                e.Ativo
            FROM exemplar e
            INNER JOIN livro l ON e.idLivro = l.idLivro
            LEFT JOIN genero g ON l.idGenero = g.idGenero
            LEFT JOIN editora ed ON l.idEditora = ed.idEditora
            $where
            ORDER BY l.Titulo ASC, e.idExemplar ASC;";
            
    $result = mysqli_query($conn, $sql);

    if(!$result){
        return array("linhas" => '<tr><td colspan="10" class="text-danger text-center"><b>Erro:</b> '.mysqli_error($conn).'</td></tr>', "modals" => '');
    }

    mysqli_close($conn);

    $lista = '';
    $modals = '';

    if (mysqli_num_rows($result) > 0) {
        foreach ($result as $coluna) {
            
            $isInativo = (strtoupper((string)$coluna["Ativo"]) === 'N');

            $emprestado = strtoupper((string)$coluna["Emprestado"]);
            if($isInativo) {
                $badgeStatus = '<h5><span class="badge badge-secondary">Inativo</span></h5>';
            } elseif($emprestado == 'S' || $emprestado == 'SIM' || $emprestado == '1' || $emprestado == 'EMPRESTADO') {
                $badgeStatus = '<h5><span class="badge badge-warning text-dark">Emprestado</span></h5>';
            } else {
                $badgeStatus = '<h5><span class="badge text-white" style="background-color: #2563eb;">Disponível</span></h5>';
            }

            $nomeGenero = $coluna["Genero"] ? $coluna["Genero"] : 'Sem Gênero';
            $nomeEditora = $coluna["Editora"] ? $coluna["Editora"] : 'Sem Editora';

            // Capa do livro (retângulo vertical). Usa a padrão se não houver imagem cadastrada
            $capa = $coluna["Foto"] ? $coluna["Foto"] : 'dist/img/capalivro.png';

            $lista .=
            '<tr>'
                .'<td align="center"><input type="checkbox" class="chk-livro" value="'.$coluna["idExemplar"].'" data-titulo="'.htmlspecialchars($coluna["Titulo"], ENT_QUOTES).'"></td>'
                .'<td align="center">'.$coluna["idExemplar"].'</td>'
                .'<td>'
                    .'<img src="'.$capa.'" alt="Capa" class="elevation-1 mr-2 capa-ampliar" '
                        .'data-capa="'.$capa.'" data-titulo="'.htmlspecialchars($coluna["Titulo"], ENT_QUOTES).'" '
                        .'style="width: 34px; height: 48px; object-fit: cover; border-radius: 3px; cursor: pointer; vertical-align: middle;">'
                    .$coluna["Titulo"]
                .'</td>'
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
                            .($isInativo
                                ? '<a href="php/salvarExemplar.php?funcao=R&codigo='.$coluna["idExemplar"].'">'
                                    .'<h6><i class="fas fa-undo text-success" data-toggle="tooltip" title="Reativar Exemplar"></i></h6>'
                                  .'</a>'
                                : '<a href="#modalDeleteExemplar'.$coluna["idExemplar"].'" data-toggle="modal">'
                                    .'<h6><i class="fas fa-trash text-danger" data-toggle="tooltip" title="Excluir Exemplar"></i></h6>'
                                  .'</a>'
                              )
                        .'</div>'
                    .'</div>'
                .'</td>'
            .'</tr>';
            
            $modals .= 
            '<div class="modal fade" id="modalEditExemplar'.$coluna["idExemplar"].'">'
                .'<div class="modal-dialog modal-lg">'
                    .'<div class="modal-content">'
                        .'<div class="modal-header text-white" style="background-color: #0b1a2c;">'
                            .'<h4 class="modal-title">Editar Dados da Obra (Afeta todos os exemplares)</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal">&times;</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<form method="POST" action="php/salvarExemplar.php?funcao=A&codigo='.$coluna["idExemplar"].'" enctype="multipart/form-data">'
                                .'<input type="hidden" name="nIdLivro" value="'.$coluna["idLivro"].'">'
                                .'<div class="row">'
                                    .'<div class="col-3 text-center">'
                                        .'<label class="d-block">Capa:</label>'
                                        .'<img src="'.$capa.'" alt="Capa atual" class="img-fluid elevation-1 mb-2" '
                                            .'style="width: 100%; max-width: 130px; height: 180px; object-fit: cover; border-radius: 4px;">'
                                        .'<input type="file" name="Capa" class="form-control-file" accept="image/*">'
                                    .'</div>'
                                    .'<div class="col-9">'
                                        .'<div class="form-group">'
                                            .'<label>Título do Livro:</label>'
                                            .'<input type="text" name="nTitulo" class="form-control" value="'.$coluna["Titulo"].'" required>'
                                        .'</div>'
                                        .'<div class="form-group">'
                                            .'<label>Autor:</label>'
                                            .'<input type="text" name="nAutor" class="form-control" value="'.$coluna["Autor"].'" required>'
                                        .'</div>'
                                    .'</div>'
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
                                    .'<button type="submit" class="btn text-white" style="background-color: #2563eb;">Atualizar Obra</button>'
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
        // Retorna vazio para o DataTables exibir sua própria mensagem sem quebrar a barra de ferramentas (busca/filtro)
        $lista = '';
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