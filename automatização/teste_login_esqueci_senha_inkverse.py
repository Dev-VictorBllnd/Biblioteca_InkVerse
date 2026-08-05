"""
TESTE AUTOMATIZADO - ESQUECI MINHA SENHA
Sistema: Biblioteca InkVerse
Ferramenta: Selenium WebDriver com Python
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options

import os
import time
import webbrowser


class TesteEsqueciSenha:

    def __init__(self, url_base):

        self.url_base = url_base.rstrip("/")

        pasta_script = os.path.dirname(os.path.abspath(__file__))
        self.pasta_resultados = os.path.join(
            pasta_script,
            "TesteEsqueciSenha"
        )

        os.makedirs(self.pasta_resultados, exist_ok=True)

        self.resultados = []

        chrome = Options()
        chrome.add_argument("--start-maximized")

        self.driver = webdriver.Chrome(options=chrome)
        self.wait = WebDriverWait(self.driver, 10)

        print("✓ Ambiente preparado!")

    #########################################################

    def screenshot(self, nome):

        caminho = os.path.join(self.pasta_resultados, nome)

        self.driver.save_screenshot(caminho)

        return nome

    #########################################################

    def gerar_relatorio(self):

        sucessos = len([x for x in self.resultados if x["status"] == "Sucesso"])
        falhas = len(self.resultados) - sucessos

        html = f"""
        <!DOCTYPE html>

        <html>

        <head>

        <meta charset="UTF-8">

        <title>Teste Esqueci Senha</title>

        <style>

        body{{font-family:Arial;background:#f4f4f4;padding:20px;}}

        table{{width:100%;border-collapse:collapse;}}

        th,td{{border:1px solid #ccc;padding:10px;}}

        th{{background:#0b1a2c;color:white;}}

        .ok{{color:green;}}

        .erro{{color:red;}}

        </style>

        </head>

        <body>

        <h1>Relatório - Recuperação de Senha</h1>

        <h2>Total: {len(self.resultados)}</h2>

        <h2>Sucesso: {sucessos}</h2>

        <h2>Falhas: {falhas}</h2>

        <table>

        <tr>

        <th>ID</th>

        <th>Email</th>

        <th>Status</th>

        <th>Detalhes</th>

        <th>Print</th>

        </tr>
        """

        for r in self.resultados:

            cor = "ok" if r["status"] == "Sucesso" else "erro"

            html += f"""

            <tr>

            <td>{r["id"]}</td>

            <td>{r["email"]}</td>

            <td class="{cor}">{r["status"]}</td>

            <td>{r["detalhe"]}</td>

            <td>

            <a href="{r["print"]}" target="_blank">

            Abrir

            </a>

            </td>

            </tr>

            """

        html += "</table></body></html>"

        caminho = os.path.join(self.pasta_resultados, "dashboard.html")

        with open(caminho, "w", encoding="utf8") as f:
            f.write(html)

        return caminho

    #########################################################

    def executar(self, email):

        print("Abrindo Login...")

        self.driver.get(f"{self.url_base}/index.php")

        status = "Falha"
        detalhe = ""

        try:

            self.wait.until(
                EC.element_to_be_clickable(
                    (By.LINK_TEXT, "Esqueci minha senha")
                )
            ).click()

            self.wait.until(
                EC.presence_of_element_located(
                    (By.NAME, "email")
                )
            ).send_keys(email)

            self.screenshot("01_email_digitado.png")

            self.driver.find_element(
                By.CSS_SELECTOR,
                "button.btn-login"
            ).click()

            time.sleep(2)

            self.screenshot("02_resultado.png")

            url = self.driver.current_url

            if "sucesso" in url.lower():

                status = "Sucesso"
                detalhe = "Solicitação enviada."

            else:

                try:

                    texto = self.driver.find_element(
                        By.TAG_NAME,
                        "body"
                    ).text

                    if "link" in texto.lower():

                        status = "Sucesso"
                        detalhe = "Link gerado."

                    elif "email enviado" in texto.lower():

                        status = "Sucesso"
                        detalhe = "Email enviado."

                    elif "não encontrado" in texto.lower():

                        detalhe = "Email inexistente."

                    else:

                        detalhe = "Resposta não identificada."

                except:

                    detalhe = "Não foi possível validar."

        except Exception as e:

            detalhe = str(e)

        self.resultados.append({

            "id": 1,
            "email": email,
            "status": status,
            "detalhe": detalhe,
            "print": "02_resultado.png"

        })

        caminho = self.gerar_relatorio()

        self.driver.quit()

        print("Relatório criado!")

        webbrowser.open("file://" + os.path.realpath(caminho))


#############################################################

if __name__ == "__main__":

    URL = "http://localhost:8080/Biblioteca_InkVerse"

    EMAIL = "admin@gmail.com"

    teste = TesteEsqueciSenha(URL)

    teste.executar(EMAIL)