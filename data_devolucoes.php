<?php 
  session_start();
  include('php/conexao.php');
  
  if(file_exists('php/funcoes.php')) {
      include('php/funcoes.php');
  }

  // =========================================================================
  // 1. LÓGICA DE DEVOLUÇÃO EM LOTE (Vários livros ao mesmo tempo)
  // =========================================================================
  if (isset($_GET['funcao']) && $_GET['funcao'] == 'devolver_lote') {
      $idCliente = $_POST['idCliente'];
      
      // Verifica se o usuário marcou alguma caixinha
      if(!empty($_POST['livros_devolver'])) {
          foreach($_POST['livros_devolver'] as $info) {
              // O value do checkbox traz o id do Emprestimo e o id do Exemplar separados por um underline "_"
              list($idEmp, $idExe) = explode('_', $info);

              // Registra a devolução
              $sqlDevolver = "UPDATE emprestimo_has_exemplar 
                              SET Data_devolucao = NOW() 
                              WHERE idEmprestimo = '$idEmp' AND idExemplar = '$idExe'";
              mysqli_query($conn, $sqlDevolver);

              // Libera o livro para a estante
              $sqlLiberarLivro = "UPDATE exemplar 
                                  SET Emprestado = 'nao' 
                                  WHERE idExemplar = '$idExe'";
              mysqli_query($conn, $sqlLiberarLivro);
          }
          header("Location: devolucoes.php?cliente=$idCliente&sucesso=1");
          exit;
      } else {
          // Se clicou no botão sem marcar nada
          header("Location: devolucoes.php?cliente=$idCliente&erro=vazio");
          exit;
      }
  }

  // =========================================================================
  // 2. CARREGAMENTO DA TELA (Busca pelo Cliente)
  // =========================================================================
  if (!isset($_GET['cliente'])) {
      header("Location: emprestimo.php");
      exit;
  }

  $idCliente = $_GET['cliente'];

  // Busca o nome do cliente para o cabeçalho
  $sqlNome = mysqli_query($conn, "SELECT Nome FROM cliente WHERE idCliente = '$idCliente'");
  $nomeCliente = mysqli_fetch_assoc($sqlNome)['Nome'];

  // Busca TODOS os livros pendentes desse cliente (mesmo que sejam de empréstimos diferentes)
  $sql = "SELECT e.idEmprestimo, ehe.idExemplar, l.Titulo, ehe.Data_emprestimo, ehe.data_prevista 
          FROM emprestimo_has_exemplar ehe
          JOIN emprestimo e ON e.idEmprestimo = ehe.idEmprestimo
          JOIN exemplar ex ON ehe.idExemplar = ex.idExemplar
          JOIN livro l ON ex.idLivro = l.idLivro
          WHERE e.idCliente = '$idCliente' AND ehe.Data_devolucao IS NULL
          ORDER BY ehe.Data_emprestimo ASC";

  $resultado = mysqli_query($conn, $sql);
  $qtd_livros = mysqli_num_rows($resultado);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>InkVerse - Devolução em Lote</title>
  <?php include('partes/css.php'); ?>
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <?php include('partes/navbar.php'); ?>
  <?php 
    $_SESSION['menu-n1'] = 'biblioteca'; 
    $_SESSION['menu-n2'] = 'emprestimos';
    include('partes/sidebar.php'); 
  ?>

  <div class="content-wrapper">
    <div class="content-header"></div>
    
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            
            <div class="card">
              <div class="card-header">
                <div class="row">
                  <div class="col-9">
                    <h3 class="card-title">Devolução de Livros - Cliente: <strong><?php echo $nomeCliente; ?></strong></h3>
                  </div>
                  <div class="col-3" align="right">
                    <a href="emprestimo.php" class="btn btn-default">
                      <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                  </div>
                </div>
              </div>

              <div class="card-body">
                  
                  <?php if (isset($_GET['sucesso'])): ?>
                      <div class="alert alert-success alert-dismissible">
                          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                          <h5><i class="icon fas fa-check"></i> Devolução Registrada!</h5>
                          Os livros selecionados foram devolvidos e estão disponíveis na prateleira.
                      </div>
                  <?php endif; ?>

                  <?php if (isset($_GET['erro'])): ?>
                      <div class="alert alert-danger alert-dismissible">
                          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                          <h5><i class="icon fas fa-exclamation-triangle"></i> Atenção!</h5>
                          Você precisa selecionar pelo menos um livro para devolver.
                      </div>
                  <?php endif; ?>

                  <?php if ($qtd_livros > 0): ?>
                      <p>Selecione nas caixinhas os livros que o cliente está entregando agora:</p>
                      
                      <form method="POST" action="devolucoes.php?funcao=devolver_lote">
                          <input type="hidden" name="idCliente" value="<?php echo $idCliente; ?>">
                          
                          <table class="table table-bordered table-hover text-nowrap">
                              <thead class="bg-light">
                                  <tr>
                                      <th style="width: 50px; text-align: center;"><i class="fas fa-check-square"></i></th>
                                      <th>Título do Livro</th>
                                      <th>Cód. Exemplar</th>
                                      <th>Data que Pegou</th>
                                      <th>Data Prevista</th>
                                  </tr>
                              </thead>
                              <tbody>
                                  <?php while ($row = mysqli_fetch_assoc($resultado)): ?>
                                      <tr>
                                          <td align="center">
                                              <input type="checkbox" name="livros_devolver[]" value="<?php echo $row['idEmprestimo'].'_'.$row['idExemplar']; ?>" style="width: 20px; height: 20px;">
                                          </td>
                                          <td><strong><?php echo $row['Titulo']; ?></strong></td>
                                          <td><?php echo $row['idExemplar']; ?></td>
                                          <td><?php echo date('d/m/Y', strtotime($row['Data_emprestimo'])); ?></td>
                                          <td><?php echo date('d/m/Y', strtotime($row['data_prevista'])); ?></td>
                                      </tr>
                                  <?php endwhile; ?>
                              </tbody>
                          </table>
                          
                          <button type="submit" class="btn btn-success mt-3" onclick="return confirm('Confirmar a devolução dos livros selecionados?');">
                              <i class="fas fa-check-double"></i> Concluir Devolução
                          </button>
                      </form>

                  <?php else: ?>
                      <div class="alert alert-info text-center mt-3">
                          <h5>Tudo certo!</h5>
                          <p>O cliente <strong><?php echo $nomeCliente; ?></strong> não tem nenhum livro pendente no momento.</p>
                          <a href="emprestimo.php" class="btn btn-primary mt-2">Voltar aos Empréstimos</a>
                      </div>
                  <?php endif; ?>

              </div>
            </div>

          </div>
        </div>
      </div>
    </section>
  </div>

  <aside class="control-sidebar control-sidebar-dark"></aside>
</div>

<?php include('partes/js.php'); ?>
</body>
</html>