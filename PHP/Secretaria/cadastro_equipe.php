<?php
include '../conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $usuario_id = $_POST['usuario_id'];
    $profissao = $_POST['profissao'];
    $descricao = $_POST['descricao'];

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $fotoTmp = $_FILES['foto']['tmp_name'];
        $fotoNome = uniqid() . '-' . basename($_FILES['foto']['name']);
        $destino = '../../assets/uploads/' . $fotoNome;

        if (move_uploaded_file($fotoTmp, $destino)) {
            $sql = "INSERT INTO equipe (nome, usuario_id, profissao, descricao, foto) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $usuario_id, $profissao, $descricao, $fotoNome]);

            echo "<script>alert('Membro cadastrado com sucesso!'); window.location.href='equipe.php';</script>";
        } else {
            echo "Erro ao fazer upload da imagem.";
        }
    } else {
        echo "Envie uma imagem válida.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        form {
    max-width: 500px;
    margin: 30px auto;
    padding: 25px 30px;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

form label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #333;
}

form input[type="text"],
form select,
form textarea,
form input[type="file"] {
    width: 100%;
    padding: 10px 12px;
    margin-bottom: 18px;
    border: 1.8px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

form input[type="text"]:focus,
form select:focus,
form textarea:focus,
form input[type="file"]:focus {
    border-color: #2c7be5;
    outline: none;
    box-shadow: 0 0 5px #2c7be5aa;
}

form textarea {
    resize: vertical;
    min-height: 90px;
}

form button {
    background-color: #2c7be5;
    color: white;
    padding: 12px 25px;
    font-size: 1.1rem;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    width: 100%;
}

form button:hover {
    background-color: #1a5dcc;
}

    </style>
    <title>Document</title>
</head>
<body>
    <form action="cadastro_equipe.php" method="POST" enctype="multipart/form-data">
    <label for="nome">Nome:</label><br>
    <input type="text" name="nome" required><br><br>

    <label for="usuario_id">Usuário (ID):</label><br>
    <select name="usuario_id" required>
        <?php
        $usuarios = $pdo->query("SELECT id, nome FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($usuarios as $usuario) {
            echo "<option value='{$usuario['id']}'>{$usuario['nome']}</option>";
        }
        ?>
    </select><br><br>

    <label for="profissao">Profissão:</label><br>
    <input type="text" name="profissao" required><br><br>

    <label for="descricao">Descrição:</label><br>
    <textarea name="descricao" rows="4" required></textarea><br><br>

    <label for="foto">Foto:</label><br>
    <input type="file" name="foto" accept="image/*" required><br><br>

    <button type="submit">Salvar</button>
</form>

</body>
</html>
