<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit;
}

require '../conexao.php';

$usuario_id = $_SESSION['id'];
$tipo = $_SESSION['tipo_usuario'];

$animais = [];
if ($tipo === 'Cliente') {
    $stmt = $pdo->prepare("SELECT id, nome FROM Animais WHERE usuario_id = ?");
    $stmt->execute([$usuario_id]);
    $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$veterinarios = [];
$stmt = $pdo->prepare("SELECT id, nome FROM Equipe WHERE profissao = :profissao");
$stmt->execute([':profissao' => 'Vet']);
$veterinarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Calendário</title>
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f2f2f2;
            display: flex;
            gap: 40px;
        }

        #calendar {
            flex: 2;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        #formulario-agendamento,
        #solicitacoes {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.1);
        }

        .solicitacao {
            border-bottom: 1px solid #ccc;
            padding: 10px 0;
        }

        form label {
            font-weight: bold;
        }

        form input,
        form select,
        form textarea,
        form button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form button {
            background-color: #2e8b57;
            color: white;
            cursor: pointer;
        }

        form button:hover {
            background-color: #276747;
        }

        button.aceitar {
            background-color: #2e8b57;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        button.recusar {
            background-color: #b22222;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <div id='calendar'></div>

    <?php if ($tipo === 'Cliente'): ?>
        <div id="formulario-agendamento">
            <h3>Agendar Consulta</h3>
            <form action="salvar_agendamento.php" method="POST">
                <label for="data_hora">Data da Consulta:</label>
                <input type="date" name="data_hora" id="data_hora" readonly required>

                <label for="hora_inicio">Horário de Início:</label>
                <select name="hora_inicio" id="hora_inicio" required onchange="definirHoraFinal(this.value)">
                    <option value="">Selecione o horário</option>
                    <?php
                    for ($h = 9; $h <= 17; $h++) {
                        $hora = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
                        echo "<option value=\"$hora\">$hora</option>";
                    }
                    ?>
                </select>
                <input type="hidden" name="hora_final" id="hora_final">

                <label for="veterinario_id">Selecione o Veterinário:</label>
                <select name="veterinario_id" id="veterinario_id" required>
                    <option value="">Selecione o veterinário</option>
                    <?php foreach ($veterinarios as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="animal_id">Selecione o Animal:</label>
                <select name="animal_id" id="animal_id" required>
                    <option value="">Selecione o animal</option>
                    <?php foreach ($animais as $a): ?>
                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="observacoes">Observações (opcional):</label>
                <textarea name="observacoes" id="observacoes" rows="3"></textarea>

                <button type="submit">Agendar</button>
            </form>
        </div>

    <?php elseif ($tipo === 'Veterinario' || $tipo === 'Secretaria'): ?>
        <div id="solicitacoes">
            <h3>Solicitações Pendentes</h3>
            <div id="lista-solicitacoes">Carregando...</div>
        </div>
    <?php endif; ?>

    <script>
        function definirHoraFinal(hora) {
            if (hora) {
                const [h, m] = hora.split(':');
                const novaHora = String(parseInt(h) + 1).padStart(2, '0') + ':' + m;
                document.getElementById('hora_final').value = novaHora;
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                initialView: 'dayGridMonth',
                locale: 'pt-br',
                events: 'get_agendamentos.php',
                <?php if ($tipo === 'Cliente'): ?>
            dateClick: function (info) {
                        document.getElementById('formulario-agendamento').style.display = 'block';
                        document.getElementById('data_hora').value = info.dateStr;
                    }
        <?php endif; ?>
            });
            calendar.render();

            <?php if ($tipo === 'Veterinario' || $tipo === 'Secretaria'): ?>
                function carregarSolicitacoes() {
                    fetch('get_solicitacoes.php')
                        .then(res => res.json())
                        .then(data => {
                            const container = document.getElementById('lista-solicitacoes');
                            container.innerHTML = '';
                            if (data.length === 0) {
                                container.innerHTML = '<p>Sem solicitações pendentes.</p>';
                                return;
                            }
                            data.forEach(s => {
                                const div = document.createElement('div');
                                div.classList.add('solicitacao');
                                div.innerHTML = `
                        <strong>${s.animal_nome}</strong> - ${s.data_hora} ${s.hora_inicio}<br>
                        ${s.observacoes || ''}<br>
                        <button class="aceitar" data-id="${s.id}">Aceitar</button>
                        <button class="recusar" data-id="${s.id}">Recusar</button>
                    `;
                                container.appendChild(div);
                            });
                        });
                }

                // Captura clique dos botões e envia status correto
                document.addEventListener("click", async (e) => {
                    if (e.target.classList.contains("aceitar") || e.target.classList.contains("recusar")) {
                        const id = e.target.dataset.id;
                        const status = e.target.classList.contains("aceitar") ? "confirmado" : "cancelado";

                        try {
                            const resposta = await fetch("atualizar_status.php", {
                                method: "POST",
                                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                                body: `id=${encodeURIComponent(id)}&status=${encodeURIComponent(status)}`,
                                credentials: "same-origin" // ESSENCIAL para enviar cookies de sessão
                            });

                            const dados = await resposta.json();

                            if (dados.status === "ok") {
                                alert(`Agendamento ${status} com sucesso!`);
                                carregarSolicitacoes();
                                calendar.refetchEvents();
                            } else {
                                alert(dados.erro || "Erro ao atualizar agendamento.");
                            }
                        } catch (erro) {
                            console.error("Erro na requisição:", erro);
                            alert("Ocorreu um erro na conexão.");
                        }
                    }
                });


                carregarSolicitacoes();
            <?php endif; ?>
        });
    </script>

</body>

</html>