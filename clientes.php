<?php 
 include('php/autenticacao.php');
 include('php/funcoes.php');

 // Filtro de exibição: ativos (padrão), inativos ou todos
 $status = $_GET['status'] ?? 'ativos';
 $multa = $_GET['multa'] ?? '';

if (!in_array($status, ['ativos', 'inativos', 'todos'])) {
    $status = 'ativos';
}

if (!in_array($multa, ['todos', 'com_multa', 'sem_multa'])) {
    $multa = 'todos';
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Clientes</title>

  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'administrador';
    $_SESSION['menu-n2'] = 'clientes';
    include('partes/sidebar.php'); 
  ?>
  <div class="content-wrapper">
    <div class="content-header">
    </div>
    
    <?php if(isset($_GET['erro']) && $_GET['erro'] == 'cpf_existe'): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-ban"></i> Atenção!</h5>
              O CPF informado já está cadastrado no sistema. Por favor, verifique os dados.
            </div>
            <?php endif; ?>

            <?php if(isset($_GET['erro']) && $_GET['erro'] == 'multa'): ?>
            <div class="alert alert-danger alert-dismissible">
              <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
              <h5><i class="icon fas fa-exclamation-triangle"></i> Multa em aberto!</h5>
              Este cliente possui multa(s) em aberto e não pode ser excluído/inativado. Quite a multa antes de continuar.
            </div>
            <?php endif; ?>
            
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">

                  <div class="col-9">
                    <h3 class="card-title">Clientes</h3>
                  </div>
                  
                  <div class="col-3" align="right">
                    <button type="button" class="btn text-white" style="background-color: #2563eb;" data-toggle="modal" data-target="#novoClienteModal">
                    <i class="fas fa-plus"></i>  Novo Cliente
                    </button>
                  </div>

                </div>
              </div>

              <div class="card-body">
                <table id="tabela" class="table table-bordered table-hover">
                  <thead>
                  <tr>
                      <th>ID</th>
                      <th>Nome</th>
                      <th>Email</th>
                      <th>CPF</th>
                      <th>Telefone</th>
                      <th>Multas (R$)</th>
                      <th>Ativo</th>                
                      <th>Ações</th>     
                  </tr>
                  </thead>
                  <tbody>

                  <?php echo listaClientes($status, $multa); ?>
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="novoClienteModal">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header text-white" style="background-color: #0b1a2c;">
              <h4 class="modal-title">Novo Cliente</h4>
              <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                X
              </button>
            </div>
            <div class="modal-body">
              <form id="formNovoCliente" method="POST" action="php/salvarCliente.php?funcao=I" enctype="multipart/form-data">              
                
              <h5 class="mb-3 text-info border-bottom pb-2">Dados Pessoais</h5>

                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="iNome">Nome Completo:</label>
                      <input type="text" class="form-control" id="iNome" name="nNome" maxlength="100" required placeholder="Digite o seu nome">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="iCpf">CPF:</label>
                      <input type="text" class="form-control" id="iCpf" name="nCpf" placeholder="000.000.000-00" maxlength="14" required>
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label for="iLogin">E-mail:</label>
                      <input type="email" class="form-control" id="iLogin" name="nEmail" maxlength="100" required placeholder="exemplo@email.com">
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="iDatanasc">Data de Nascimento:</label>
                      <input type="date" class="form-control" id="iDatanasc" name="nDatanasc" required>
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label for="iTelefone">Telefone:</label>
                      <input type="text" class="form-control" id="iTelefone" name="nTelefone" placeholder="(00) 00000-0000" maxlength="15" required>
                    </div>
                  </div>
                </div> 

                <h5 class="mt-3 mb-3 text-info border-bottom pb-2">Endereço</h5>
                
                <div class="row">
                  <div class="col-md-3">
                    <div class="form-group">
                      <label>CEP</label>
                      <input required name="CEP" type="text" class="form-control cep" placeholder="00000-000">
                    </div>
                  </div>

                  <div class="col-md-9">
                    <div class="form-group">
                      <label>Endereço</label>
                      <input required name="Endereco" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-3">
                    <div class="form-group">
                      <label>Número</label>
                      <input required name="Numero" type="text" maxlength="8" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-9">
                    <div class="form-group">
                      <label>Complemento</label>
                      <input name="Complemento" type="text" maxlength="50" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label>Bairro</label>
                      <input required name="Bairro" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-5">
                    <div class="form-group">
                      <label>Cidade</label>
                      <input required name="Cidade" type="text" class="form-control">
                    </div>
                  </div>

                  <div class="col-md-2">
                    <div class="form-group">
                      <label>UF</label>
                      <input required name="UF" type="text" class="form-control">
                    </div>
                  </div>
                </div> 
                
                <h5 class="mt-3 mb-3 text-info border-bottom pb-2">Outros</h5>
                  
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="iFoto">Foto:</label>
                      <input type="file" class="form-control" id="iFoto" name="Foto" accept="image/*">
                    </div>
                  </div>
                
                  <div class="col-md-4">
                      <div class="form-group">
                          <label>Situação do Cliente:</label>
                          <select name="nAtivo" class="form-control" required>
                              <option value="S" selected>Ativo (Acesso Permitido)</option>
                              <option value="N">Inativo (Acesso Bloqueado)</option>
                          </select>
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
      </section>
  </div>

  <aside class="control-sidebar control-sidebar-dark">
  </aside>
</div>

<?php include('partes/js.php'); ?>

<script>
$(document).ready(function () {

  var statusAtual = '<?php echo $status; ?>';
  var multaAtual  = '<?php echo $multa; ?>';

    var rotulos = {
    ativos: 'Ativos',
    inativos: 'Inativos',
    todos: 'Todos',
    com_multa: 'Com Multa',
    sem_multa: 'Sem Multa'
};

    var tabela = $('#tabela').DataTable({
        paging: true,
        lengthChange: true,
        searching: true,
        ordering: true,
        info: true,
        autoWidth: false,
        responsive: true,

        language: {
            search: "Pesquisar:"
        },

        initComplete: function () {

          var filtro =
'<div class="btn-group btn-group-sm mr-2" role="group" style="vertical-align: middle;">' +
    '<button type="button" class="btn btn-outline-secondary dropdown-toggle" data-toggle="dropdown">' +
        '<i class="fas fa-filter"></i> ' + rotulos[statusAtual] + ' | ' + rotulos[multaAtual] +
    '</button>' +
   '<div class="dropdown-menu">' +

'<h6 class="dropdown-header">Status</h6>' +

'<a class="dropdown-item" href="clientes.php?status=ativos&multa=' + multaAtual + '">Ativos</a>' +
'<a class="dropdown-item" href="clientes.php?status=inativos&multa=' + multaAtual + '">Inativos</a>' +
'<a class="dropdown-item" href="clientes.php?status=todos&multa=' + multaAtual + '">Todos</a>' +

'<div class="dropdown-divider"></div>' +

'<h6 class="dropdown-header">Multas</h6>' +

'<a class="dropdown-item text-danger" href="clientes.php?status=' + statusAtual + '&multa=com_multa">' +
'<i class="fas fa-exclamation-circle"></i> Com Multa</a>' +

'<a class="dropdown-item" href="clientes.php?status=' + statusAtual + '&multa=sem_multa">' +
'<i class="fas fa-check-circle text-success"></i> Sem Multa</a>' +

'<a class="dropdown-item" href="clientes.php?status=' + statusAtual + '&multa=todos">' +
'Todas</a>' +

'</div>';

$('#tabela_filter label').before(filtro);

        }

    });

    // Botão "Pagar Multa" no modal do cliente — quita todas as multas em aberto dele
    $(document).on('click', '.btn-pagar-multa', function () {
        var $btn      = $(this);
        var idCliente = $btn.data('cliente');

        if (!confirm('Confirmar quitação de todas as multas em aberto deste cliente?\nIsso liberará o cliente para novos empréstimos.')) {
            return;
        }

        $btn.prop('disabled', true);

        var fd = new FormData();
        fd.append('nCliente', idCliente);
        fd.append('ajax', '1');

        fetch('php/salvarEmprestimo.php?funcao=P', { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function () { location.reload(); })
            .catch(function () {
                alert('Não foi possível quitar a multa. Tente novamente.');
                $btn.prop('disabled', false);
            });
    });

});

// =========================================================================
// BLOCO DE MÁSCARAS E VALIDAÇÕES DO FORMULÁRIO (CPF E TELEFONE)
// =========================================================================
$(function () {
  const formNovoCliente = document.getElementById("formNovoCliente");
  const cpfModal = document.getElementById("iCpf");
  const telefoneModal = document.getElementById("iTelefone");

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
      valor = valor.replace(/^(\d{2})(\d)/g, '($1) $2'); // Coloca parênteses no DDD e espaço
      valor = valor.replace(/(\d)(\d{4})$/, '$1-$2');    // Coloca o traço antes dos últimos 4 dígitos
      this.value = valor;
    });
  }

  // Tratamento no momento do envio do formulário
  if (formNovoCliente) {
    formNovoCliente.addEventListener('submit', function (e) {
      // Remove a formatação do CPF antes de enviar, para salvar só os números no banco
      if (cpfModal) {
        cpfModal.value = cpfModal.value.replace(/\D/g, "");
      }
      
      // Opcional: Se quiser remover a formatação do telefone para salvar apenas números, descomente as linhas abaixo:
      // if (telefoneModal) {
      //   telefoneModal.value = telefoneModal.value.replace(/\D/g, "");
      // }
    });
  }
});
</script>

</body>
</html>