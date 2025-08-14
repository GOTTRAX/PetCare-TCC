<?php
session_start();
require '../conexao.php';

// Garantir que o usuário está logado
if (!isset($_SESSION['id'])) {
    http_response_code(403);
    echo json_encode(["erro" => "Usuário não autenticado"]);
    exit;
}

$tipo = $_SESSION['tipo_usuario'];
$usuario_id = $_SESSION['id'];

// Se não for Veterinário nem Secretaria, bloqueia
if ($tipo !== 'Veterinario' && $tipo !== 'Secretaria') {
    http_response_code(403);
    echo json_encode(["erro" => "Acesso negado"]);
    exit;
}

// Monta a query de acordo com o tipo
if ($tipo === 'Veterinario') {
    // Apenas solicitações para este veterinário ou sem veterinário definido
    $sql = "
        SELECT 
            a.id,
            a.veterinario_id,
            a.status,
            an.nome AS animal_nome,
            a.data_hora,
            a.hora_inicio,
            a.observacoes
        FROM Agendamentos a
        INNER JOIN Animais an ON a.animal_id = an.id
        WHERE a.status = 'pendente'
          AND (a.veterinario_id = :vet_id OR a.veterinario_id IS NULL)
        ORDER BY a.data_hora ASC, a.hora_inicio ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['vet_id' => $usuario_id]);

} elseif ($tipo === 'Secretaria') {
    // Todas as solicitações pendentes
    $sql = "
        SELECT 
            a.id,
            a.veterinario_id,
            a.status,
            an.nome AS animal_nome,
            a.data_hora,
            a.hora_inicio,
            a.observacoes
        FROM Agendamentos a
        INNER JOIN Animais an ON a.animal_id = an.id
        WHERE a.status = 'pendente'
        ORDER BY a.data_hora ASC, a.hora_inicio ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

// Retorna JSON para o JavaScript
header('Content-Type: application/json; charset=utf-8');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
