<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["tipo_usuario"] !== "Secretaria") {
    header("Location: ../index.php");
    exit();
}

include '../conexao.php';

$sql = "SELECT * FROM Usuarios";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

function formatarData($data) {
    if ($data && $data !== "0000-00-00") {
        return date("d-m-Y", strtotime($data));
    }
    return "-";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Secretária | Sistema Veterinário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3f37c9;
            --accent-color: #4895ef;
            --danger-color: #f72585;
            --success-color: #4cc9f0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --gray-color: #6c757d;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: var(--dark-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 95%;
            margin: 0 auto;
            padding: 20px;
            background-color: white;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--accent-color);
        }

        .header h1 {
            color: var(--secondary-color);
            font-size: 28px;
            font-weight: 600;
        }

        .btn-group {
            display: flex;
            gap: 15px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            transition: var(--transition);
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-success {
            background-color: var(--success-color);
            color: white;
        }

        .btn-success:hover {
            background-color: #3aa8d8;
            transform: translateY(-2px);
        }

        .table-container {
            overflow-x: auto;
            margin-top: 20px;
            border-radius: 10px;
            box-shadow: var(--shadow);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        tr:hover {
            background-color: #f1f3ff;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 6px;
        }

        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }

        .btn-danger:hover {
            background-color: #e5177e;
            transform: translateY(-2px);
        }

        select {
            padding: 8px 12px;
            border-radius: 6px;
            border: 1px solid #ced4da;
            background-color: white;
            font-size: 14px;
            transition: var(--transition);
        }

        select:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(72, 149, 239, 0.25);
        }

        .status-active {
            color: #28a745;
            font-weight: 500;
        }

        .status-inactive {
            color: var(--danger-color);
            font-weight: 500;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-client {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .badge-secretary {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .badge-vet {
            background-color: #fff3e0;
            color: #e65100;
        }

        @media (max-width: 1200px) {
            .table-container {
                display: block;
                width: 100%;
                overflow-x: auto;
            }
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--gray-color);
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-users-cog"></i> Gerenciamento de Usuários</h1>
            <div class="btn-group">
                <a href="equipe.php" class="btn btn-primary">
                    <i class="fas fa-users"></i> Ver Equipe
                </a>
                <a href="cadastro_equipe.php" class="btn btn-success">
                    <i class="fas fa-user-plus"></i> Cadastrar Membro
                </a>
            </div>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Telefone</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Gênero</th>
                        <th>Tentativas</th>
                        <th>Data Nasc.</th>
                        <th>Bloqueado Até</th>
                        <th>Último Login</th>
                        <th>Ativo</th>
                        <th>Criado</th>
                        <th>Atualizado</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($usuarios)): ?>
                        <tr>
                            <td colspan="15" class="no-data">Nenhum usuário cadastrado</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <form method="POST" action="atualizar_usuario.php">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                    <td><?= htmlspecialchars($usuario['cpf']) ?></td>
                                    <td><?= htmlspecialchars($usuario['telefone']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email']) ?></td>

                                    <td>
                                        <select name="tipo_usuario" class="user-type">
                                            <option value="Cliente" <?= $usuario['tipo_usuario'] == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                                            <option value="Secretaria" <?= $usuario['tipo_usuario'] == 'Secretaria' ? 'selected' : '' ?>>Secretária</option>
                                            <option value="Veterinario" <?= $usuario['tipo_usuario'] == 'Veterinario' ? 'selected' : '' ?>>Veterinário</option>
                                        </select>
                                    </td>

                                    <td><?= htmlspecialchars($usuario['genero']) ?></td>
                                    <td><?= htmlspecialchars($usuario['tentativas']) ?></td>
                                    <td><?= formatarData($usuario['datanasc']) ?></td>
                                    <td><?= formatarData($usuario['bloqueado_ate']) ?></td>
                                    <td><?= formatarData($usuario['ultimo_login']) ?></td>

                                    <td>
                                        <select name="ativo">
                                            <option value="1" <?= $usuario['ativo'] ? 'selected' : '' ?>>Sim</option>
                                            <option value="0" <?= !$usuario['ativo'] ? 'selected' : '' ?>>Não</option>
                                        </select>
                                    </td>

                                    <td><?= formatarData($usuario['criado']) ?></td>
                                    <td><?= formatarData($usuario['atualizado_em']) ?></td>

                                    <td class="actions">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-save"></i> Salvar
                                        </button>
                                </form>
                                <form method="POST" action="deletar_usuario.php" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                                    <input type="hidden" name="id" value="<?= htmlspecialchars($usuario['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fas fa-trash-alt"></i> Excluir
                                    </button>
                                </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Adiciona classes de badge baseadas no tipo de usuário
        document.addEventListener('DOMContentLoaded', function() {
            const userTypeSelects = document.querySelectorAll('.user-type');
            
            userTypeSelects.forEach(select => {
                // Atualiza a classe quando a página carrega
                updateBadgeClass(select);
                
                // Atualiza a classe quando o valor muda
                select.addEventListener('change', function() {
                    updateBadgeClass(this);
                });
            });

            function updateBadgeClass(selectElement) {
                const selectedValue = selectElement.value;
                const parentTd = selectElement.parentElement;
                
                // Remove todas as classes de badge
                parentTd.classList.remove('badge-client', 'badge-secretary', 'badge-vet');
                
                // Adiciona a classe apropriada
                if (selectedValue === 'Cliente') {
                    parentTd.classList.add('badge-client');
                } else if (selectedValue === 'Secretaria') {
                    parentTd.classList.add('badge-secretary');
                } else if (selectedValue === 'Veterinario') {
                    parentTd.classList.add('badge-vet');
                }
            }
        });
    </script>
</body>
</html>