<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Verificar Código</title>
</head>
<body>

<h2>Digite o código recebido por e-mail</h2>

<form action="validaCodigo.php" method="POST">

    <input
        type="text"
        name="codigo"
        placeholder="Código de verificação"
        required>

    <button type="submit">
        Verificar
    </button>

</form>

</body>
</html>