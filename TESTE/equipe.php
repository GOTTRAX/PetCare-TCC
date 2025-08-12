<?php
session_start();
include '../PHP/conexao.php'; // Conexão com o banco

/* ===============================
   ATUALIZAR USUÁRIO (POST)
   =============================== */
if (isset($_POST['acao']) && $_POST['acao'] === 'atualizar_usuario') {
    if (!isset($_SESSION["id"]) || $_SESSION["tipo_usuario"] !== "Secretaria") {
        header("Location: ../index.php");
        exit();
    }

    $id = $_POST['id'] ?? null;
    $tipo = $_POST['tipo_usuario'] ?? null;
    $ativo = $_POST['ativo'] ?? null;

    if ($id && $tipo && $ativo !== null) {
        $sql = "UPDATE usuarios 
                SET tipo_usuario = :tipo, ativo = :ativo, atualizado_em = NOW() 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':tipo' => $tipo,
            ':ativo' => $ativo,
            ':id' => $id
        ]);
    }
    header("Location: sec_home.php");
    exit();
}

/* ===============================
   DELETAR USUÁRIO (POST)
   =============================== */
if (isset($_POST['acao']) && $_POST['acao'] === 'deletar_usuario') {
    $usuarioId = $_POST['id'] ?? null;

    if ($usuarioId) {
        try {
            $sqlAnimais = "DELETE FROM animais WHERE usuario_id = ?";
            $stmtAnimais = $pdo->prepare($sqlAnimais);
            $stmtAnimais->execute([$usuarioId]);

            $sqlUsuario = "DELETE FROM usuarios WHERE id = ?";
            $stmtUsuario = $pdo->prepare($sqlUsuario);
            $stmtUsuario->execute([$usuarioId]);

            echo "Usuário e seus animais deletados com sucesso!";
        } catch (PDOException $e) {
            echo "Erro ao deletar: " . $e->getMessage();
        }
    } else {
        echo "ID do usuário não informado.";
    }
    exit();
}

/* ===============================
   LISTAR EQUIPE (PÁGINA HTML)
   =============================== */
try {
    $sql = "SELECT nome, profissao, descricao, foto FROM equipe";
    $stmt = $pdo->query($sql);
    $equipe = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Erro ao buscar equipe: " . $e->getMessage();
    $equipe = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipe</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../CSS/styles.css">
    <link rel="stylesheet" href="../CSS/import.css">
</head>
<body>
    <br><br>
    <div class="team-container mt-5">
        <h1 class="text-center mb-4">Nossa Equipe</h1>
        <div class="team-row row justify-content-center">
            <?php if (count($equipe) > 0): ?>
                <?php foreach ($equipe as $row): ?>
                    <div class="team-member col-md-4">
                        <div class="team-card">
                            <img src="../../assets/uploads/<?= htmlspecialchars($row["foto"]) ?>" 
                                 class="team-img" 
                                 alt="Foto de <?= htmlspecialchars($row["nome"]) ?>">
                            <div class="team-body">
                                <h5 class="team-title"><?= htmlspecialchars($row["nome"]) ?></h5>
                                <p class="team-subtitle text-muted"><?= htmlspecialchars($row["profissao"]) ?></p>
                                <p class="team-text"><?= htmlspecialchars($row["descricao"]) ?></p>
                                <div class="team-social-icons">
                                    <a href="#"><i class="fab fa-facebook"></i></a>
                                    <a href="#"><i class="fab fa-instagram"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">Nenhum membro cadastrado ainda.</p>
            <?php endif; ?>
        </div>
    </div>

    <br><br><br><br><br><br>
    
</body>
</html>
