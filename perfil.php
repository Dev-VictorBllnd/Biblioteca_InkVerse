<?php 
  session_start();
  include('php/funcoes.php');
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Projeto Modelo - Perfil</title>

  <!-- CSS -->
  <?php include('partes/css.php'); ?>
  <!-- Fim CSS -->

</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <?php include('partes/navbar.php'); ?>
  <!-- Fim Navbar -->

  <!-- Sidebar -->
  <?php 
    $_SESSION['menu-n1'] = '';
    $_SESSION['menu-n2'] = 'perfil';
    include('partes/sidebar.php'); 
  ?>
  <!-- Fim Sidebar -->

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <!-- Espaço -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-12">
            <div class="card">
              <div class="card-header">
                <div class="row">
                  
                  <div class="col-12">
                    <h3 class="card-title">Meu Perfil</h3>
                  </div>

                </div>
              </div>

              

              <!-- /.card-header -->
              <div class="card-body">

                <form method="POST" action="php/salvaPerfil.php" enctype="multipart/form-data">
                  <div class="card-body">
                      <div class="row">	
                          
                          <div class="col-12">
                              <div class="row">	
                          
                                <div class="col-12">
                                  <div class="row">
                                    <div class="col-3 text-center">
                                      <div class="foto-perfil mx-auto">
                                        <img alt="<?php echo $_SESSION['NomeLogin']; ?>" src="<?php echo $_SESSION['FotoLogin']; ?>"  class="foto">
                                        <div class="trocar-imagem">
                                          <i class="fas fa-camera upload-button"></i>
                                          <p>Alterar Foto</p>
                                          <input class="arquivo" name="Foto" type="file" title="" accept="image/*"/>
                                        </div>
                                      </div>
                                    </div>	
                                    <div class="col-9">
                                      <div class="row">											
                                        <div class="col-7">
                                          <div class="form-group">
                                            <label for="iNome">Nome</label>
                                            <input name="nNome" id="iNome" type="text" maxlength="80" class="form-control" value="<?php echo $_SESSION['NomeLogin']; ?>" required>
                                          </div>
                                        </div>											
                                        <div class="col-5">
                                          <div class="form-group">
                                            <label>Nível de Acesso</label>
                                            <input readonly name="nNivelAcesso" type="text" maxlength="50" class="form-control" value="">
                                          </div>
                                        </div>	
                                        <div class="col-7">
                                          <div class="form-group">
                                            <label>E-mail</label>
                                            <input readonly type="email" class="form-control form-control-sm" name="nEmail" id="iEmail" value="">
                                          </div>
                                        </div>		
                                        <div class="col-5">
                                          <div class="form-group">
                                            <label>Campo1</label>
                                            <input readonly type="text" class="form-control form-control-sm" name="nCampo1" id="iCampo1" value="">
                                          </div>
                                        </div>
                                      </div>
                                    </div>
                                  </div>
                                </div>

                              </div>
                          </div>
                          
                      </div>
                  </div>	

                  <div class="card-action" align="right">
                    <a href="perfil.php" class="btn btn-danger" data-toggle="tooltip" title="Cancelar a operação">
                      <span>Cancelar</span>
                    </a>
                    <input type="submit" class="btn btn-success" value="Salvar" data-toggle="tooltip" title="Salvar as alterações no perfil">
                  </div>
                </form>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
            
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->

    </section>
    <!-- /.content -->
  </div>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<!-- JS -->
<?php include('partes/js.php'); ?>
<!-- Fim JS -->

<script>
  $(function () {
    $('#tabela').DataTable({
      "paging": true,
      "lengthChange": true,
      "searching": true,
      "ordering": true,
      "info": true,
      "autoWidth": false,
      "responsive": true,
    });
  });
</script>

</body>
</html>
