<?php 
  include('php/autenticacao.php');
  include('php/funcoes.php');

  $filtroFunc = $_GET['filtro'] ?? 'ativos';

  if (!in_array($filtroFunc, ['ativos', 'inativos', 'todos'])) {
    $filtroFunc = 'ativos';
  }
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Funcionários</title>

  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'administrador';
    $_SESSION['menu-n2'] = 'funcionarios';
    include('partes/sidebar.php'); 
  ?>
  <div class="content-wrapper">
    <div class="content-header">
    </div>
    
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
         
            <?php if(isset($_GET['erro']) && $_GET['erro'] == 'cpf_existe'): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Atenção!</h5>
              O CPF informado já está cadastrado no sistema. Por favor, verifique os dados.
            </div>
            <?php endif; ?>

            <div class="card">
              <div class="card-header">
                <div class="row">
                  
                  <div class="col-9">
                    <h3 class="card-title">Funcionários</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novoUsuarioModal">
                    <i class="fas fa-plus"></i> Novo Funcionário
                    </button>
                  </div>

                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th>ID</th>
                      <th>Tipo de Funcionário</th>
                      <th>Nome</th>
                      <th>Login (E-mail)</th>
                      <th>Ativo</th>                
                      <th>Ações</th>
                  </tr>
                  </thead>
                  <tbody>

                  <?php echo listaUsuario($filtroFunc); ?>
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="novoUsuarioModal">
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header text-white" style="background-color: #0b1a2c;">
                <h4 class="modal-title">Novo Funcionário</h4>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span>
                </button>
              </div>
              <div class="modal-body">
                <form method="POST" action="php/salvarFuncionario.php?funcao=I" id="formNovoFuncionario" class="form-funcionario" enctype="multipart/form-data">              
                  
                  <div class="row">
                    <div class="col-8">
                      <div class="form-group">
                        <label for="iNome">Nome:</label>
                        <input type="text" class="form-control" id="iNome" name="nNome" maxlength="100" required>
                      </div>
                    </div>

                    <div class="col-4">
                      <div class="form-group">
                        <label for="iTipoUsuario">Tipo de Usuário:</label>
                        <select name="nTipoUsuario" id="iTipoUsuario" class="form-control" required>
                          <option value="">Selecione...</option>
                          <?php echo optionCargo(); ?>
                        </select>
                      </div>
                    </div>

                    <div class="col-7">
                      <div class="form-group">
                        <label for="iLogin">E-mail (Login):</label>
                        <input type="email" class="form-control" id="iLogin" name="nLogin" maxlength="100" required>
                      </div>
                    </div>

                    <div class="col-5">
                      <div class="form-group">
                        <label for="iCpf">CPF:</label>
                        <input type="text" class="form-control mask-cpf" id="iCpf" name="nCpf" placeholder="000.000.000-00" maxlength="14" required>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="form-group">
                        <label for="iTelefone">Telefone:</label>
                        <input type="text" class="form-control mask-telefone" id="iTelefone" name="nTelefone" placeholder="(00) 00000-0000" maxlength="15" required>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="form-group">
                        <label for="iDatanasc">Data de Nascimento:</label>
                        <input type="date" class="form-control" id="iDatanasc" name="nDatanasc" required>
                      </div>
                    </div>

                    <!-- BLOCO DE SENHAS -->
                    <div class="col-5">
                      <div class="form-group">
                        <label for="iSenhaModal">Senha:</label>
                        <input type="password" class="form-control" id="iSenhaModal" name="nSenha" maxlength="50" required>
                      </div>
                    </div>

                    <div class="col-5">
                      <div class="form-group">
                        <label for="iConfirmarSenhaModal">Confirmar Senha:</label>
                        <input type="password" class="form-control" id="iConfirmarSenhaModal" name="nConfirmarSenha" maxlength="50" required>
                      </div>
                    </div>

                    <div class="col-2 d-flex align-items-center">
                      <div class="form-group form-check mb-0 mt-3">
                        <input type="checkbox" class="form-check-input" id="mostrarSenhaModal">
                        <label class="form-check-label" for="mostrarSenhaModal" style="cursor: pointer; font-size: 14px;">Mostrar</label>
                      </div>
                    </div>
                    <!-- FIM DO BLOCO DE SENHAS -->
                    
                    <div class="col-8">
                      <div class="form-group">
                        <label for="iFoto">Foto:</label>
                        <input type="file" class="form-control" id="iFoto" name="Foto" accept="image/*">
                      </div>
                    </div>
                  
                    <div class="col-4">
                        <div class="form-group">
                            <label>Situação do Funcionário:</label>
                            <select name="nAtivo" class="form-control" required>
                                <option value="S" selected>Ativo (Acesso Permitido)</option>
                                <option value="N">Inativo (Acesso Bloqueado)</option>
                            </select>
                        </div>
                    </div>

                  </div>

                  <div class="modal-footer mt-3 px-0 pb-0">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Fechar</button>
                    <button type="submit" class="btn text-white" style="background-color: #2563eb;">Salvar</button>
                  </div>
                  
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
  </div>

  <aside class="control-sidebar control-sidebar-dark">
  </aside>
</div>

<!-- Modal para visualizar a foto ampliada -->
<div class="modal fade" id="modalFotoFuncionario">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary">
        <h4 class="modal-title" id="tituloFotoFuncionario">Foto</h4>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center">
        <img src="" id="imgFotoFuncionario" alt="Foto do Funcionário" class="img-fluid rounded" style="max-height: 70vh;">
      </div>
    </div>
  </div>
</div>

<?php include('partes/js.php'); ?>

<script>
  $(function () {
    $('#tabela').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "order": [],
      "info": true,
      "autoWidth": false,
      "responsive": true,
      "language": {
        "emptyTable": "Nenhum funcionário encontrado para este filtro.",
        "zeroRecords": "Nenhum funcionário encontrado."
      }
    });

    // Ao clicar na foto pequena, abre o modal com a imagem ampliada
    $(document).on('click', '.foto-ampliar', function () {
      var foto = $(this).data('foto');
      var nome = $(this).data('nome');
      $('#imgFotoFuncionario').attr('src', foto);
      $('#tituloFotoFuncionario').text(nome);
      $('#modalFotoFuncionario').modal('show');
    });
  });

  $(function () {
    // Lógica para mostrar/ocultar senha — só existe no modal de Novo Funcionário
    const checkModal = document.getElementById("mostrarSenhaModal");
    if (checkModal) {
      checkModal.addEventListener("change", function () {
        const tipo = this.checked ? "text" : "password";
        document.getElementById("iSenhaModal").type = tipo;
        document.getElementById("iConfirmarSenhaModal").type = tipo;
      });
    }

    // ============ MÁSCARA DO CPF (000.000.000-00) ============
    // Delegação de evento: funciona no modal de Novo Funcionário E em
    // TODOS os modais de Editar (um por funcionário), sem precisar de ID único.
    document.addEventListener("input", function (e) {
      if (!e.target.matches(".mask-cpf")) return;
      let valor = e.target.value.replace(/\D/g, "").slice(0, 11);
      valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
      valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
      valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
      e.target.value = valor;
    });

    // ============ MÁSCARA DO TELEFONE ((00) 00000-0000) ============
    document.addEventListener("input", function (e) {
      if (!e.target.matches(".mask-telefone")) return;
      let valor = e.target.value.replace(/\D/g, "").slice(0, 11);
      valor = valor.replace(/(\d{2})(\d)/, "($1) $2");
      if (valor.length > 10) {
        valor = valor.replace(/(\d{5})(\d{1,4})$/, "$1-$2");
      } else {
        valor = valor.replace(/(\d{4})(\d{1,4})$/, "$1-$2");
      }
      e.target.value = valor;
    });

    // ============ VALIDAÇÃO ANTES DE ENVIAR (Novo E Editar) ============
    // Delegação de evento no submit: pega tanto o form de "Novo Funcionário"
    // quanto cada form de "Editar" gerado dinamicamente pelo PHP.
    document.addEventListener("submit", function (e) {
      const form = e.target;
      if (!form.matches(".form-funcionario")) return;

      const senha = form.querySelector('[name="nSenha"]');
      const confirmaSenha = form.querySelector('[name="nConfirmarSenha"]'); // só existe no modal de Novo
      const cpf = form.querySelector(".mask-cpf");

      // Regra da senha: mínimo 1 minúscula, 1 maiúscula, 1 número e 1 caractere especial.
      // No modal de Editar a senha é opcional (vazio = não altera), então só valida
      // a força/confirmação se o usuário realmente digitou algo nesse campo.
      const regraSenha = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/;
      const precisaValidarSenha = senha && (confirmaSenha ? true : senha.value !== "");

      if (senha && senha.value !== "" && !regraSenha.test(senha.value)) {
        e.preventDefault();
        alert("Atenção: A senha deve conter obrigatoriamente:\n- Pelo menos uma letra minúscula\n- Pelo menos uma letra maiúscula\n- Pelo menos um número\n- Pelo menos um caractere especial (!, @, #, $, etc.)");
        senha.focus();
        return;
      }

      if (confirmaSenha && senha.value !== confirmaSenha.value) {
        e.preventDefault();
        alert("Atenção: A senha e a confirmação de senha não coincidem!");
        confirmaSenha.focus();
        return;
      }

      // Remove a formatação do CPF antes de enviar (mantém só os números no banco) —
      // é essa etapa que faltava no modal de Editar, e é o que garante que a
      // verificação de CPF duplicado no servidor compare "número com número".
      if (cpf) {
        cpf.value = cpf.value.replace(/\D/g, "");
      }
    });
  });

  $(function () {
    // ============ BOTÃO DE FILTRO (Ativos / Inativos / Todos) ============
    var filtroAtual = '<?php echo $filtroFunc; ?>';
    var rotulos = { 'ativos': 'Ativos', 'inativos': 'Inativos', 'todos': 'Todos' };
    
    var filtroHtml =
      '<div class="btn-group btn-group-sm mr-2" role="group" style="vertical-align: middle;">' +
        '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">' +
          '<i class="fas fa-filter"></i> ' + (rotulos[filtroAtual] || 'Ativos') +
        '</button>' +
        '<div class="dropdown-menu">' +
          '<a class="dropdown-item ' + (filtroAtual=='ativos'?'active':'') + '" href="funcionarios.php?filtro=ativos">Somente Ativos</a>' +
          '<a class="dropdown-item ' + (filtroAtual=='inativos'?'active':'') + '" href="funcionarios.php?filtro=inativos">Somente Inativos</a>' +
          '<a class="dropdown-item ' + (filtroAtual=='todos'?'active':'') + '" href="funcionarios.php?filtro=todos">Todos</a>' +
        '</div>' +
      '</div>';
      
    // Coloca o filtro dentro da área da pesquisa do DataTables
    // Certifique-se de que a sua tabela tem o ID correto no HTML, por exemplo: id="tabela"
    $('#tabela_filter').prepend(filtroHtml);
  });
</script>

</body>
</html>