<aside class="main-sidebar sidebar-dark-primary elevation-4" style="background-color: #0b1a2c;">
    <a href="index.php" class="brand-link" style="border-bottom: none; padding: 20px 0.5rem;">
      <i class="fas fa-book ml-3 mr-2 text-white"></i>
      <span class="brand-text font-weight-bold text-white" style="font-size: 1.3rem;">Biblioteca</span>
    </a>

    <div class="sidebar">
      
      <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
        <div class="image">
          <img src="<?php echo fotoUsuario($_SESSION['idLogin']); ?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block text-white"><?php echo nomeUsuario($_SESSION['idLogin']); ?></a>
        </div>
      </div>

      <nav class="mt-4">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          
          <li class="nav-item mb-1">
            <a href="dashboard.php" class="nav-link text-light">
              <i class="nav-icon fas fa-home"></i>
              <p>Dashboard</p>
            </a>
          </li>

          <li class="nav-item mb-1">
            <a href="#" class="nav-link text-light">
              <i class="nav-icon fas fa-book-open"></i>
              <p>Livros</p>
            </a>
          </li>

          <li class="nav-item mb-1">
            <a href="usuarios.php" class="nav-link text-white <?php echo ($_SESSION['menu-n2'] == 'usuarios') ? 'active' : ''; ?>" style="<?php echo ($_SESSION['menu-n2'] == 'usuarios') ? 'background-color: #2563eb; border-radius: 10px;' : ''; ?>">
              <i class="nav-icon fas fa-user"></i>
              <p>Funcionários</p>
            </a>
          </li>

          <li class="nav-item mb-1">
            <a href="#" class="nav-link text-light">
              <i class="nav-icon fas fa-user-friends"></i>
              <p>Clientes</p>
            </a>
          </li>

          <li class="nav-item mb-1">
            <a href="#" class="nav-link text-light">
              <i class="nav-icon fas fa-book-open"></i>
              <p>Empréstimo</p>
            </a>
          </li>

        </ul>
      </nav>
      </div>
    </aside>