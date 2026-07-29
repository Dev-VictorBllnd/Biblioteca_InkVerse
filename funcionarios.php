<?php 
  session_start();
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
                <form method="POST" action="php/salvarFuncionario.php?funcao=I" id="formNovoFuncionario" enctype="multipart/form-data">              
                  
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
                        <input type="text" class="form-control" id="iCpf" name="nCpf" placeholder="000.000.000-00" maxlength="14" required>
                      </div>
                    </div>

                    <div class="col-6">
                      <div class="form-group">
                        <label for="iTelefone">Telefone:</label>
                        <input type="text" class="form-control" id="iTelefone" name="nTelefone" placeholder="(00) 00000-0000" maxlength="15" required>
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
    const checkModal = document.getElementById("mostrarSenhaModal");
    const senhaModal = document.getElementById("iSenhaModal");
    const confirmaModal = document.getElementById("iConfirmarSenhaModal");
    const formNovoUser = document.getElementById("formNovoFuncionario");
    const cpfModal = document.getElementById("iCpf");
    const telefoneModal = document.getElementById("iTelefone");

    // Lógica para mostrar/ocultar senha no modal
    if (checkModal) {
      checkModal.addEventListener("change", function () {
        const tipo = this.checked ? "text" : "password";
        senhaModal.type = tipo;
        confirmaModal.type = tipo;
      });
    }

    // ============ MÁSCARA DO CPF (000.000.000-00) ============
    if (cpfModal) {
      cpfModal.addEventListener("input", function () {
        let valor = this.value.replace(/\D/g, "").slice(0, 11);
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d)/, "$1.$2");
        valor = valor.replace(/(\d{3})(\d{1,2})$/, "$1-$2");
        this.value = valor;
      });
    }

    // ============ MÁSCARA DO TELEFONE ((00) 00000-0000) ============
    if (telefoneModal) {
      telefoneModal.addEventListener("input", function () {
        let valor = this.value.replace(/\D/g, "").slice(0, 11);
        valor = valor.replace(/(\d{2})(\d)/, "($1) $2");
        if (valor.length > 10) {
          valor = valor.replace(/(\d{5})(\d{1,4})$/, "$1-$2");
        } else {
          valor = valor.replace(/(\d{4})(\d{1,4})$/, "$1-$2");
        }
        this.value = valor;
      });
    }

    // Validação antes de enviar o formulário para criar usuário
    if (formNovoUser) {
      formNovoUser.addEventListener('submit', function (e) {
        
        // Regra da senha: Mínimo 1 minúscula, 1 maiúscula, 1 número e 1 caractere especial
        const regraSenha = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/;

        // 1. Valida a força da senha
        if (!regraSenha.test(senhaModal.value)) {
          e.preventDefault(); // Impede o envio
          alert("Atenção: A senha deve conter obrigatoriamente:\n- Pelo menos uma letra minúscula\n- Pelo menos uma letra maiúscula\n- Pelo menos um número\n- Pelo menos um caractere especial (!, @, #, $, etc.)");
          senhaModal.focus();
          return; // Para a execução do script aqui até que ele corrija
        }

        // 2. Valida se as senhas coincidem
        if (senhaModal.value !== confirmaModal.value) {
          e.preventDefault(); // Impede o envio
          alert("Atenção: A senha e a confirmação de senha não coincidem!");
          confirmaModal.focus();
          return;
        }

        // 3. Remove a formatação do CPF antes de enviar (mantém só os números no banco)
        if (cpfModal) {
          cpfModal.value = cpfModal.value.replace(/\D/g, "");
        }
      });
    }
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