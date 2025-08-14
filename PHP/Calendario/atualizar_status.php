<?php
session_start();
require '../conexao.php';

header('Content-Type: application/json; charset=utf-8');

// Permite Veterinário ou Secretaria
if (!isset($_SESSION['id']) || !in_array($_SESSION['tipo_usuario'], ['Veterinario','Secretaria'])) {
    http_response_code(403);
    echo json_encode(["erro" => "Acesso negado"]);
    exit;
}

$id = isset($_POST['id']) ? (int) $_POST['id'] : null;
$status = $_POST['status'] ?? null;

$valoresPermitidos = ['confirmado', 'cancelado'];

if (!$id || !in_array($status, $valoresPermitidos, true)) {
    http_response_code(400);
    echo json_encode(["erro" => "Dados inválidos"]);
    exit;
}

$tipo = $_SESSION['tipo_usuario'];
$veterinario_id = $_SESSION['id'];

if ($status === 'confirmado') {
    // Se for confirmar, atribui o agendamento ao veterinário logado (se for Veterinário)
    if ($tipo === 'Veterinario') {
        $stmt = $pdo->prepare("
            UPDATE Agendamentos 
            SET status = ?, veterinario_id = ?
            WHERE id = ? AND (veterinario_id = ? OR veterinario_id IS NULL)
        ");
        $stmt->execute([$status, $veterinario_id, $id, $veterinario_id]);
    } else {
        // Secretaria pode confirmar qualquer agendamento sem alterar veterinario_id
        $stmt = $pdo->prepare("UPDATE Agendamentos SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
    }
} else {
    // Cancelar
    $stmt = $pdo->prepare("UPDATE Agendamentos SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
}

echo json_encode(["status" => "ok"]);
