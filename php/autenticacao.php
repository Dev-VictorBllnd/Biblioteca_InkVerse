<?php
 if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// Impede que o navegador (ou qualquer proxy) guarde esta página em cache.
// Assim, ao clicar em "voltar", o navegador é obrigado a pedir a página de
// novo ao servidor em vez de mostrar a versão antiga guardada localmente.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

// Se não houver uma sessão de login válida, expulsa para a tela de login
if (empty($_SESSION['logado']) || $_SESSION['logado'] != 1) {

    // Descobre se este script está dentro da pasta /php/ para montar o
    // caminho de redirecionamento corretamente (../index.php vs index.php)
    $prefixo = (strpos($_SERVER['SCRIPT_NAME'], '/php/') !== false) ? '../' : '';

    header("Location: {$prefixo}index.php");
    exit();
}
