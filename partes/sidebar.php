<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #0b1a2c;">
    <div class="brand-link text-left" style="border-bottom: none; padding: 20px 0.5rem;">
    <img src="/BIBLIOTECA_InkVerse/dist/img/logo.png"
     alt="Logo Biblioteca"
     width="60">
     <span class="brand-text font-weight-bold">
        Biblioteca
    </span>
    </div>

    <div class="sidebar">
      
      <?php
        // Verifica se a tela atual é a do perfil para aplicar o estilo azul
        $isPerfil = (isset($_SESSION['menu-n2']) && $_SESSION['menu-n2'] == 'perfil');
        
        // Se for o perfil, fundo azul arredondado. Se não, apenas a linha divisória transparente
        $estiloPerfil = $isPerfil 
            ? 'background-color: #2563eb; border-radius: 10px; padding: 10px; margin-top: 10px;' 
            : 'border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 1rem; margin-top: 10px;';
      ?>

      <!-- Painel do Usuário (Foto e Nome) -->
      <div class="user-panel mb-3 d-flex" style="<?php echo $estiloPerfil; ?> align-items: center;">
        <div class="image">
          <img src="<?php echo fotoUsuario($_SESSION['idLogin']); ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="perfil.php" class="d-block text-white" style="<?php echo $isPerfil ? 'font-weight: bold;' : ''; ?>">
            <?php echo nomeUsuario($_SESSION['idLogin']); ?>
          </a>
        </div>
      </div>

      <!-- Aqui chamamos a função que monta o menu abaixo do perfil -->
      <?php echo montaMenu($_SESSION['menu-n1'], $_SESSION['menu-n2']); ?>

    </div>
</aside>

<div class="modal fade" id="modalLogout" tabindex="-1" role="dialog" aria-labelledby="modalLogoutLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="modalLogoutLabel">
            <i class="fas fa-sign-out-alt"></i> Confirmar Saída
        </h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Fechar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body text-dark" style="white-space: normal;">
        <p>Tem a certeza de que deseja terminar a sessão e sair do InkVerse?</p>
      </div>
      
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-outline-dark" data-dismiss="modal">Cancelar</button>
        <a href="php/validaLogoff.php" class="btn btn-danger font-weight-bold">Sim, Quero Sair</a>
      </div>
      
    </div>
  </div>
</div>