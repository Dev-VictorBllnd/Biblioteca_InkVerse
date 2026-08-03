"""
TESTE AUTOMATIZADO - CADASTRO DE FUNCIONÁRIO (DASHBOARD)
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


class TesteAutomatizadoFuncionario:
    def __init__(self, url_base, login_email, login_senha):
        self.url_base = url_base.rstrip("/")
        self.login_email = login_email
        self.login_senha = login_senha

        # Sempre cria a pasta de resultados ao lado do próprio arquivo .py,
        # independente da pasta de onde o comando "python ..." foi executado.
        pasta_do_script = os.path.dirname(os.path.abspath(__file__))
        self.diretorio_teste = os.path.join(pasta_do_script, "TesteCadastroFuncionario")

        if not os.path.exists(self.diretorio_teste):
            os.makedirs(self.diretorio_teste)

        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")

        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)

        print("✓ Ambiente preparado e pasta 'TesteCadastroFuncionario' verificada!")

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
    def gerar_cpf_fake(self):
        return "".join(str(random.randint(0, 9)) for _ in range(11))

    def gerar_telefone_fake(self):
        return f"47{random.randint(90000, 99999)}{random.randint(1000, 9999)}"

    def gerar_senha_forte(self):
        # Garante ao menos 1 minúscula, 1 maiúscula, 1 número e 1 especial
        base = [
            random.choice(string.ascii_lowercase),
            random.choice(string.ascii_uppercase),
            random.choice(string.digits),
            random.choice("!@#$%&*"),
        ]
        resto = [random.choice(string.ascii_letters + string.digits) for _ in range(4)]
        senha = base + resto
        random.shuffle(senha)
        return "".join(senha)

    def gerar_nome_completo(self):
        primeiros_nomes = [
            "Ana", "Bruno", "Camila", "Daniel", "Eduarda", "Felipe", "Gabriela",
            "Henrique", "Isabela", "João", "Larissa", "Marcos", "Natália",
            "Otávio", "Patrícia", "Rafael", "Sabrina", "Tiago", "Vanessa",
            "William", "Yasmin", "Carlos", "Fernanda", "Gustavo", "Juliana",
            "Leonardo", "Mariana", "Paulo", "Renata", "Vitor",
        ]
        sobrenomes = [
            "Silva", "Souza", "Oliveira", "Costa", "Pereira", "Almeida",
            "Ramos", "Gouveia", "Lima", "Ferreira", "Rodrigues", "Carvalho",
            "Gomes", "Martins", "Araújo", "Barbosa", "Cardoso", "Teixeira",
            "Nascimento", "Correia",
        ]
        return f"{random.choice(primeiros_nomes)} {random.choice(sobrenomes)}"

    def gerar_dados_aleatorios(self):
        nome = self.gerar_nome_completo()
        sufixo_unico = str(int(time.time() * 1000))[-6:]  # evita e-mail duplicado
        nome_email = (
            nome.lower()
            .replace(" ", ".")
            .replace("ã", "a").replace("á", "a").replace("â", "a")
            .replace("é", "e").replace("ê", "e")
            .replace("í", "i")
            .replace("ó", "o").replace("ô", "o")
            .replace("ú", "u")
            .replace("ç", "c")
        )

        return {
            "nome": nome,
            "tipo_usuario_index": random.randint(1, 2),  # pula o "Selecione..." (índice 0)
            "login": f"{nome_email}.{sufixo_unico}@teste.com",
            "cpf": self.gerar_cpf_fake(),
            "telefone": self.gerar_telefone_fake(),
            "data_nascimento": f"{random.randint(1970, 2003)}-{random.randint(1, 12):02d}-{random.randint(1, 28):02d}",
            "senha": self.gerar_senha_forte(),
            "ativo": "S",  # sempre Ativo - dado de teste deve ficar utilizável no sistema
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
            <title>Dashboard de Testes - Funcionários InkVerse</title>
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
                <h1>Relatório de Automação de Cadastro de Funcionários</h1>
                <div class="summary">
                    <div class="card"><h3>Total</h3><h2>{len(self.resultados_testes)}</h2></div>
                    <div class="card"><h3 class="status-sucesso">Sucessos</h3><h2>{sucessos}</h2></div>
                    <div class="card"><h3 class="status-falha">Falhas</h3><h2>{falhas}</h2></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Funcionário</th>
                            <th>Login (E-mail)</th>
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
                    <td>{r['nome']}</td>
                    <td>{r['login']}</td>
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
            print(f"\n🚀 Iniciando cadastro de funcionário {i + 1} de {quantidade}...")
            dados = self.gerar_dados_aleatorios()
            status = "Falha"
            detalhe = ""
            nome_print = None  # será preenchido assim que o formulário estiver pronto para envio

            try:
                self.driver.get(f"{self.url_base}/funcionarios.php")

                # Abre o modal "Novo Funcionário"
                self.wait.until(
                    EC.element_to_be_clickable((By.CSS_SELECTOR, "[data-target='#novoUsuarioModal']")),
                    "ETAPA: botão 'Novo Funcionário' não ficou clicável (verifique se o login realmente deu acesso a funcionarios.php)"
                ).click()

                # Espera o modal terminar a transição do Bootstrap (fade) e ficar
                # de fato interativo, não só presente no DOM.
                self.wait.until(
                    EC.visibility_of_element_located((By.CSS_SELECTOR, "#novoUsuarioModal.show")),
                    "ETAPA: modal #novoUsuarioModal não abriu (classe 'show' não apareceu - possível erro de JS/Bootstrap)"
                )
                campo_nome = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iNome")),
                    "ETAPA: campo Nome (iNome) não ficou clicável dentro do modal"
                )
                time.sleep(0.4)  # pequena folga para a animação de opacidade terminar

                # Preenchimento dos campos (cada um espera ficar clicável antes de agir)
                campo_nome.send_keys(dados["nome"])

                select_tipo_el = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iTipoUsuario")),
                    "ETAPA: select Tipo de Usuário (iTipoUsuario) não ficou clicável"
                )
                Select(select_tipo_el).select_by_index(dados["tipo_usuario_index"])

                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iLogin")),
                    "ETAPA: campo E-mail (iLogin) não ficou clicável"
                ).send_keys(dados["login"])
                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iCpf")),
                    "ETAPA: campo CPF (iCpf) não ficou clicável"
                ).send_keys(dados["cpf"])
                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iTelefone")),
                    "ETAPA: campo Telefone (iTelefone) não ficou clicável"
                ).send_keys(dados["telefone"])
                campo_data = self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iDatanasc")),
                    "ETAPA: campo Data de Nascimento (iDatanasc) não ficou clicável"
                )
                # IMPORTANTE: inputs type="date" não devem receber send_keys()
                # com hífens - o navegador interpreta as teclas em segmentos
                # (dia/mês/ano) e o hífen "vaza" dígitos pro segmento errado,
                # gerando datas inválidas (ex: ano 10322). O valor interno de
                # um <input type="date"> é sempre ISO (AAAA-MM-DD), então
                # setamos via JS e disparamos os eventos que o form escuta.
                self.driver.execute_script(
                    """
                    arguments[0].value = arguments[1];
                    arguments[0].dispatchEvent(new Event('input', { bubbles: true }));
                    arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
                    """,
                    campo_data,
                    dados["data_nascimento"],
                )
                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iSenhaModal")),
                    "ETAPA: campo Senha (iSenhaModal) não ficou clicável"
                ).send_keys(dados["senha"])
                self.wait.until(
                    EC.element_to_be_clickable((By.ID, "iConfirmarSenhaModal")),
                    "ETAPA: campo Confirmar Senha (iConfirmarSenhaModal) não ficou clicável"
                ).send_keys(dados["senha"])

                # Situação do funcionário (Ativo/Inativo)
                # IMPORTANTE: o name="nAtivo" se repete em um <select> escondido
                # dentro do modal de EDIÇÃO de cada funcionário da tabela (gerado
                # por listaUsuario()). Por isso restringimos a busca ao modal
                # #novoUsuarioModal, senão By.NAME pega o primeiro da página,
                # que pode estar escondido e nunca ficar clicável.
                select_ativo_el = self.wait.until(
                    EC.element_to_be_clickable(
                        (By.CSS_SELECTOR, "#novoUsuarioModal select[name='nAtivo']")
                    ),
                    "ETAPA: select Situação do Funcionário (nAtivo) não ficou clicável"
                )
                Select(select_ativo_el).select_by_value(dados["ativo"])

                # --- PRINT DOS DADOS PREENCHIDOS (antes de enviar) ---
                # Screenshot tirado aqui mostra exatamente as informações que
                # serão cadastradas, antes de clicar em Salvar.
                nome_print = self.tirar_screenshot(f"preenchimento_{i + 1}.png")

                # Envia o formulário
                botao_salvar = self.wait.until(
                    EC.element_to_be_clickable(
                        (By.CSS_SELECTOR, "#formNovoFuncionario button[type='submit']")
                    ),
                    "ETAPA: botão Salvar não ficou clicável"
                )
                self.driver.execute_script(
                    "arguments[0].scrollIntoView({block: 'center'});", botao_salvar
                )
                botao_salvar.click()
                time.sleep(2)

                # --- Validação (AMBOS os critérios) ---
                url_atual = self.driver.current_url
                page_source = self.driver.page_source.lower()

                if "erro=cpf_existe" in url_atual:
                    status = "Falha"
                    detalhe = "CPF já cadastrado (erro=cpf_existe na URL)"
                elif "erro=" in url_atual:
                    status = "Falha"
                    detalhe = f"Parâmetro de erro detectado na URL: {url_atual}"
                elif "sucesso=1" in url_atual or "sucesso" in page_source:
                    # O sistema redireciona para funcionarios.php?sucesso=1 ao
                    # salvar (não existe mensagem de texto "sucesso" na página,
                    # só o parâmetro na URL) - verificamos os dois por segurança.
                    status = "Sucesso"
                    detalhe = "Redirecionado com sucesso=1 na URL"
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
                "nome": dados["nome"],
                "login": dados["login"],
                "status": status,
                "detalhe": detalhe,
                "screenshot": nome_print,
            })

        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()

        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open("file://" + os.path.realpath(caminho_report))


if __name__ == "__main__":
    print("--- AUTOMAÇÃO DE CADASTRO DE FUNCIONÁRIOS - INKVERSE ---")

    URL_LOCAL = "http://localhost/Biblioteca_InkVerse"
    LOGIN_EMAIL = "admin@gmail.com"   
    LOGIN_SENHA = "Admin123@"      

    try:
        qtd = int(input("Quantos funcionários você deseja cadastrar hoje? "))
        if qtd > 0:
            teste = TesteAutomatizadoFuncionario(
                url_base=URL_LOCAL,
                login_email=LOGIN_EMAIL,
                login_senha=LOGIN_SENHA,
            )
            teste.executar_teste_completo(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")