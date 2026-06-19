sql = """
                SELECT 
                    e.idExemplar, l.Titulo, l.Autor, l.Genero, l.Editora, l.Isbn, l.ano, e.Emprestado, l.idLivro
                FROM exemplar e
                INNER JOIN livro l ON e.idLivro = l.idLivro
                WHERE l.Titulo LIKE %s
                ORDER BY l.Titulo, e.idExemplar
            """