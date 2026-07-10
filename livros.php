<?php
  session_start();
  include('php/funcoes.php');
  include('php/funcaoLivro.php');

  // Filtro de exibição: ativos (padrão), inativos ou todos
  $filtroLivros = $_GET['filtro'] ?? 'ativos';
  if(!in_array($filtroLivros, ['ativos','inativos','todos'])){ $filtroLivros = 'ativos'; }

  $dadosLivro = listaLivro($filtroLivros);
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Livros</title>
  <?php include('partes/css.php'); ?>
  <link rel="stylesheet" href="plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php
    $_SESSION['menu-n1'] = 'biblioteca';
    $_SESSION['menu-n2'] = 'livros';
    include('partes/sidebar.php');
  ?>

  <div class="content-wrapper">
    <div class="content-header"></div>
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">

            <?php if(isset($_GET['sucesso_cad'])): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> Livro cadastrado com sucesso!
              </div>
            <?php endif; ?>

            <?php if(isset($_GET['del_ok']) && (int)$_GET['del_ok'] > 0): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> <?php echo (int)$_GET['del_ok']; ?> exemplar(es) excluído(s) com sucesso.
              </div>
            <?php endif; ?>

            <?php if(isset($_GET['inativados']) && (int)$_GET['inativados'] > 0): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> <?php echo (int)$_GET['inativados']; ?> exemplar(es) inativado(s) com sucesso.
              </div>
            <?php endif; ?>

            <?php if(isset($_GET['reativado'])): ?>
              <div class="alert alert-success alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle"></i> Exemplar reativado com sucesso.
              </div>
            <?php endif; ?>

            <?php
              // Exemplares que não puderam ser excluídos (têm histórico de empréstimo)
              $bloqueadosLista = array();
              if(isset($_GET['bloqueados']) && $_GET['bloqueados'] != ''){
                $idsBloq = array_filter(array_map('intval', explode(',', $_GET['bloqueados'])));
                if(count($idsBloq) > 0){
                  $inSql = implode(',', $idsBloq);
                  include('php/conexao.php');
                  $qb = mysqli_query($conn, "SELECT e.idExemplar, l.Titulo FROM exemplar e INNER JOIN livro l ON e.idLivro = l.idLivro WHERE e.idExemplar IN ($inSql);");
                  if($qb){
                    while($rb = mysqli_fetch_assoc($qb)){ $bloqueadosLista[] = $rb; }
                  }
                  mysqli_close($conn);
                }
              }
            ?>

            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-md-5">
                    <h3 class="card-title">Gestão de Exemplares do Acervo</h3>
                  </div>
                  <div class="col-md-7" align="right">
                    <button type="button" id="btnExcluirSelecionados" class="btn btn-danger mr-1" disabled>
                      <i class="fas fa-trash"></i> Excluir Selecionados (<span id="contadorSel">0</span>)
                    </button>
                    <button type="button" class="btn btn-outline-primary mr-1" data-toggle="modal" data-target="#addExemplaresModal">
                      <i class="fas fa-plus"></i> Adicionar Exemplares
                    </button>
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novoLivroModal">
                      <i class="fas fa-plus"></i> Novo Livro
                    </button>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                    <tr>
                      <th style="width: 3%;" class="text-center"><input type="checkbox" id="chkTodos" title="Selecionar todos"></th>
                      <th style="width: 8%;">ID Cópia</th>
                      <th>Título</th>
                      <th>Autor</th>
                      <th>Gênero</th>
                      <th>Editora</th>
                      <th style="width: 8%;">Ano</th>
                      <th>ISBN</th>
                      <th style="width: 12%;">Situação</th>
                      <th style="width: 10%;">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php echo $dadosLivro['linhas']; ?>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>
      </div>

      <!-- Modal Cadastro de Livro -->
      <div class="modal fade" id="novoLivroModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title">Novo Livro</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarLivro.php?funcao=I" enctype="multipart/form-data" id="formNovoLivro">

                <div class="row">
                  <div class="col-3 text-center">
                    <label class="d-block">Capa do Livro:</label>
                    <img src="dist/img/capalivro.png" id="previewCapa" alt="Capa" class="img-fluid elevation-1 mb-2"
                         style="width: 100%; max-width: 130px; height: 180px; object-fit: cover; border-radius: 4px;">
                    <input type="file" name="Capa" id="inputCapa" class="form-control-file" accept="image/*">
                  </div>
                  <div class="col-9">
                    <div class="form-group">
                      <label for="iTitulo">Título:</label>
                      <input type="text" class="form-control" id="iTitulo" name="nTitulo" maxlength="200" placeholder="Título completo do livro" required>
                    </div>

                    <div class="form-group">
                      <label for="iAutor">Autor:</label>
                      <input type="text" class="form-control" id="iAutor" name="nAutor" maxlength="150" placeholder="Nome do autor" required>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-6">
                    <div class="form-group">
                      <label for="iGenero">Gênero:</label>
                      <select name="nGenero" id="iGenero" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php echo optionGenero(); ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-6">
                    <div class="form-group">
                      <label for="iEditora">Editora:</label>
                      <select name="nEditora" id="iEditora" class="form-control" required>
                        <option value="">Selecione...</option>
                        <?php echo optionEditora(); ?>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-4">
                    <div class="form-group">
                      <label for="iAno">Ano de Publicação:</label>
                      <input type="number" class="form-control" id="iAno" name="nAno" min="1000" max="<?php echo date('Y'); ?>" placeholder="<?php echo date('Y'); ?>" required>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="form-group">
                      <label for="iIsbn">ISBN:</label>
                      <input type="text" class="form-control" id="iIsbn" name="nIsbn" maxlength="20" placeholder="Ex: 978-85-333-0227-3" required>
                    </div>
                  </div>
                  <div class="col-4">
                    <div class="form-group">
                      <label for="iQtd">Qtd. de Exemplares:</label>
                      <input type="number" class="form-control" id="iQtd" name="nQtd" min="1" max="99" value="1" required>
                    </div>
                  </div>
                </div>

                <div class="modal-footer mt-3">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
                  <button type="submit" class="btn text-white" style="background-color: #2563eb;">Salvar</button>
                </div>

              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Adicionar Exemplares a um livro já cadastrado -->
      <div class="modal fade" id="addExemplaresModal">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title">Adicionar Exemplares</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
              <form method="POST" action="php/salvarExemplar.php?funcao=E" id="formAddExemplares">
                <div class="form-group">
                  <label for="iLivroExemplar">Livro:</label>
                  <select name="nIdLivro" id="iLivroExemplar" class="form-control" required>
                    <option value="">Selecione o livro...</option>
                    <?php
                      include('php/conexao.php');
                      $qLiv = mysqli_query($conn, "SELECT idLivro, Titulo, Autor FROM livro ORDER BY Titulo ASC");
                      if($qLiv && mysqli_num_rows($qLiv) > 0){
                        while($lv = mysqli_fetch_assoc($qLiv)){
                          echo '<option value="'.$lv['idLivro'].'">'.htmlspecialchars($lv['Titulo']).' — '.htmlspecialchars($lv['Autor']).'</option>';
                        }
                      }
                      mysqli_close($conn);
                    ?>
                  </select>
                  <small class="text-muted">Os dados da obra são os do livro escolhido.</small>
                </div>
                <div class="form-group">
                  <label for="iQtdExemplares">Quantidade de exemplares a adicionar:</label>
                  <input type="number" class="form-control" id="iQtdExemplares" name="nQtd" min="1" max="99" value="1" required>
                </div>
                <div class="modal-footer mt-3">
                  <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
                  <button type="submit" class="btn text-white" style="background-color: #2563eb;">Adicionar</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Formulário oculto que efetiva a exclusão em massa -->
      <form method="POST" action="php/salvarExemplar.php?funcao=DM" id="formExcluirMassa">
        <div id="inputsExcluir"></div>
      </form>

      <!-- Modal Exclusão em massa - ETAPA 1 -->
      <div class="modal fade" id="modalExcluir1" data-backdrop="static">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger">
              <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirmar Exclusão (1 de 2)</h4>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
              <p>Você está prestes a excluir <strong><span id="qtdExcluir1">0</span></strong> exemplar(es). Confira a lista:</p>
              <ul id="listaExcluir1" class="pl-3" style="max-height: 250px; overflow-y: auto;"></ul>
              <p class="text-danger mb-0"><strong>Esta ação é permanente. Tem certeza?</strong></p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn btn-danger" id="btnAvancarExcluir">Sim, continuar</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de confirmação do CADASTRO de livro -->
      <div class="modal fade" id="modalConfirmCadastro">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title"><i class="fas fa-question-circle"></i> Confirmar Cadastro</h4>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
              <p class="mb-1">Cadastrar o livro:</p>
              <h4 class="font-weight-bold" id="confCadTitulo">—</h4>
              <p class="mb-0">com <strong class="text-primary" id="confCadQtd">0</strong> exemplar(es)?</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn text-white" style="background-color: #2563eb;" id="btnConfirmarCadastro">
                <i class="fas fa-check"></i> Confirmar Cadastro
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal de confirmação ao ADICIONAR EXEMPLARES -->
      <div class="modal fade" id="modalConfirmAddEx">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title"><i class="fas fa-question-circle"></i> Confirmar</h4>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
              <p class="mb-1">Adicionar <strong class="text-primary" id="confAddQtd">0</strong> exemplar(es) de:</p>
              <h4 class="font-weight-bold" id="confAddLivro">—</h4>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
              <button type="button" class="btn text-white" style="background-color: #2563eb;" id="btnConfirmarAddEx">
                <i class="fas fa-check"></i> Confirmar
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal AMARELO: inativar exemplares que não puderam ser excluídos -->
      <form method="POST" action="php/salvarExemplar.php?funcao=IM" id="formInativarMassa">
        <div id="inputsInativar">
          <?php foreach($bloqueadosLista as $b){ echo '<input type="hidden" name="ids[]" value="'.$b['idExemplar'].'">'; } ?>
        </div>
      </form>

      <div class="modal fade" id="modalInativarMassa" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content bg-warning">
            <div class="modal-header">
              <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Não foi possível excluir!</h4>
            </div>
            <div class="modal-body text-dark">
              <p>Os exemplares abaixo <strong>não puderam ser excluídos</strong> porque possuem histórico de empréstimos vinculado a eles:</p>
              <ul class="pl-3" style="max-height: 220px; overflow-y: auto;">
                <?php foreach($bloqueadosLista as $b){ echo '<li><strong>Cód. '.$b['idExemplar'].'</strong> — '.htmlspecialchars($b['Titulo']).'</li>'; } ?>
              </ul>
              <p class="mb-0"><strong>Deseja inativar esses exemplares?</strong> Ao inativar, eles deixarão de aparecer nas listagens do sistema.</p>
            </div>
            <div class="modal-footer justify-content-between">
              <a href="livros.php" class="btn btn-outline-dark">Cancelar</a>
              <button type="button" class="btn btn-danger font-weight-bold" id="btnInativarMassa">
                <i class="fas fa-eye-slash"></i> Sim, Inativar <?php echo count($bloqueadosLista); ?> exemplar(es)
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Modal Exclusão em massa - ETAPA 2 -->
      <div class="modal fade" id="modalExcluir2" data-backdrop="static">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger">
              <h4 class="modal-title"><i class="fas fa-trash"></i> Confirmação Final (2 de 2)</h4>
              <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body text-center">
              <p class="mb-1">Confirma a exclusão definitiva de:</p>
              <h3 class="text-danger font-weight-bold"><span id="qtdExcluir2">0</span> exemplar(es)</h3>
              <p class="text-muted mb-0">Não será possível desfazer.</p>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Não, cancelar</button>
              <button type="button" class="btn btn-danger font-weight-bold" id="btnConfirmarExcluir">
                <i class="fas fa-trash"></i> Excluir definitivamente
              </button>
            </div>
          </div>
        </div>
      </div>

    </section>
  </div>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<?php echo $dadosLivro['modals']; ?>

<div class="modal fade" id="modalFalhaInativar" data-backdrop="static" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content bg-warning">
      <div class="modal-header">
        <h4 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Não foi possível excluir!</h4>
      </div>
      <div class="modal-body text-dark">
        <p>Este exemplar não pode ser excluído permanentemente porque possui históricos de empréstimos vinculados a ele.</p>
        <p><strong>Deseja inativar este livro?</strong> Ao inativar, ele e os seus exemplares deixarão de aparecer nas listagens do sistema.</p>
      </div>
      <div class="modal-footer justify-content-between">
        <a href="livros.php" class="btn btn-outline-dark">Cancelar</a>
        <a href="" id="linkInativarConfirmado" class="btn btn-danger font-weight-bold">Sim, Inativar Livro</a>
      </div>
    </div>
  </div>
</div>

<!-- Modal para visualizar a capa ampliada -->
<div class="modal fade" id="modalCapaLivro">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header text-white" style="background-color: #0b1a2c;">
        <h4 class="modal-title" id="tituloCapaLivro">Capa</h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="imgCapaLivro" alt="Capa do Livro" class="img-fluid rounded" style="max-height: 75vh;">
      </div>
    </div>
  </div>
</div>

<?php include('partes/js.php'); ?>
<script src="plugins/select2/js/select2.full.min.js"></script>

<script>
  $(function () {
    // Coluna 0 (checkbox) e coluna 9 (ações) não ordenáveis
    $('#tabela').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": false,
      "order": [[ 1, "asc" ]],
      "columnDefs": [
        { "orderable": false, "searchable": false, "targets": [0, 9] }
      ],
      "language": {
        "emptyTable": "Nenhum exemplar encontrado para este filtro.",
        "zeroRecords": "Nenhum exemplar corresponde à pesquisa."
      }
    });

    // ============ BOTÃO DE FILTRO (Ativos / Inativos / Todos) ao lado da pesquisa ============
    var filtroAtual = '<?php echo $filtroLivros; ?>';
    var rotulos = { 'ativos': 'Ativos', 'inativos': 'Inativos', 'todos': 'Todos' };
    var filtroHtml =
      '<div class="btn-group btn-group-sm mr-2" role="group" style="vertical-align: middle;">' +
        '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">' +
          '<i class="fas fa-filter"></i> ' + (rotulos[filtroAtual] || 'Ativos') +
        '</button>' +
        '<div class="dropdown-menu">' +
          '<a class="dropdown-item ' + (filtroAtual=='ativos'?'active':'') + '" href="livros.php?filtro=ativos">Somente Ativos</a>' +
          '<a class="dropdown-item ' + (filtroAtual=='inativos'?'active':'') + '" href="livros.php?filtro=inativos">Somente Inativos</a>' +
          '<a class="dropdown-item ' + (filtroAtual=='todos'?'active':'') + '" href="livros.php?filtro=todos">Todos</a>' +
        '</div>' +
      '</div>';
    // Coloca o filtro dentro da área da pesquisa (que já fica à direita), à esquerda do campo
    $('#tabela_filter').prepend(filtroHtml);

    // Busca no seletor de livro (Adicionar Exemplares)
    $('#iLivroExemplar').select2({
      theme: 'bootstrap4',
      placeholder: 'Digite ou selecione o livro...',
      language: { noResults: function () { return 'Nenhum livro encontrado'; } },
      dropdownParent: $('#addExemplaresModal')
    });

    // Pré-visualização da capa ao escolher o arquivo no cadastro
    $('#inputCapa').on('change', function () {
      if (this.files && this.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) { $('#previewCapa').attr('src', e.target.result); };
        reader.readAsDataURL(this.files[0]);
      }
    });

    // Clicar na capa pequena abre o modal com a imagem ampliada
    $(document).on('click', '.capa-ampliar', function () {
      $('#imgCapaLivro').attr('src', $(this).data('capa'));
      $('#tituloCapaLivro').text($(this).data('titulo'));
      $('#modalCapaLivro').modal('show');
    });

    // ================= SELEÇÃO EM MASSA =================
    function atualizaSelecao() {
      var n = $('.chk-livro:checked').length;
      $('#contadorSel').text(n);
      $('#btnExcluirSelecionados').prop('disabled', n === 0);
    }

    // Marcar/desmarcar todos os visíveis
    $('#chkTodos').on('change', function () {
      $('.chk-livro').prop('checked', this.checked);
      atualizaSelecao();
    });

    $(document).on('change', '.chk-livro', function () {
      if (!this.checked) { $('#chkTodos').prop('checked', false); }
      atualizaSelecao();
    });

    // Abre a ETAPA 1 montando a lista dos selecionados
    $('#btnExcluirSelecionados').on('click', function () {
      var selecionados = $('.chk-livro:checked');
      if (selecionados.length === 0) { return; }

      var itens = '';
      selecionados.each(function () {
        itens += '<li><strong>Cód. ' + $(this).val() + '</strong> — ' + $(this).data('titulo') + '</li>';
      });
      $('#listaExcluir1').html(itens);
      $('#qtdExcluir1').text(selecionados.length);
      $('#modalExcluir1').modal('show');
    });

    // ETAPA 1 -> ETAPA 2
    $('#btnAvancarExcluir').on('click', function () {
      $('#modalExcluir1').modal('hide');
      $('#qtdExcluir2').text($('.chk-livro:checked').length);
      $('#modalExcluir2').modal('show');
    });

    // ETAPA 2 -> envia o formulário oculto
    $('#btnConfirmarExcluir').on('click', function () {
      var inputs = '';
      $('.chk-livro:checked').each(function () {
        inputs += '<input type="hidden" name="ids[]" value="' + $(this).val() + '">';
      });
      $('#inputsExcluir').html(inputs);
      $('#formExcluirMassa').submit();
    });

    // ============ CONFIRMAÇÃO NO CADASTRO (modal centralizado) ============
    var cadastroConfirmado = false;
    $('#formNovoLivro').on('submit', function (e) {
      if (cadastroConfirmado) { return; } // já confirmou, deixa enviar
      e.preventDefault();
      $('#confCadTitulo').text($('#iTitulo').val() || '(sem título)');
      $('#confCadQtd').text(parseInt($('#iQtd').val(), 10) || 0);
      $('#modalConfirmCadastro').modal('show');
    });
    $('#btnConfirmarCadastro').on('click', function () {
      cadastroConfirmado = true;
      $('#modalConfirmCadastro').modal('hide');
      $('#formNovoLivro').submit();
    });

    // ============ CONFIRMAÇÃO AO ADICIONAR EXEMPLARES ============
    var addExConfirmado = false;
    $('#formAddExemplares').on('submit', function (e) {
      if (addExConfirmado) { return; }
      e.preventDefault();
      if (!$('#iLivroExemplar').val()) { alert('Selecione o livro.'); return; }
      $('#confAddQtd').text(parseInt($('#iQtdExemplares').val(), 10) || 0);
      $('#confAddLivro').text($('#iLivroExemplar option:selected').text());
      $('#modalConfirmAddEx').modal('show');
    });
    $('#btnConfirmarAddEx').on('click', function () {
      addExConfirmado = true;
      $('#modalConfirmAddEx').modal('hide');
      $('#formAddExemplares').submit();
    });

    // ============ INATIVAR EM MASSA (modal amarelo) ============
    $('#btnInativarMassa').on('click', function () {
      $('#formInativarMassa').submit();
    });

    // Mensagens de resultado da exclusão em massa
    const urlParams = new URLSearchParams(window.location.search);

    // Se houver exemplares bloqueados, abre o modal amarelo perguntando se inativa
    <?php if(count($bloqueadosLista) > 0): ?>
      $('#modalInativarMassa').modal('show');
    <?php endif; ?>

    if (urlParams.has('erro_excluir') && urlParams.has('idLivro')) {
        const idLivro = urlParams.get('idLivro');
        $('#linkInativarConfirmado').attr('href', 'php/salvarExemplar.php?funcao=I&idLivro=' + idLivro);
        $('#modalFalhaInativar').modal('show');
    }
  });
</script>
</body>
</html>