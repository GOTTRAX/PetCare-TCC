<?php
include '../conexao.php';

// Consulta para retornar todas as espécies e contar quantos animais há por espécie (mesmo que seja zero)
$query = "
    SELECT e.nome AS especie_nome, COUNT(a.id) AS quantidade
    FROM Especies e
    LEFT JOIN Animais a ON a.especie_id = e.id
    GROUP BY e.nome
";

try {
    $stmt = $pdo->query($query);
    $especies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($especies);
} catch (PDOException $e) {
    echo json_encode(['erro' => 'Erro na consulta: ' . $e->getMessage()]);
}
?>
