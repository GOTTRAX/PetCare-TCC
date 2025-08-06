<?php
include '../conexao.php';

if (isset($_POST['id'])) {
    $usuarioId = $_POST['id'];

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
?>
