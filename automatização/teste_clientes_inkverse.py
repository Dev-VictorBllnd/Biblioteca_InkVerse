"""
Automação Selenium - Cadastro de Clientes (Biblioteca InkVerse)
Com Dashboard HTML Estilizado
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import Select, WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options
import time, random, os, webbrowser


class TesteClientes:
    def __init__(self, url_base, login_email, login_senha):
        self.url_base = url_base.rstrip("/")
        self.login_email = login_email
        self.login_senha = login_senha

        pasta_do_script = os.path.dirname(os.path.abspath(__file__))
        self.diretorio_teste = os.path.join(pasta_do_script, "TesteResultados")
        os.makedirs(self.diretorio_teste, exist_ok=True)
        self.resultados_testes = []

        chrome_options = Options()
        chrome_options.add_argument("--start-maximized")
        self.driver = webdriver.Chrome(options=chrome_options)
        self.wait = WebDriverWait(self.driver, 10)

    # ------------------------------------------------------------------
    # LOGIN
    # ------------------------------------------------------------------
    def fazer_login(self):
        self.driver.get(f"{self.url_base}/index.php")
        self.wait.until(
            EC.presence_of_element_located((By.NAME, "nEmail")),
            "ETAPA LOGIN: campo de e-mail não apareceu"
        ).send_keys(self.login_email)
        self.driver.find_element(By.NAME, "nSenha").send_keys(self.login_senha)
        self.driver.find_element(By.CSS_SELECTOR, "button.btn-login, .btn-login").click()
        self.wait.until(
            lambda d: "index.php" not in d.current_url,
            "ETAPA LOGIN: não saiu da tela de login (usuário/senha errados?)"
        )

    # ------------------------------------------------------------------
    # GERADORES DE DADOS DE TESTE
    # ------------------------------------------------------------------
    def gerar_nome_completo(self):
        primeiros = ["Ana", "Bruno", "Camila", "Daniel", "Eduarda", "Felipe", "Gabriela"]
        sobrenomes = ["Silva", "Souza", "Oliveira", "Costa", "Pereira", "Almeida", "Ramos"]
        return f"{random.choice(primeiros)} {random.choice(sobrenomes)}"

    def gerar_email_unico(self, nome):
        sufixo = str(int(time.time() * 1000))[-6:]
        base = nome.lower().replace(" ", ".")
        return f"{base}.{sufixo}@teste.com"

    def gerar_cpf_unico(self):
        sufixo = str(int(time.time() * 1000))[-6:]
        return f"{random.randint(100, 999)}{random.randint(100, 999)}{sufixo}"[:11]

    def gerar_telefone(self):
        return f"({random.randint(11, 99)}) 9{random.randint(1000,9999)}-{random.randint(1000,9999)}"

    def gerar_dados_cliente(self):
        nome = self.gerar_nome_completo()
        return {
            "nome": nome,
            "cpf": self.gerar_cpf_unico(),
            "email": self.gerar_email_unico(nome),
            "data_nasc": "1995-04-12",
            "telefone": self.gerar_telefone(),
            "cep": "89201-000",
            "endereco": "Rua das Palmeiras",
            "numero": str(random.randint(1, 999)),
            "complemento": "Apto 101",
            "bairro": "Centro",
            "cidade": "Joinville",
            "uf": "SC",
        }

    # ------------------------------------------------------------------
    # CADASTRO DE UM CLIENTE
    # ------------------------------------------------------------------
    def cadastrar_cliente(self, dados, indice):
        detalhe = ""
        status = "FALHA"
        nome_print = None

        try:
            self.driver.get(f"{self.url_base}/clientes.php")

            # 1) Abre o modal
            self.wait.until(
                EC.element_to_be_clickable((By.CSS_SELECTOR, "[data-target='#novoClienteModal']")),
                "ETAPA MODAL: botão 'Novo Cliente' não ficou clicável"
            ).click()

            self.wait.until(
                EC.visibility_of_element_located((By.CSS_SELECTOR, "#novoClienteModal.show")),
                "ETAPA MODAL: modal não abriu (classe .show não apareceu)"
            )
            time.sleep(0.4)  # folga para a animação do Bootstrap terminar

            modal = self.driver.find_element(By.ID, "novoClienteModal")

            # 2) Campos de texto simples
            modal.find_element(By.NAME, "nNome").send_keys(dados["nome"])
            modal.find_element(By.NAME, "nCpf").send_keys(dados["cpf"])
            modal.find_element(By.NAME, "nEmail").send_keys(dados["email"])
            modal.find_element(By.NAME, "nTelefone").send_keys(dados["telefone"])
            modal.find_element(By.NAME, "CEP").send_keys(dados["cep"])
            modal.find_element(By.NAME, "Endereco").send_keys(dados["endereco"])
            modal.find_element(By.NAME, "Numero").send_keys(dados["numero"])
            modal.find_element(By.NAME, "Complemento").send_keys(dados["complemento"])
            modal.find_element(By.NAME, "Bairro").send_keys(dados["bairro"])
            modal.find_element(By.NAME, "Cidade").send_keys(dados["cidade"])
            modal.find_element(By.NAME, "UF").send_keys(dados["uf"])

            # 3) Campo de data -> SEMPRE via JavaScript
            campo_data = modal.find_element(By.NAME, "nDatanasc")
            self.driver.execute_script("""
                arguments[0].value = arguments[1];
                arguments[0].dispatchEvent(new Event('input', { bubbles: true }));
                arguments[0].dispatchEvent(new Event('change', { bubbles: true }));
            """, campo_data, dados["data_nasc"])

            # 4) Select de situação
            select_ativo = Select(modal.find_element(By.NAME, "nAtivo"))
            select_ativo.select_by_value("S")

            # 5) Screenshot ANTES de salvar
            nome_print = f"clientes_preenchimento_{indice}.png"
            self.driver.save_screenshot(os.path.join(self.diretorio_teste, nome_print))

            # 6) Salvar
            modal.find_element(By.CSS_SELECTOR, "button[type='submit']").click()

            # 7) Validar resultado
            self.wait.until(
                lambda d: "sucesso=1" in d.current_url or "erro=" in d.current_url,
                "ETAPA VALIDAÇÃO: página não redirecionou após salvar"
            )

            if "erro=cpf_existe" in self.driver.current_url:
                status = "FALHA"
                detalhe = "CPF já existente no sistema"
            elif "sucesso=1" in self.driver.current_url:
                status = "SUCESSO"
                detalhe = "Cadastro processado com sucesso!"
            else:
                status = "FALHA"
                detalhe = f"Retorno inesperado: {self.driver.current_url}"

        except Exception as e:
            status = "FALHA"
            detalhe = f"Erro inesperado: {str(e).splitlines()[0]}"
            try:
                nome_print = f"clientes_erro_{indice}.png"
                self.driver.save_screenshot(os.path.join(self.diretorio_teste, nome_print))
            except Exception:
                pass

        self.resultados_testes.append({
            "id": indice,
            "nome": dados["nome"],
            "cpf": dados["cpf"],
            "email": dados["email"],
            "status": status,
            "detalhe": detalhe,
            "screenshot": nome_print,
        })
        print(f"[{indice}] {dados['nome']} -> {status} ({detalhe})")

    # ------------------------------------------------------------------
    # RODAR EM LOTE
    # ------------------------------------------------------------------
    def rodar(self, quantidade=1):
        self.fazer_login()
        for i in range(quantidade):
            dados = self.gerar_dados_cliente()
            self.cadastrar_cliente(dados, i + 1)
        
        caminho_report = self.gerar_relatorio_html()
        self.driver.quit()
        
        print(f"\n✅ Testes finalizados! Relatório gerado em: {caminho_report}")
        webbrowser.open("file://" + os.path.realpath(caminho_report))

    # ------------------------------------------------------------------
    # RELATÓRIO (MODIFICADO PARA O DASHBOARD ESTILIZADO)
    # ------------------------------------------------------------------
    def gerar_relatorio_html(self):
        caminho_html = os.path.join(self.diretorio_teste, "dashboard.html")

        sucessos = sum(1 for r in self.resultados_testes if r["status"] == "SUCESSO")
        falhas = len(self.resultados_testes) - sucessos

        html_content = f"""
        <!DOCTYPE html>
        <html lang="pt-br">
        <head>
            <meta charset="UTF-8">
            <title>Dashboard de Testes - Cadastro de Clientes</title>
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
                <h1>Relatório de Automação de Cadastro de Clientes</h1>
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
                            <th>CPF</th>
                            <th>E-mail</th>
                            <th>Status</th>
                            <th>Detalhe</th>
                            <th>Evidência</th>
                        </tr>
                    </thead>
                    <tbody>
        """

        for r in self.resultados_testes:
            cor_status = "status-sucesso" if r["status"] == "SUCESSO" else "status-falha"
            html_content += f"""
                <tr>
                    <td>{r['id']}</td>
                    <td>{r['nome']}</td>
                    <td>{r['cpf']}</td>
                    <td>{r['email']}</td>
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


if __name__ == "__main__":
    # --- CONFIGURAÇÃO: ajuste aqui antes de rodar ---
    URL_BASE = "http://localhost:8080/Biblioteca_InkVerse"   # ajuste para a sua URL local
    LOGIN_EMAIL = "admin@gmail.com"
    LOGIN_SENHA = "Admin123@"

    try:
        qtd = int(input("Quantos clientes você deseja cadastrar hoje? "))
        if qtd > 0:
            teste = TesteClientes(
                url_base=URL_BASE,
                login_email=LOGIN_EMAIL,
                login_senha=LOGIN_SENHA,
            )
            teste.rodar(qtd)
        else:
            print("Quantidade inválida.")
    except ValueError:
        print("Por favor, digite apenas números inteiros.")