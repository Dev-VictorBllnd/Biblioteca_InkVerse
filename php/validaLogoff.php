<?php
    // Precisa "entrar" na sessão existente antes de conseguir destruí-la —
    // era exatamente isso que faltava aqui antes (por isso o logout não
    // funcionava de verdade: session_destroy() sem session_start() não
    // tem nenhuma sessão associada para apagar).
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Esvazia todas as variáveis da sessão (logado, idLogin, idCargo, etc.)
    $_SESSION = array();

    // Remove o cookie de sessão do navegador do usuário
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Destroi a sessão no servidor
    session_destroy();

    header('Location: ../index.php');
    exit();
?>
