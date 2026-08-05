"""
TESTE AUTOMATIZADO - RECUPERAÇÃO DE SENHA
Sistema: Biblioteca InkVerse
Ferramenta: Selenium WebDriver + MySQL
"""

from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.webdriver.chrome.options import Options

import mysql.connector
import os
import time
import string
import random
import webbrowser


class TesteRecuperacaoSenha:


    def __init__(self, url_base, email):

        self.url_base = url_base.rstrip("/")
        self.email = email

        pasta = os.path.dirname(os.path.abspath(__file__))

        self.pasta_resultado = os.path.join(
            pasta,
            "TesteRecuperacaoSenha"
        )

        os.makedirs(
            self.pasta_resultado,
            exist_ok=True
        )

        chrome = Options()
        chrome.add_argument("--start-maximized")

        self.driver = webdriver.Chrome(
            options=chrome
        )

        self.wait = WebDriverWait(
            self.driver,
            15
        )

        self.resultado = {}

        print("✓ Ambiente preparado")


    # -------------------------------------------------

    def conectar_banco(self):

        return mysql.connector.connect(

            host="localhost",
            user="root",
            password="",
            database="biblioteca"

        )


    # -------------------------------------------------

    def buscar_codigo(self):

        conexao = self.conectar_banco()

        cursor = conexao.cursor()

        cursor.execute(

            """
            SELECT CodigoRecuperacao
            FROM funcionario
            WHERE Email=%s
            """,

            (self.email,)

        )


        dado = cursor.fetchone()

        conexao.close()


        if dado:

            return str(dado[0])


        return None



    # -------------------------------------------------

    def gerar_senha(self):

        return (

            random.choice(string.ascii_uppercase)

            +

            random.choice(string.ascii_lowercase)

            +

            random.choice(string.digits)

            +

            "@Ink2026"

        )



    # -------------------------------------------------

    def screenshot(self,nome):

        caminho = os.path.join(

            self.pasta_resultado,

            nome

        )

        self.driver.save_screenshot(
            caminho
        )

        return nome



    # -------------------------------------------------

    def executar(self):


        status="Falha"
        detalhe=""


        try:


            print("Abrindo login")


            self.driver.get(

                self.url_base +

                "/index.php"

            )


            # -----------------------------
            # Esqueci senha
            # -----------------------------


            self.wait.until(

                EC.element_to_be_clickable(

                    (
                    By.LINK_TEXT,
                    "Esqueci minha senha"
                    )

                )

            ).click()



            print("Tela recuperação aberta")



            # -----------------------------
            # Email
            # -----------------------------


            campo_email = self.wait.until(

                EC.presence_of_element_located(

                    (
                    By.NAME,
                    "email"
                    )

                )

            )


            campo_email.send_keys(
                self.email
            )


            self.driver.find_element(

                By.CSS_SELECTOR,

                "button.btn-login"

            ).click()



            time.sleep(2)



            # -----------------------------
            # Confirma envio
            # -----------------------------


            self.wait.until(

                EC.presence_of_element_located(

                    (
                    By.XPATH,
                    "//*[contains(text(),'Código Enviado')]"
                    )

                )

            )


            print("✓ Código enviado")



            # -----------------------------
            # Continuar
            # -----------------------------


            self.wait.until(

                EC.element_to_be_clickable(

                    (
                    By.LINK_TEXT,
                    "Continuar"
                    )

                )

            ).click()



            # -----------------------------
            # Código
            # -----------------------------


            codigo=None


            for tentativa in range(15):

                codigo=self.buscar_codigo()


                if codigo:

                    break


                time.sleep(1)



            if codigo is None:

                raise Exception(
                    "Código não encontrado no banco"
                )


            print(
                "Código encontrado:",
                codigo
            )



            campo_codigo=self.wait.until(

                EC.presence_of_element_located(

                    (
                    By.NAME,
                    "codigo"
                    )

                )

            )


            campo_codigo.send_keys(
                codigo
            )



            self.driver.find_element(

                By.CSS_SELECTOR,

                "button.btn-login"

            ).click()



            time.sleep(2)



            # -----------------------------
            # Nova senha
            # -----------------------------


            nova=self.gerar_senha()


            senha=self.wait.until(

                EC.presence_of_element_located(

                    (
                    By.NAME,
                    "nSenha"
                    )

                )

            )


            senha.send_keys(
                nova
            )


            confirmar=self.driver.find_element(

                By.NAME,

                "nConfirmarSenha"

            )


            confirmar.send_keys(
                nova
            )



            self.driver.find_element(

                By.CSS_SELECTOR,

                "button.btn-login"

            ).click()



            time.sleep(3)



            status="Sucesso"

            detalhe=(

                "Senha alterada com sucesso. "

                "Nova senha: "

                + nova

            )



        except Exception as erro:


            detalhe=str(erro)



        self.resultado={

            "email":self.email,

            "status":status,

            "detalhe":detalhe,

            "print":self.screenshot(
                "resultado.png"
            )

        }


        self.gerar_relatorio()


        self.driver.quit()



    # -------------------------------------------------

    def gerar_relatorio(self):


        caminho=os.path.join(

            self.pasta_resultado,

            "dashboard.html"

        )


        html=f"""

        <!DOCTYPE html>

        <html>

        <head>

        <meta charset="UTF-8">

        <title>
        Teste Recuperação Senha
        </title>

        </head>


        <body>


        <h1>
        Recuperação de Senha InkVerse
        </h1>


        <table border="1"
        cellpadding="10">


        <tr>

        <th>Email</th>

        <th>Status</th>

        <th>Detalhe</th>

        </tr>


        <tr>

        <td>
        {self.resultado['email']}
        </td>


        <td>
        {self.resultado['status']}
        </td>


        <td>
        {self.resultado['detalhe']}
        </td>


        </tr>


        </table>


        </body>

        </html>

        """


        with open(

            caminho,

            "w",

            encoding="utf-8"

        ) as arquivo:

            arquivo.write(html)



        webbrowser.open(

            "file://" +

            os.path.realpath(caminho)

        )




if __name__=="__main__":


    teste = TesteRecuperacaoSenha(

        url_base="http://localhost:8080/Biblioteca_InkVerse",

        email="fernanda@biblioteca.com"

    )


    teste.executar()