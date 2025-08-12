<?php
session_start();
require '../conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'Veterinario') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

$veterinario_id = $_SESSION['id'];

$stmt = $pdo->prepare("
    SELECT a.id, an.nome AS animal_nome, a.data_hora, a.hora_inicio, a.observacoes
    FROM Agendamentos a
    JOIN Animais an ON a.animal_id = an.id
    WHERE a.veterinario_id = ? AND a.status = 'pendente'
    ORDER BY a.data_hora ASC
");
$stmt->execute([$veterinario_id]);

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
