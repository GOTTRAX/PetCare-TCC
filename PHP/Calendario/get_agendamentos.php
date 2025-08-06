<?php
session_start();
require 'conexao.php';

$usuario_id = $_SESSION['id'] ?? null;
$tipo = $_SESSION['tipo_usuario'] ?? null;

if (!$usuario_id || !$tipo) {
    http_response_code(403);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    if ($tipo === 'Cliente') {
        $stmt = $pdo->prepare("
            SELECT id, data_hora, hora_inicio, hora_final, observacoes
            FROM Agendamentos
            WHERE cliente_id = ?
        ");
        $stmt->execute([$usuario_id]);

    } else if ($tipo === 'Veterinario') {
        $stmt = $pdo->prepare("
            SELECT id, data_hora, hora_inicio, hora_final, observacoes
            FROM Agendamentos
            WHERE veterinario_id = ?
        ");
        $stmt->execute([$usuario_id]);

    } else if ($tipo === 'Secretaria' || $tipo === 'Cuidador') {
        $stmt = $pdo->query("
            SELECT id, data_hora, hora_inicio, hora_final, observacoes
            FROM Agendamentos
        ");
    } else {
        throw new Exception("Tipo de usuário inválido.");
    }

    $eventos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $eventos[] = [
            'id'    => $row['id'],
            'title' => $row['observacoes'] ?? 'Agendamento',
            'start' => $row['data_hora'] . 'T' . $row['hora_inicio'],
            'end'   => $row['data_hora'] . 'T' . $row['hora_final'],
        ];
    }

    echo json_encode($eventos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => $e->getMessage()]);
}
