"""
TESTE AUTOMATIZADO - CADASTRO DE LIVRO (DASHBOARD)
Sistema: Biblioteca InkVerse
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
import time
import random
import string
import os
import webbrowser


class TesteAutomatizadoLivro:
    def __init__(self, url_base, login_email, login_senha):
        self.url_base = url_base.rstrip("/")
        self.login_email = login_email
        self.login_senha = login_senha

        # Sempre cria a pasta de resultados ao lado do próprio arquivo .py,
        # independente da pasta de onde o comando "python ..." foi executado.
        pasta_do_script = os.path.dirname(os.path.abspath(__file__))
        self.diretorio_teste = os.path.join(pasta_do_script, "TesteCadastroLivro")

        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)

        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")

        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)

        print("✓ Ambiente preparado e pasta 'TesteCadastroLivro' verificada!")

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
    # GERADORES DE DADOS FAKE
    # ------------------------------------------------------------------
    def gerar_isbn_fake(self):
        # Formato parecido com ISBN real, mas com sufixo de tempo pra garantir
        # unicidade (o campo Isbn tem UNIQUE implícito via checagem no PHP:
        # erro_isbn=1 se já existir). maxlength do campo é 20.
        sufixo = str(int(time.time() * 1000))[-9:]
        return f"978-{sufixo[:2]}-{sufixo[2:6]}-{sufixo[6:]}"

    def gerar_titulo_fake(self):
        adjetivos = [
            "Sombras", "Ecos", "Fragmentos", "Segredos", "Memórias", "Ruínas",
            "Reflexos", "Ventos", "Cinzas", "Horizontes", "Labirintos", "Vestígios",
        ]
        complementos = [
            "do Amanhã", "Perdidos", "de Cristal", "Eternos", "do Silêncio",
            "de um Sonho", "Esquecidos", "da Meia-Noite", "do Destino", "Sem Volta",
        ]
        return f"{random.choice(adjetivos)} {random.choice(complementos)}"

    def gerar_autor_fake(self):
        primeiros_nomes = [
            "Ana", "Bruno", "Camila", "Daniel", "Eduarda", "Felipe", "Gabriela",
            "Henrique", "Isabela", "João", "Larissa", "Marcos", "Natália",
            "Otávio", "Patrícia", "Rafael", "Sabrina", "Tiago", "Vanessa",
            "William", "Yasmin", "Carlos", "Fernanda", "Gustavo", "Juliana",
        ]
        sobrenomes = [
            "Silva", "Souza", "Oliveira", "Costa", "Pereira", "Almeida",
            "Ramos", "Gouveia", "Lima", "Ferreira", "Rodrigues", "Carvalho",
            "Gomes", "Martins", "Araújo", "Barbosa", "Cardoso", "Teixeira",
        ]
        return f"{random.choice(primeiros_nomes)} {random.choice(sobrenomes)}"

    def gerar_ano_fake(self):
        return random.randint(1950, time.localtime().tm_year)

    def gerar_qtd_fake(self):
        return random.randint(1, 5)

    def gerar_dados_aleatorios(self):
        return {
            "titulo": self.gerar_titulo_fake(),
            "autor": self.gerar_autor_fake(),
            "ano": self.gerar_ano_fake(),
            "isbn": self.gerar_isbn_fake(),
            "qtd": self.gerar_qtd_fake(),
        }

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
            <title>Dashboard de Testes - Livros InkVerse</title>
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
                <h1>Relatório de Automação de Cadastro de Livros</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>ISBN</th>
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
                    <td>{r['titulo']}</td>
                    <td>{r['autor']}</td>
                    <td>{r['isbn']}</td>
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
    # FLUXO PRINCIPAL DE CADASTRO
    # ------------------------------------------------------------------
    def executar_teste_completo(self, quantidade):
        self.fazer_login()

        for i in range(quantidade):
            print(f"\n🚀 Iniciando cadastro de livro {i + 1} de {quantidade}...")
            dados = self.gerar_dados_aleatorios()
            status = "Falha"
            detalhe = ""
            nome_print = None  # será preenchido assim que o formulário estiver pronto para envio

            try:
                self.driver.get(f"{self.url_base}/livros.php")

                # Abre o modal "Novo Livro"
                self.wait.until(
                    EC.element_to_be_clickable((By.CSS_SELECTOR, "[data-target='#novoLivroModal']")),
                    "ETAPA: botão 'Novo Livro' não ficou clicável (verifique se o login realmente deu acesso a livros.php)"
                ).click()

                # Espera o modal terminar a transição do Bootstrap (fade) e ficar
                # de fato interativo, não só presente no DOM.
                self.wait.until(
                    EC.visibility_of_element_located((By.CSS_SELECTOR, "#novoLivroModal.show")),
                    "ETAPA: modal #novoLivroModal não abriu (classe 'show' não apareceu - possível erro de JS/Bootstrap)"
                )
                campo_titulo = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iTitulo")),
                    "ETAPA: campo Título (iTitulo) não ficou clicável dentro do modal"
                )
                time.sleep(0.4)  # pequena folga para a animação de opacidade terminar

                # Preenchimento dos campos (cada um espera ficar clicável antes de agir)
                campo_titulo.send_keys(dados["titulo"])

                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iAutor")),
                    "ETAPA: campo Autor (iAutor) não ficou clicável"
                ).send_keys(dados["autor"])

                # Gênero: pula o "Selecione..." (índice 0), escolhe aleatório
                # dentre as opções realmente cadastradas no banco.
                select_genero_el = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iGenero")),
                    "ETAPA: select Gênero (iGenero) não ficou clicável"
                )
                select_genero = Select(select_genero_el)
                qtd_generos = len(select_genero.options) - 1
                select_genero.select_by_index(random.randint(1, max(qtd_generos, 1)))

                # Editora: mesma lógica do gênero.
                select_editora_el = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iEditora")),
                    "ETAPA: select Editora (iEditora) não ficou clicável"
                )
                select_editora = Select(select_editora_el)
                qtd_editoras = len(select_editora.options) - 1
                select_editora.select_by_index(random.randint(1, max(qtd_editoras, 1)))

                campo_ano = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iAno")),
                    "ETAPA: campo Ano de Publicação (iAno) não ficou clicável"
                )
                campo_ano.send_keys(str(dados["ano"]))

                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iIsbn")),
                    "ETAPA: campo ISBN (iIsbn) não ficou clicável"
                ).send_keys(dados["isbn"])

                campo_qtd = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iQtd")),
                    "ETAPA: campo Qtd. de Exemplares (iQtd) não ficou clicável"
                )
                # O campo já vem com valor padrão "1" - limpa antes de digitar
                # o valor aleatório, senão o texto fica concatenado (ex: "13").
                campo_qtd.clear()
                campo_qtd.send_keys(str(dados["qtd"]))

                # --- PRINT DOS DADOS PREENCHIDOS (antes de enviar) ---
                nome_print = self.tirar_screenshot(f"preenchimento_{i + 1}.png")

                # Envia o formulário - primeiro clique dispara o modal de
                # confirmação (#modalConfirmCadastro), NÃO envia o form ainda.
                botao_salvar = self.wait.until(
                    EC.element_to_be_clickable(
                        (By.CSS_SELECTOR, "#formNovoLivro button[type='submit']")
                    ),
                    "ETAPA: botão Salvar não ficou clicável"
                )
                self.driver.execute_script(
                    "arguments[0].scrollIntoView({block: 'center'});", botao_salvar
                )
                botao_salvar.click()

                # Espera o modal de confirmação abrir e clica em "Confirmar Cadastro"
                # (é essa segunda etapa que realmente envia o form).
                botao_confirmar = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "btnConfirmarCadastro")),
                    "ETAPA: modal de confirmação (#modalConfirmCadastro) não abriu ou o botão "
                    "'Confirmar Cadastro' não ficou clicável"
                )
                botao_confirmar.click()
                time.sleep(2)

                # --- Validação (mensagens de sucesso/erro definidas em salvarLivro.php) ---
                url_atual = self.driver.current_url

                if "erro_isbn=1" in url_atual:
                    status = "Falha"
                    detalhe = "ISBN já cadastrado (erro_isbn=1 na URL)"
                elif "erro_cad=1" in url_atual:
                    status = "Falha"
                    detalhe = "Erro de validação/cadastro (erro_cad=1 na URL)"
                elif "sucesso_cad=1" in url_atual:
                    status = "Sucesso"
                    detalhe = "Redirecionado com sucesso_cad=1 na URL"
                else:
                    status = "Falha"
                    detalhe = f"Nenhuma confirmação de sucesso encontrada (URL final: {url_atual})"

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
                "titulo": dados["titulo"],
                "autor": dados["autor"],
                "isbn": dados["isbn"],
                "status": status,
                "detalhe": detalhe,
                "screenshot": nome_print,
            })

        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()

        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open("file://" + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- AUTOMAÇÃO DE CADASTRO DE LIVROS - INKVERSE ---")

    URL_LOCAL = "http://localhost:8080/Biblioteca_InkVerse"
    LOGIN_EMAIL = "admin@gmail.com"
    LOGIN_SENHA = "Admin123@"

    try:
        qtd = int(input("Quantos livros você deseja cadastrar hoje? "))
        if qtd > 0:
            teste = TesteAutomatizadoLivro(
                url_base=URL_LOCAL,
                login_email=LOGIN_EMAIL,
                login_senha=LOGIN_SENHA,
            )
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")
