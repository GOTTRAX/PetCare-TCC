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
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Home Secretária</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 8px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
        form {
            margin: 0;
        }
        button {
            padding: 5px 10px;
        }
    </style>
</head>
<body>

<div style="text-align: center; margin-top: 30px;">
    <a href="equipe.php"><button class="btn-equipe">Ver Equipe</button></a>
    <a href="cadastro_equipe.php"><button class="btn-equipe">Cadastrar Membro</button></a>
</div>

<h1 style="text-align: center;">Usuários Cadastrados</h1>

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
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <form method="POST" action="atualizar_usuario.php">
                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                    <td><?= htmlspecialchars($usuario['nome']) ?></td>
                    <td><?= htmlspecialchars($usuario['cpf']) ?></td>
                    <td><?= htmlspecialchars($usuario['telefone']) ?></td>
                    <td><?= htmlspecialchars($usuario['email']) ?></td>

                    <!-- Tipo -->
                    <td>
                        <select name="tipo_usuario">
                            <option value="Cliente" <?= $usuario['tipo_usuario'] == 'Cliente' ? 'selected' : '' ?>>Cliente</option>
                            <option value="Secretaria" <?= $usuario['tipo_usuario'] == 'Secretaria' ? 'selected' : '' ?>>Secretária</option>
                            <option value="Veterinario" <?= $usuario['tipo_usuario'] == 'Veterinario' ? 'selected' : '' ?>>Veterinário</option>
                        </select>
                    </td>

                    <td><?= htmlspecialchars($usuario['genero']) ?></td>
                    <td><?= htmlspecialchars($usuario['tentativas']) ?></td>
                    <td><?= htmlspecialchars($usuario['datanasc']) ?></td>
                    <td><?= htmlspecialchars($usuario['bloqueado_ate']) ?></td>
                    <td><?= htmlspecialchars($usuario['ultimo_login']) ?></td>

                    <!-- Ativo -->
                    <td>
                        <select name="ativo">
                            <option value="1" <?= $usuario['ativo'] ? 'selected' : '' ?>>Sim</option>
                            <option value="0" <?= !$usuario['ativo'] ? 'selected' : '' ?>>Não</option>
                        </select>
                    </td>

                    <td><?= htmlspecialchars($usuario['criado']) ?></td>
                    <td><?= htmlspecialchars($usuario['atualizado_em']) ?></td>
                    <td><button type="submit">Salvar</button></td>
                </form>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>
