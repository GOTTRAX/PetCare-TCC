<?php
include '../conexao.php'; // Certifique-se que este arquivo define corretamente a variável $pdo (instância de PDO)

try {
    $sql = "SELECT nome, profissao, descricao, foto FROM equipe";
    $stmt = $pdo->query($sql); // Executa a query
    $equipe = $stmt->fetchAll(PDO::FETCH_ASSOC); // Pega todos os resultados como array associativo
} catch (PDOException $e) {
    echo "Erro ao buscar equipe: " . $e->getMessage();
    $equipe = []; // Garante que $equipe esteja definido
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../../CSS/styles.css">
    <title>Equipe</title>
</head>
<body>
    <br><br>
    <div class="team-container mt-5">
        <h1 class="text-center mb-4">Nossa Equipe</h1>
        <div class="team-row row justify-content-center">

        <?php
$sql = "SELECT nome, profissao, descricao, foto FROM equipe";
$stmt = $pdo->query($sql); // já executa a query diretamente
$equipe = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($equipe) > 0) {
    foreach ($equipe as $row) {
        echo '
        <div class="team-member col-md-4">
            <div class="team-card">
                <img src="../../assets/uploads/' . htmlspecialchars($row["foto"]) . '" class="team-img" alt="Foto de ' . htmlspecialchars($row["nome"]) . '">
                <div class="team-body">
                    <h5 class="team-title">' . htmlspecialchars($row["nome"]) . '</h5>
                    <p class="team-subtitle text-muted">' . htmlspecialchars($row["profissao"]) . '</p>
                    <p class="team-text">' . htmlspecialchars($row["descricao"]) . '</p>
                    <div class="team-social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>';
    }
} else {
    echo '<p class="text-center">Nenhum membro cadastrado ainda.</p>';
}
?>


        </div>
    </div>

    <br><br><br><br><br><br>
    <?php include '../menu.php'; ?>
    <?php include '../footer.html'; ?>
    <script src="../../JS/script.js" defer></script>
</body>
</html>
