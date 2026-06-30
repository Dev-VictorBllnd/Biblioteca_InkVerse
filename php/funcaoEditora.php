<?php
// Função para listar todas as editoras e gerar os Modais de Edição e Exclusão
function listaEditoras(){

    include("conexao.php");
    $sql = "SELECT * FROM editora ORDER BY Nome;";

    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    $lista = '';

    if ($result && mysqli_num_rows($result) > 0) {

        foreach ($result as $coluna) {

            $email    = $coluna["Email"]    ? $coluna["Email"]    : '<span class="text-muted">—</span>';
            $telefone = $coluna["Telefone"] ? $coluna["Telefone"] : '<span class="text-muted">—</span>';

            $lista .=
            '<tr>'
                .'<td align="center">'.$coluna["idEditora"].'</td>'
                .'<td>'.$coluna["Nome"].'</td>'
                .'<td>'.$email.'</td>'
                .'<td>'.$telefone.'</td>'
                .'<td>'
                    .'<div class="row" align="center">'
                        .'<div class="col-6">'
                            .'<a href="#modalEditEditora'.$coluna["idEditora"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-edit text-info" data-toggle="tooltip" title="Alterar editora"></i></h6>'
                            .'</a>'
                        .'</div>'
                        .'<div class="col-6">'
                            .'<a href="#modalDeleteEditora'.$coluna["idEditora"].'" data-toggle="modal">'
                                .'<h6><i class="fas fa-trash text-danger" data-toggle="tooltip" title="Excluir editora"></i></h6>'
                            .'</a>'
                        .'</div>'
                    .'</div>'
                .'</td>'
            .'</tr>'

            // MODAL DE EDIÇÃO
            .'<div class="modal fade" id="modalEditEditora'.$coluna["idEditora"].'">'
                .'<div class="modal-dialog modal-lg">'
                    .'<div class="modal-content">'
                        .'<div class="modal-header text-white" style="background-color: #0b1a2c;">'
                            .'<h4 class="modal-title">Alterar Editora</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">'
                                .'<span aria-hidden="true">&times;</span>'
                            .'</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<form method="POST" action="php/salvarEditora.php?funcao=A&codigo='.$coluna["idEditora"].'">'
                                .'<div class="row">'
                                    .'<div class="col-md-12">'
                                        .'<div class="form-group">'
                                            .'<label>Nome:</label>'
                                            .'<input type="text" value="'.htmlspecialchars($coluna["Nome"], ENT_QUOTES).'" class="form-control" name="nNome" maxlength="100" required>'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-md-7">'
                                        .'<div class="form-group">'
                                            .'<label>E-mail:</label>'
                                            .'<input type="email" value="'.htmlspecialchars((string)$coluna["Email"], ENT_QUOTES).'" class="form-control" name="nEmail" maxlength="100">'
                                        .'</div>'
                                    .'</div>'
                                    .'<div class="col-md-5">'
                                        .'<div class="form-group">'
                                            .'<label>Telefone:</label>'
                                            .'<input type="text" value="'.htmlspecialchars((string)$coluna["Telefone"], ENT_QUOTES).'" class="form-control" name="nTelefone" maxlength="20">'
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
            .'<div class="modal fade" id="modalDeleteEditora'.$coluna["idEditora"].'">'
                .'<div class="modal-dialog">'
                    .'<div class="modal-content bg-danger">'
                        .'<div class="modal-header">'
                            .'<h4 class="modal-title">Excluir Editora</h4>'
                            .'<button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">'
                                .'<span aria-hidden="true">&times;</span>'
                            .'</button>'
                        .'</div>'
                        .'<div class="modal-body">'
                            .'<p>Deseja realmente excluir a editora <strong>'.htmlspecialchars($coluna["Nome"]).'</strong>?</p>'
                            .'<p class="mb-0"><small>Atenção: não será possível excluir se houver livros vinculados a esta editora.</small></p>'
                        .'</div>'
                        .'<div class="modal-footer justify-content-between">'
                            .'<button type="button" class="btn btn-outline-light" data-dismiss="modal">Cancelar</button>'
                            .'<a href="php/salvarEditora.php?funcao=D&codigo='.$coluna["idEditora"].'" class="btn btn-outline-light">Excluir</a>'
                        .'</div>'
                    .'</div>'
                .'</div>'
            .'</div>'; // Fim modal exclusão

        }
    } else {
        $lista = '<tr><td colspan="5" align="center">Nenhuma editora cadastrada.</td></tr>';
    }

    return $lista;
}
?>
