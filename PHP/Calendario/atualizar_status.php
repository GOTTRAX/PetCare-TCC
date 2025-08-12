<?php
session_start();
require '../conexao.php';

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'Veterinario') {
    http_response_code(403);
    exit("Acesso negado");
}

$id = $_POST['id'] ?? null;
$status = $_POST['status'] ?? null;

if (!$id || !in_array($status, ['aceito', 'recusado'])) {
    http_response_code(400);
    exit("Dados inválidos");
}

$stmt = $pdo->prepare("UPDATE Agendamentos SET status = ? WHERE id = ? AND veterinario_id = ?");
$stmt->execute([$status, $id, $_SESSION['id']]);

echo "ok";
