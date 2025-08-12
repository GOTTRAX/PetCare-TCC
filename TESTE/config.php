<?php
// config.php

// === Conexão PDO ===
$host = "localhost";
$dbname = "PetCare"; // ajuste para seu banco
$user = "root";
$pass = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// === Salvar configurações ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $diasSemana = ['Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo'];

    foreach ($diasSemana as $dia) {
        $key = strtolower($dia);
        $ativo = isset($_POST['dia_' . $key]) ? 1 : 0;
        $abertura = $_POST['abertura_' . $key] ?? '08:00:00';
        $fechamento = $_POST['fechamento_' . $key] ?? '18:00:00';

        // Upsert (insert or update)
        $stmt = $pdo->prepare("
            INSERT INTO Dias_Trabalhados (dia_semana, horario_abertura, horario_fechamento, ativo)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE horario_abertura = VALUES(horario_abertura), horario_fechamento = VALUES(horario_fechamento), ativo = VALUES(ativo)
        ");
        $stmt->execute([$dia, $abertura, $fechamento, $ativo]);
    }

    // Salvar preços dos serviços
    if (isset($_POST['servicos']) && is_array($_POST['servicos'])) {
        foreach ($_POST['servicos'] as $servico_id => $preco) {
            // Limpar preço pra evitar SQL injection e formatos inválidos
            $preco = str_replace(',', '.', $preco);
            $preco = floatval($preco);

            $stmt = $pdo->prepare("UPDATE Servicos SET preco_normal = ? WHERE id = ?");
            $stmt->execute([$preco, $servico_id]);
        }
    }

    echo "<p style='color:green;'>Configurações salvas com sucesso!</p>";
}

// === Carregar dados ===
$dias = [];
$results = $pdo->query("SELECT * FROM Dias_Trabalhados")->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $row) {
    $dias[strtolower($row['dia_semana'])] = $row;
}

$servicos = $pdo->query("SELECT * FROM Servicos")->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Configurações da Clínica</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        /* Estilos básicos só pra melhorar visualização */
        .settings-grid { display: flex; gap: 20px; flex-wrap: wrap; }
        .setting-card { background: #f9f9f9; padding: 15px; border-radius: 6px; flex: 1 1 300px; }
        .days-selector { display: flex; flex-wrap: wrap; gap: 10px; }
        .day-label { margin-right: 15px; }
        .form-group { margin-bottom: 10px; }
        .form-label { display: block; margin-bottom: 4px; }
        input[type="text"], input[type="time"], input[type="number"] { width: 100%; padding: 6px; box-sizing: border-box; }
        .btn-primary { background-color: #007bff; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px; }
        .btn-primary:hover { background-color: #0056b3; }
    </style>
</head>
<body>

<h2>Configurações da Clínica</h2>

<form method="post">

    <div class="tab-content" id="configuracoes-tab">
        <div class="settings-grid">
            <div class="setting-card">
                <h4><i class="fas fa-calendar-alt"></i> Dias de Trabalho</h4>
                <div class="days-selector">
                    <?php 
                    $diasSemana = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];
                    $diasNomes = ['segunda'=>'Segunda', 'terca'=>'Terça', 'quarta'=>'Quarta', 'quinta'=>'Quinta', 'sexta'=>'Sexta', 'sabado'=>'Sábado', 'domingo'=>'Domingo'];
                    foreach ($diasSemana as $dia): 
                        $checked = (isset($dias[$dia]) && $dias[$dia]['ativo']) ? 'checked' : '';
                    ?>
                        <input type="checkbox" id="<?= $dia ?>" class="day-checkbox" name="dia_<?= $dia ?>" <?= $checked ?>>
                        <label for="<?= $dia ?>" class="day-label"><?= $diasNomes[$dia] ?></label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="setting-card">
                <h4><i class="fas fa-clock"></i> Horário de Trabalho</h4>
                <?php foreach ($diasSemana as $dia): 
                    $abertura = $dias[$dia]['horario_abertura'] ?? '08:00:00';
                    $fechamento = $dias[$dia]['horario_fechamento'] ?? '18:00:00';
                ?>
                <div class="form-group">
                    <label class="form-label">Horário de Abertura - <?= $diasNomes[$dia] ?></label>
                    <input type="time" class="form-control" name="abertura_<?= $dia ?>" value="<?= substr($abertura,0,5) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Horário de Fechamento - <?= $diasNomes[$dia] ?></label>
                    <input type="time" class="form-control" name="fechamento_<?= $dia ?>" value="<?= substr($fechamento,0,5) ?>">
                </div>
                <?php endforeach; ?>
                <div class="form-group">
                    <label class="form-label">Duração das Consultas (minutos)</label>
                    <input type="number" class="form-control" name="duracao_consulta" value="30" disabled>
                    <!-- Pode adaptar para salvar essa duração em outra tabela ou campo -->
                </div>
            </div>

            <div class="setting-card">
                <h4><i class="fas fa-dollar-sign"></i> Valores dos Serviços</h4>
                <?php foreach ($servicos as $servico): ?>
                <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($servico['nome']) ?></label>
                    <input type="text" class="form-control" name="servicos[<?= $servico['id'] ?>]" value="<?= number_format($servico['preco_normal'], 2, ',', '.') ?>">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="setting-card">
                <h4><i class="fas fa-bell"></i> Notificações</h4>
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center;">
                        <input type="checkbox" style="margin-right: 10px;" checked disabled>
                        Lembretes de Consultas
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center;">
                        <input type="checkbox" style="margin-right: 10px;" checked disabled>
                        Notificações de Novos Agendamentos
                    </label>
                </div>
                <div class="form-group">
                    <label class="form-label" style="display: flex; align-items: center;">
                        <input type="checkbox" style="margin-right: 10px;" >
                        Promoções e Novidades
                    </label>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Salvar Configurações
            </button>
        </div>
    </div>

</form>

</body>
</html>
