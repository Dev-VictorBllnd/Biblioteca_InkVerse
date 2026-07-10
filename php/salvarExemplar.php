<?php
include("conexao.php");

$funcao = $_GET["funcao"] ?? '';
$idExemplar = $_GET["codigo"] ?? 0;
$idLivro = $_GET["idLivro"] ?? 0;

if ($funcao == "DM") {
    // EXCLUSÃO EM MASSA — recebe um array de idExemplar
    $ids = $_POST["ids"] ?? [];
    $ok = 0;
    $bloqueados = [];

    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id < 1) { continue; }

        // Verifica ANTES se o exemplar tem histórico de empréstimo (evita o Fatal Error da FK)
        $temHist = mysqli_query($conn, "SELECT 1 FROM emprestimo_has_exemplar WHERE idExemplar = $id LIMIT 1;");
        if ($temHist && mysqli_num_rows($temHist) > 0) {
            $bloqueados[] = $id;
            continue;
        }

        // Sem histórico: pode excluir com segurança
        if (@mysqli_query($conn, "DELETE FROM exemplar WHERE idExemplar = $id;")) {
            $ok++;
        } else {
            $bloqueados[] = $id; // qualquer outro vínculo inesperado
        }
    }

    mysqli_close($conn);
    $params = "del_ok=$ok";
    if (count($bloqueados) > 0) {
        $params .= "&bloqueados=" . implode(',', $bloqueados);
    }
    header("Location: ../livros.php?$params");
    exit;
}

if ($funcao == "R") {
    // REATIVAR um exemplar individual (Ativo = 'S')
    $id = (int)($_GET["codigo"] ?? 0);
    if ($id > 0) {
        mysqli_query($conn, "UPDATE exemplar SET Ativo = 'S' WHERE idExemplar = $id;");
    }
    mysqli_close($conn);
    header("Location: ../livros.php?filtro=inativos&reativado=1");
    exit;
}

if ($funcao == "IM") {
    // INATIVAÇÃO EM MASSA — marca Ativo = 'N' nos exemplares informados
    $ids = $_POST["ids"] ?? [];
    $n = 0;
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id < 1) { continue; }
        if (mysqli_query($conn, "UPDATE exemplar SET Ativo = 'N' WHERE idExemplar = $id;")) { $n++; }
    }
    mysqli_close($conn);
    header("Location: ../livros.php?inativados=$n");
    exit;
}

if ($funcao == "E") {
    // ADICIONAR EXEMPLARES a um livro já cadastrado
    $idLivroForm = (int)($_POST["nIdLivro"] ?? 0);
    $qtd         = (int)($_POST["nQtd"] ?? 0);

    if ($idLivroForm < 1 || $qtd < 1 || $qtd > 99) {
        header("Location: ../livros.php?erro_cad=1");
        exit;
    }

    // Confere se o livro existe antes de criar as cópias
    $resLiv = mysqli_query($conn, "SELECT idLivro FROM livro WHERE idLivro = $idLivroForm LIMIT 1;");
    if (!$resLiv || mysqli_num_rows($resLiv) == 0) {
        header("Location: ../livros.php?erro_cad=1");
        exit;
    }

    for ($i = 0; $i < $qtd; $i++) {
        mysqli_query($conn, "INSERT INTO exemplar (idLivro, Emprestado, Ativo) VALUES ($idLivroForm, 'nao', 'S');");
    }

    mysqli_close($conn);
    header("Location: ../livros.php?sucesso_cad=1");
    exit;
}

if ($funcao == "A") {
    $idLivroForm = $_POST["nIdLivro"];
    $titulo = mysqli_real_escape_string($conn, $_POST["nTitulo"]);
    $autor = mysqli_real_escape_string($conn, $_POST["nAutor"]);
    $genero = (int)$_POST["nGenero"];
    $editora = (int)$_POST["nEditora"];
    $ano = (int)$_POST["nAno"];
    $isbn = mysqli_real_escape_string($conn, $_POST["nIsbn"]);

    // Atualiza todos os exemplares desta obra
    $sql = "UPDATE livro SET 
                Titulo = '$titulo', 
                Autor = '$autor', 
                idGenero = $genero, 
                idEditora = $editora, 
                Isbn = '$isbn', 
                ano = $ano 
            WHERE idLivro = $idLivroForm;";
            
    if (mysqli_query($conn, $sql)) {

        // Upload da nova capa (opcional) — só atualiza se o arquivo for enviado
        if (isset($_FILES['Capa']) && $_FILES['Capa']['tmp_name'] != '') {
            $ext      = pathinfo($_FILES['Capa']['name'], PATHINFO_EXTENSION);
            $novoNome = 'capa-'.$idLivroForm.'-'.time().'.'.$ext;

            if (!is_dir('../dist/img/livros/')) {
                mkdir('../dist/img/livros/', 0777, true);
            }

            if (move_uploaded_file($_FILES['Capa']['tmp_name'], '../dist/img/livros/'.$novoNome)) {
                $dirImagem = 'dist/img/livros/'.$novoNome;
                mysqli_query($conn, "UPDATE livro SET Foto = '$dirImagem' WHERE idLivro = $idLivroForm;");
            }
        }

        header("Location: ../livros.php?sucesso_edit=1");
    } else {
        echo "Erro ao atualizar dados da obra: " . mysqli_error($conn);
    }

} elseif ($funcao == "D") {
    // Tenta Excluir Permanentemente o Exemplar
    $sql = "DELETE FROM exemplar WHERE idExemplar = $idExemplar;";
    
    try {
        $result = mysqli_query($conn, $sql);
        
        if ($result) {
            header("Location: ../livros.php?sucesso_del=1");
        } else {
            // Falha por ForeignKey silenciosa
            header("Location: ../livros.php?erro_excluir=1&idExemplar=$idExemplar&idLivro=$idLivro");
        }
    } catch (Exception $e) {
        // Falha por Exception
        header("Location: ../livros.php?erro_excluir=1&idExemplar=$idExemplar&idLivro=$idLivro");
    }

} elseif ($funcao == "I") {
    // INATIVAÇÃO DO EXEMPLAR ESPECÍFICO
    $idExemplarInativar = $_GET["codigo"] ?? 0;
    
    // Altera a flag para 'N' apenas nesta cópia física!
    $sql = "UPDATE exemplar SET Ativo = 'N' WHERE idExemplar = $idExemplarInativar;";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: ../livros.php?sucesso_inativar=1");
    } else {
        echo "Erro ao inativar exemplar: " . mysqli_error($conn);
    }
}

mysqli_close($conn);
?>