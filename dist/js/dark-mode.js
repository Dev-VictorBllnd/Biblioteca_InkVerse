/**
 * Modo escuro estilo Claude — alternância + persistência.
 * A classe "dark-mode" é aplicada em <html>, não em <body>, porque o
 * AdminLTE já usa seletores descendentes ".dark-mode .algumaCoisa" e
 * aplicar em <html> evita o "flash" de tela clara ao carregar a página
 * (veja o script bloqueante em partes/css.php).
 */
(function () {
    var STORAGE_KEY = 'inkverse-dark-mode';

    function isDarkModeOn() {
        return document.documentElement.classList.contains('dark-mode');
    }

    function setDarkMode(ativo) {
        document.documentElement.classList.toggle('dark-mode', ativo);
        localStorage.setItem(STORAGE_KEY, ativo ? '1' : '0');
    }

    document.addEventListener('DOMContentLoaded', function () {
        // Aceita tanto o botão do sistema interno quanto o da tela de login
        var botoes = document.querySelectorAll('#toggle-dark-mode, #toggle-dark-mode-login');

        botoes.forEach(function (botao) {
            botao.addEventListener('click', function (e) {
                e.preventDefault();
                setDarkMode(!isDarkModeOn());
            });
        });
    });
})();
