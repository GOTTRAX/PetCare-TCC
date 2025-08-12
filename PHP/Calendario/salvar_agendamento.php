<?php
session_start();
require '../conexao.php';

$cliente_id = $_SESSION['id'] ?? null;

if (!$cliente_id) {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = $_POST['data_hora'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_final = $_POST['hora_final']; // já vem do JS
    $veterinario_id = $_POST['veterinario_id'];
    $animal_id = $_POST['animal_id'];
    $obs = $_POST['observacoes'];

    $stmt = $pdo->prepare("
        INSERT INTO Agendamentos 
        (cliente_id, animal_id, veterinario_id, data_hora, hora_inicio, hora_final, observacoes)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $cliente_id,
        $animal_id,
        $veterinario_id,
        $data,
        $hora_inicio,
        $hora_final,
        $obs
    ]);

    header("Location: calendario.php");
    exit;
}
