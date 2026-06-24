<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function montaMenu($n1, $n2) {
    
    $clsDashboard    = 'text-light'; $styDashboard    = '';
    $clsLivros       = 'text-light'; $styLivros       = '';
    $clsFuncionarios = 'text-light'; $styFuncionarios = '';
    $clsClientes     = 'text-light'; $styClientes     = '';
    $clsEmprestimos  = 'text-light'; $styEmprestimos  = '';
    $clsPerfil       = 'text-light'; $styPerfil       = '';
    
    $activeClass = 'text-white active';
    $activeStyle = 'background-color: #2563eb; border-radius: 10px;';

    switch ($n2) {
        case 'dashboard':
            $clsDashboard = $activeClass;
            $styDashboard = $activeStyle;
            break;     
        case 'funcionarios':  
            $clsFuncionarios = $activeClass;
            $styFuncionarios = $activeStyle;
            break;
        case 'clientes':
            $clsClientes = $activeClass;
            $styClientes = $activeStyle;
            break;
        case 'livros':
            $clsLivros = $activeClass;
            $styLivros = $activeStyle;
            break;
        case 'emprestimos':
        case 'emprestimo':
            $clsEmprestimos = $activeClass;
            $styEmprestimos = $activeStyle;
            break;
        case 'perfil':
            $clsPerfil = $activeClass;
            $styPerfil = $activeStyle;
            break;
    }

    $html = '
    <nav class="mt-4">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
            
            <li class="nav-item mb-1">
                <a href="dashboard.php" class="nav-link '.$clsDashboard.'" style="'.$styDashboard.'">
                    <i class="nav-icon fas fa-home"></i>
                    <p>Dashboard</p>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="livros.php" class="nav-link '.$clsLivros.'" style="'.$styLivros.'">
                    <i class="nav-icon fas fa-book-open"></i>
                    <p>Livros</p>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="funcionarios.php" class="nav-link '.$clsFuncionarios.'" style="'.$styFuncionarios.'">
                    <i class="nav-icon fas fa-user"></i>
                    <p>Funcionários</p>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="clientes.php" class="nav-link '.$clsClientes.'" style="'.$styClientes.'">
                    <i class="nav-icon fas fa-user-friends"></i>
                    <p>Clientes</p>
                </a>
            </li>

            <li class="nav-item mb-1">
                <a href="emprestimo.php" class="nav-link '.$clsEmprestimos.'" style="'.$styEmprestimos.'">
                    <i class="nav-icon fas fa-exchange-alt"></i>
                    <p>Empréstimo</p>
                </a>
            </li>

            <li class="nav-item mt-4">
                <a href="#" class="nav-link text-danger" data-toggle="modal" data-target="#modalLogout">
                    <i class="nav-icon fas fa-sign-out-alt"></i>
                    <p>Sair do Sistema</p>
                </a>
            </li>

        </ul>
    </nav>';

    return $html;
}
?>