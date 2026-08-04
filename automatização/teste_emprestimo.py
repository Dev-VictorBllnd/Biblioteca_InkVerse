"""
TESTE AUTOMATIZADO - REGISTRO DE EMPRÉSTIMO (DASHBOARD)
Sistema: Biblioteca InkVerse
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
from selenium.common.exceptions import TimeoutException, StaleElementReferenceException
import time
import random
import os
import webbrowser


class TesteAutomatizadoEmprestimo:
    def __init__(self, url_base, login_email, login_senha):
        self.url_base = url_base.rstrip("/")
        self.login_email = login_email
        self.login_senha = login_senha

        # Salva os resultados FORA da pasta do sistema (fora do
        # htdocs/Biblioteca_InkVerse) - direto na Área de Trabalho do
        # usuário, para não misturar prints de teste com os arquivos do
        # projeto.
        self.diretorio_teste = os.path.join(os.path.expanduser("~"), "Desktop", "TesteEmprestimos")

        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)

        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")

        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)

        print("✓ Ambiente preparado e pasta 'TesteEmprestimos' verificada!")

    # ------------------------------------------------------------------
    # LOGIN
    # ------------------------------------------------------------------
    def fazer_login(self):
        print("🔐 Efetuando login...")
        self.driver.get(f"{self.url_base}/index.php")

        self.wait.until(EC.presence_of_element_located((By.NAME, "nEmail"))).send_keys(self.login_email)
        self.driver.find_element(By.NAME, "nSenha").send_keys(self.login_senha)
        self.driver.find_element(By.CSS_SELECTOR, "button.btn-login").click()

        # Espera sair da tela de login (redirecionamento pós-autenticação)
        self.wait.until(lambda d: "index.php" not in d.current_url)
        print("✓ Login efetuado com sucesso!")

    # ------------------------------------------------------------------
    # LEITURA DAS REGRAS DE BLOQUEIO DO PRÓPRIO JS DA TELA
    # ------------------------------------------------------------------
    def ler_variaveis_js(self):
        """Lê os mapas JS que o próprio sistema usa para bloquear o
        formulário (clientesComMulta / pendentesPorCliente), evitando
        que o teste esbarre em alert()/confirm() nativos do navegador."""
        clientes_com_multa = self.driver.execute_script("return clientesComMulta;") or {}
        pendentes_por_cliente = self.driver.execute_script("return pendentesPorCliente;") or {}
        return clientes_com_multa, pendentes_por_cliente

    def gerar_quantidade_livros_fake(self):
        return random.randint(1, 2)

    def escolher_cliente_seguro(self):
        """Escolhe, entre as <option> do select, um idCliente que NÃO
        esteja em clientesComMulta e que tenha 0 pendências - assim o
        submit não esbarra em alert()/confirm() nativo."""
        clientes_com_multa, pendentes_por_cliente = self.ler_variaveis_js()

        select_el = self.driver.find_element(By.ID, "iCliente")
        options = select_el.find_elements(By.TAG_NAME, "option")
        candidatos = []
        for opt in options:
            valor = opt.get_attribute("value")
            if not valor:
                continue
            if valor in clientes_com_multa:
                continue
            if pendentes_por_cliente.get(valor, 0) > 0:
                continue
            candidatos.append((valor, opt.text))

        if not candidatos:
            raise RuntimeError(
                "Nenhum cliente 'seguro' (sem multa e sem livro pendente) encontrado. "
                "Cadastre/limpe um cliente de teste antes de rodar."
            )

        return random.choice(candidatos)

    def selecionar_cliente_por_id(self, id_cliente):
        # Campo <select> por trás de um Select2: setar direto via JS e
        # disparar 'change' para o jQuery/Select2 e o JS da página reagirem.
        select_el = self.driver.find_element(By.ID, "iCliente")
        self.driver.execute_script(
            """
            arguments[0].value = arguments[1];
            arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
            """,
            select_el,
            str(id_cliente),
        )

    def selecionar_livros(self, quantidade=1):
        """Os livros não são inputs - são botões '.item-disponivel' que
        precisam ser CLICADOS um a um. Cada clique remove o botão da lista
        e recria a listagem, então reconsultamos o DOM a cada iteração
        para evitar StaleElementReferenceException."""
        titulos_escolhidos = []
        for _ in range(quantidade):
            botoes = self.driver.find_elements(By.CSS_SELECTOR, "#listaDisponiveis .item-disponivel")
            if not botoes:
                break
            alvo = random.choice(botoes)
            titulo = alvo.get_attribute("data-titulo")
            try:
                alvo.click()
            except StaleElementReferenceException:
                botoes = self.driver.find_elements(By.CSS_SELECTOR, "#listaDisponiveis .item-disponivel")
                if not botoes:
                    break
                alvo = botoes[0]
                titulo = alvo.get_attribute("data-titulo")
                alvo.click()
            titulos_escolhidos.append(titulo)
            time.sleep(0.15)  # pequena folga entre cliques
        return titulos_escolhidos

    def cliente_aparece_na_tabela(self, nome_cliente):
        """Procura o cliente usando a API do DataTables (via jQuery), que
        enxerga os dados de TODAS as páginas - não só a página exibida no
        momento. Checar apenas o HTML visível (ex: XPath direto na tabela)
        falha sempre que o novo registro cai em outra página, o que fica
        mais comum quanto mais empréstimos/clientes o sistema acumula."""
        script = """
        var termo = arguments[0];
        if (!$.fn.dataTable.isDataTable('#tabela')) { return null; }
        var tabela = $('#tabela').DataTable();
        var dados = tabela.rows().data().toArray();
        return dados.some(function(linha) {
            return linha[1] && linha[1].toString().indexOf(termo) !== -1;
        });
        """
        return self.driver.execute_script(script, nome_cliente)

    # ------------------------------------------------------------------
    # SCREENSHOT E RELATÓRIO
    # ------------------------------------------------------------------
    def tirar_screenshot(self, nome_arquivo):
        caminho = os.path.join(self.diretorio_teste, nome_arquivo)
        self.driver.save_screenshot(caminho)
        return nome_arquivo

    def gerar_relatorio_html(self):
        caminho_html = os.path.join(self.diretorio_teste, "dashboard.html")

        sucessos = sum(1 for r in self.resultados_testes if r["status"] == "Sucesso")
        falhas = len(self.resultados_testes) - sucessos

        html_content = f"""
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Dashboard de Testes - Empréstimos InkVerse</title>
            <style>
                body {{ font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 20px; }}
                .container {{ max-width: 1000px; margin: auto; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }}
                h1 {{ color: #0b1a2c; text-align: center; }}
                .summary {{ display: flex; justify-content: space-around; margin-bottom: 30px; padding: 15px; background: #e9ecef; border-radius: 5px; }}
                .card {{ text-align: center; }}
                .card h2 {{ margin: 0; font-size: 2em; }}
                .status-sucesso {{ color: #28a745; }}
                .status-falha {{ color: #dc3545; }}
                table {{ width: 100%; border-collapse: collapse; margin-top: 20px; }}
                th, td {{ padding: 12px; border-bottom: 1px solid #ddd; text-align: left; }}
                th {{ background-color: #0b1a2c; color: white; }}
                .img-link {{ color: #2563eb; text-decoration: none; font-weight: bold; }}
                tr:hover {{ background-color: #f1f1f1; }}
            </style>
        </head>
        <body>
            <div class="container">
                <h1>Relatório de Automação de Empréstimos</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Livro(s)</th>
                            <th>Status</th>
                            <th>Detalhe</th>
                            <th>Evidência</th>
                        </tr>
                    </thead>
                    <tbody>
        """

        for r in self.resultados_testes:
            cor_status = "status-sucesso" if r["status"] == "Sucesso" else "status-falha"
            html_content += f"""
                <tr>
                    <td>{r['id']}</td>
                    <td>{r['cliente']}</td>
                    <td>{r['livros']}</td>
                    <td class="{cor_status}">{r['status']}</td>
                    <td>{r['detalhe']}</td>
                    <td><a class="img-link" href="{r['screenshot']}" target="_blank">Visualizar Screenshot</a></td>
                </tr>
            """

        html_content += """
                    </tbody>
                </table>
            </div>
        </body>
        </html>
        """

        with open(caminho_html, "w", encoding="utf-8") as f:
            f.write(html_content)

        return caminho_html

    # ------------------------------------------------------------------
    # FLUXO PRINCIPAL DE REGISTRO DE EMPRÉSTIMO
    # ------------------------------------------------------------------
    def executar_teste_completo(self, quantidade):
        self.fazer_login()

        for i in range(quantidade):
            print(f"\n🚀 Iniciando registro de empréstimo {i + 1} de {quantidade}...")
            status = "Falha"
            detalhe = ""
            nome_cliente = "N/A"
            livros = []
            nome_print = None  # será preenchido assim que o formulário estiver pronto para envio

            try:
                self.driver.get(f"{self.url_base}/emprestimo.php")

                # Abre o modal "Novo Empréstimo"
                self.wait.until(
                    EC.element_to_be_clickable((By.CSS_SELECTOR, "[data-target='#novoEmprestimoModal']")),
                    "ETAPA: botão 'Novo Empréstimo' não ficou clicável (verifique se o login realmente deu acesso a emprestimo.php)"
                ).click()

                # Espera o modal terminar a transição do Bootstrap (fade) e ficar
                # de fato interativo, não só presente no DOM.
                self.wait.until(
                    EC.visibility_of_element_located((By.CSS_SELECTOR, "#novoEmprestimoModal.show")),
                    "ETAPA: modal #novoEmprestimoModal não abriu (classe 'show' não apareceu - possível erro de JS/Bootstrap)"
                )
                time.sleep(0.4)  # pequena folga para a animação de opacidade terminar

                # Cliente: escolhe um que não tenha multa nem livro pendente,
                # para não esbarrar em alert()/confirm() nativo do navegador.
                id_cliente, nome_cliente = self.escolher_cliente_seguro()
                self.selecionar_cliente_por_id(id_cliente)

                # Livros: são botões clicáveis, não inputs de formulário.
                quantidade_livros = self.gerar_quantidade_livros_fake()
                livros = self.selecionar_livros(quantidade_livros)

                if not livros:
                    detalhe = "Nenhum livro disponível para emprestar"
                    nome_print = self.tirar_screenshot(f"erro_{i + 1}.png")
                    self.resultados_testes.append({
                        "id": i + 1, "cliente": nome_cliente, "livros": "-",
                        "status": "Falha", "detalhe": detalhe, "screenshot": nome_print,
                    })
                    continue

                # --- PRINT DOS DADOS PREENCHIDOS (antes de enviar) ---
                nome_print = self.tirar_screenshot(f"preenchimento_{i + 1}.png")

                botao_salvar = self.wait.until(
                    EC.element_to_be_clickable((By.CSS_SELECTOR, "#formNovoEmprestimo button[type='submit']")),
                    "ETAPA: botão Salvar não ficou clicável"
                )
                self.driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", botao_salvar)
                botao_salvar.click()

                # --- Validação (mensagens de sucesso/erro definidas em salvarEmprestimo.php) ---
                self.wait.until(
                    lambda d: "emprestimo.php" in d.current_url,
                    "ETAPA: não redirecionou de volta para emprestimo.php após o submit"
                )
                url_atual = self.driver.current_url

                if "erro=sem_livro" in url_atual:
                    status, detalhe = "Falha", "Sistema recusou: nenhum livro selecionado"
                elif "erro=limite" in url_atual:
                    status, detalhe = "Falha", "Sistema recusou: limite de 5 livros por cliente atingido"
                elif "erro=multa" in url_atual:
                    status, detalhe = "Falha", "Sistema recusou: cliente com multa em aberto"
                else:
                    try:
                        self.wait.until(
                            lambda d: self.cliente_aparece_na_tabela(nome_cliente) is True,
                            "ETAPA: cliente não apareceu na tabela de empréstimos ativos (em nenhuma página)"
                        )
                        status = "Sucesso"
                        detalhe = f"Empréstimo registrado para {nome_cliente} ({len(livros)} livro(s))"
                    except TimeoutException:
                        status = "Falha"
                        detalhe = f"Redirecionou sem erro= mas cliente não apareceu na tabela em nenhuma página (URL final: {url_atual})"

            except Exception as e:
                tipo_erro = type(e).__name__
                msg_bruta = getattr(e, "msg", None) or str(e)
                primeira_linha = msg_bruta.strip().splitlines()[0] if msg_bruta.strip() else "sem mensagem"
                detalhe = f"Erro no processo ({tipo_erro}): {primeira_linha}"
                print(f"✗ {detalhe}")

            # Se algum erro impediu de chegar no print de preenchimento (ex:
            # falhou antes de terminar de preencher o form), tira um print
            # de emergência agora, só para fins de depuração.
            if nome_print is None:
                nome_print = self.tirar_screenshot(f"erro_{i + 1}.png")

            self.resultados_testes.append({
                "id": i + 1,
                "cliente": nome_cliente,
                "livros": ", ".join(livros) if livros else "-",
                "status": status,
                "detalhe": detalhe,
                "screenshot": nome_print,
            })

        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()

        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open("file://" + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- AUTOMAÇÃO DE EMPRÉSTIMOS - INKVERSE ---")

    URL_LOCAL = "http://localhost:8080/Biblioteca_InkVerse"
    LOGIN_EMAIL = "admin@gmail.com"
    LOGIN_SENHA = "Admin123@"

    try:
        qtd = int(input("Quantos empréstimos você deseja registrar hoje? "))
        if qtd > 0:
            teste = TesteAutomatizadoEmprestimo(
                url_base=URL_LOCAL,
                login_email=LOGIN_EMAIL,
                login_senha=LOGIN_SENHA,
            )
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")