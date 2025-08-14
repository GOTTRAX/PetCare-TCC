<?php
session_start();
require '../conexao.php';

header('Content-Type: application/json; charset=utf-8');

$usuario_id = $_SESSION['id'] ?? null;
$tipo = $_SESSION['tipo_usuario'] ?? null;

if (!$usuario_id || !$tipo) {
    http_response_code(403);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

try {
    if ($tipo === 'Cliente') {
        $sql = "
            SELECT a.id, a.data_hora, a.hora_inicio, a.hora_final, a.observacoes, a.status,
                   an.nome AS animal_nome, e.nome AS vet_nome
            FROM Agendamentos a
            LEFT JOIN Animais an ON a.animal_id = an.id
            LEFT JOIN Equipe e  ON a.veterinario_id = e.id
            WHERE a.cliente_id = ? AND a.status = 'confirmado'
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$usuario_id]);

    } elseif ($tipo === 'Veterinario') {
        $sql = "
            SELECT a.id, a.data_hora, a.hora_inicio, a.hora_final, a.observacoes, a.status,
                   an.nome AS animal_nome, e.nome AS vet_nome
            FROM Agendamentos a
            LEFT JOIN Animais an ON a.animal_id = an.id
            LEFT JOIN Equipe e  ON a.veterinario_id = e.id
            WHERE a.status IN ('pendente', 'confirmado')
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

    } elseif ($tipo === 'Secretaria' || $tipo === 'Cuidador') {
        $sql = "
            SELECT a.id, a.data_hora, a.hora_inicio, a.hora_final, a.observacoes, a.status,
                   an.nome AS animal_nome, e.nome AS vet_nome
            FROM Agendamentos a
            LEFT JOIN Animais an ON a.animal_id = an.id
            LEFT JOIN Equipe e  ON a.veterinario_id = e.id
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();

    } else {
        http_response_code(403);
        echo json_encode(['error' => 'Tipo de usuário inválido']);
        exit;
    }

    $eventos = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $date = $row['data_hora'];
        $start_time = $row['hora_inicio'];
        $end_time = $row['hora_final'];

        $start = null;
        $end = null;

        if ($date && $start_time) {
            $dtStart = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $start_time)
                       ?: DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $start_time)
                       ?: DateTime::createFromFormat('Y-m-d', $date);
            $start = $dtStart ? $dtStart->format('c') : $date . 'T' . $start_time;
        }

        if ($date && $end_time) {
            $dtEnd = DateTime::createFromFormat('Y-m-d H:i', $date . ' ' . $end_time)
                     ?: DateTime::createFromFormat('Y-m-d H:i:s', $date . ' ' . $end_time)
                     ?: DateTime::createFromFormat('Y-m-d', $date);
            $end = $dtEnd ? $dtEnd->format('c') : $date . 'T' . $end_time;
        }

        $title = 'Agendamento';
        if (!empty($row['observacoes'])) {
            $title = mb_strimwidth($row['observacoes'], 0, 80, '...');
        } else {
            $parts = [];
            if (!empty($row['animal_nome'])) $parts[] = $row['animal_nome'];
            if (!empty($row['vet_nome'])) $parts[] = 'Vet: ' . $row['vet_nome'];
            if ($parts) $title = implode(' - ', $parts);
        }

        if (!$start) continue;

        $eventos[] = [
            'id'     => $row['id'],
            'title'  => $title,
            'start'  => $start,
            'end'    => $end,
            'allDay' => false,
            'status' => $row['status'] 
        ];
    }

    echo json_encode($eventos);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no banco de dados: ' . $e->getMessage()]);
    exit;
}
