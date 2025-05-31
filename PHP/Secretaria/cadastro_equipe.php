<?php
// Inclui o arquivo de conexão
include '../conexao.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $profissao = $_POST['profissao'];
    $descricao = $_POST['descricao'];

    // Verifica se um arquivo foi enviado
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
        $fotoTmp = $_FILES['foto']['tmp_name'];
        $fotoNome = uniqid() . '-' . basename($_FILES['foto']['name']);
        $destino = '../../assets/uploads/' . $fotoNome;

        // Move o arquivo para a pasta uploads
        if (move_uploaded_file($fotoTmp, $destino)) {
            // Insere no banco de dados
            $sql = "INSERT INTO equipe (nome, profissao, descricao, foto) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $nome, $profissao, $descricao, $fotoNome);

            if ($stmt->execute()) {
                echo "<script>alert('Membro cadastrado com sucesso!'); window.location.href='equipe.php';</script>";
            } else {
                echo "Erro ao salvar no banco de dados: " . $conn->error;
            }

            $stmt->close();
        } else {
            echo "Erro ao fazer upload da imagem.";
        }
    } else {
        echo "Envie uma imagem válida.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Membro da Equipe</title>
    <link rel="stylesheet" href="../../CSS/styles.css">
</head>
<body>
    <div class="form-container">
        <h2>Cadastrar Membro da Equipe</h2>
        <form action="cadastro_equipe.php" method="POST" enctype="multipart/form-data">
            <label for="nome">Nome:</label><br>
            <input type="text" name="nome" required><br><br>

            <label for="profissao">Profissão:</label><br>
            <input type="text" name="profissao" required><br><br>

            <label for="descricao">Descrição:</label><br>
            <textarea name="descricao" rows="4" required></textarea><br><br>

            <label for="foto">Foto:</label><br>
            <input type="file" name="foto" accept="image/*" required><br><br>

            <button type="submit">Salvar</button>
        </form>
        <br>
        <a href="equipe.php"><button>Voltar para Equipe</button></a>
    </div>
</body>
</html>
