<?php
// Configurações do banco
$host = "localhost";
$dbname = "PetCare";
$user = "root";
$pass = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}

// Função para formatar datas
function formatarData($dt) {
    if (!$dt) return '-';
    $d = new DateTime($dt);
    return $d->format('d/m/Y H:i');
}

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Adicionar usuário
    if (isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
        $stmt = $pdo->prepare("INSERT INTO Usuarios 
            (nome, cpf, telefone, email, tipo_usuario, genero, tentativas, datanasc, bloqueado_ate, ultimo_login, ativo, descricao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $_POST['telefone'],
            $_POST['email'],
            $_POST['tipo_usuario'],
            $_POST['genero'],
            intval($_POST['tentativas']),
            $_POST['datanasc'] ?: null,
            $_POST['bloqueado_ate'] ?: null,
            $_POST['ultimo_login'] ?: null,
            isset($_POST['ativo']) && $_POST['ativo'] === '1' ? 1 : 0,
            $_POST['descricao']
        ]);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    // Editar usuário
    if (isset($_POST['acao']) && $_POST['acao'] === 'editar' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("UPDATE Usuarios SET 
            nome=?, cpf=?, telefone=?, email=?, tipo_usuario=?, genero=?, tentativas=?, datanasc=?, bloqueado_ate=?, ultimo_login=?, ativo=?, descricao=?, atualizado_em=NOW()
            WHERE id=?");
        $stmt->execute([
            $_POST['nome'],
            $_POST['cpf'],
            $_POST['telefone'],
            $_POST['email'],
            $_POST['tipo_usuario'],
            $_POST['genero'],
            intval($_POST['tentativas']),
            $_POST['datanasc'] ?: null,
            $_POST['bloqueado_ate'] ?: null,
            $_POST['ultimo_login'] ?: null,
            isset($_POST['ativo']) && $_POST['ativo'] === '1' ? 1 : 0,
            $_POST['descricao'],
            $_POST['id']
        ]);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }

    // Excluir usuário
    if (isset($_POST['acao']) && $_POST['acao'] === 'excluir' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM Usuarios WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

// Se for editar, pegar dados para preencher formulário
$editarUsuario = null;
if (isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'editar') {
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editarUsuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Listar todos usuários
$usuarios = $pdo->query("SELECT * FROM Usuarios ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

// Listas para selects
$tiposUsuario = ['Cliente', 'Veterinario', 'Secretaria', 'Cuidador'];
$generos = ['Masculino', 'Feminino', 'Outro'];

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <title>Gerenciar Usuários</title>
</head>
<body>

<h1>Gerenciar Usuários</h1>

<!-- Formulário Adicionar / Editar -->
<h2><?= $editarUsuario ? "Editar Usuário #{$editarUsuario['id']}" : "Adicionar Novo Usuário" ?></h2>
<form method="post">
    <input type="hidden" name="acao" value="<?= $editarUsuario ? 'editar' : 'adicionar' ?>">
    <?php if ($editarUsuario): ?>
        <input type="hidden" name="id" value="<?= $editarUsuario['id'] ?>">
    <?php endif; ?>

    <label>Nome:<br>
        <input type="text" name="nome" value="<?= htmlspecialchars($editarUsuario['nome'] ?? '') ?>" required>
    </label><br><br>

    <label>CPF:<br>
        <input type="text" name="cpf" value="<?= htmlspecialchars($editarUsuario['cpf'] ?? '') ?>" required>
    </label><br><br>

    <label>Telefone:<br>
        <input type="text" name="telefone" value="<?= htmlspecialchars($editarUsuario['telefone'] ?? '') ?>">
    </label><br><br>

    <label>Email:<br>
        <input type="email" name="email" value="<?= htmlspecialchars($editarUsuario['email'] ?? '') ?>" required>
    </label><br><br>

    <label>Tipo de Usuário:<br>
        <select name="tipo_usuario" required>
            <?php foreach ($tiposUsuario as $tipo): ?>
                <option value="<?= $tipo ?>" <?= (isset($editarUsuario['tipo_usuario']) && $editarUsuario['tipo_usuario'] === $tipo) ? 'selected' : '' ?>>
                    <?= $tipo ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Gênero:<br>
        <select name="genero" required>
            <?php foreach ($generos as $gen): ?>
                <option value="<?= $gen ?>" <?= (isset($editarUsuario['genero']) && $editarUsuario['genero'] === $gen) ? 'selected' : '' ?>>
                    <?= $gen ?>
                </option>
            <?php endforeach; ?>
        </select>
    </label><br><br>

    <label>Data de Nascimento:<br>
        <input type="date" name="datanasc" value="<?= htmlspecialchars($editarUsuario['datanasc'] ?? '') ?>">
    </label><br><br>

    <label>Tentativas de Login:<br>
        <input type="number" name="tentativas" min="0" value="<?= htmlspecialchars($editarUsuario['tentativas'] ?? 0) ?>">
    </label><br><br>

    <label>Último Login (YYYY-MM-DD HH:MM:SS):<br>
        <input type="text" name="ultimo_login" value="<?= htmlspecialchars($editarUsuario['ultimo_login'] ?? '') ?>" placeholder="YYYY-MM-DD HH:MM:SS">
    </label><br><br>

    <label>Bloqueado até (YYYY-MM-DD HH:MM:SS):<br>
        <input type="text" name="bloqueado_ate" value="<?= htmlspecialchars($editarUsuario['bloqueado_ate'] ?? '') ?>" placeholder="YYYY-MM-DD HH:MM:SS">
    </label><br><br>

    <label>Ativo:<br>
        <select name="ativo" required>
            <option value="1" <?= (isset($editarUsuario['ativo']) && $editarUsuario['ativo'] == 1) ? 'selected' : '' ?>>Ativo</option>
            <option value="0" <?= (isset($editarUsuario['ativo']) && $editarUsuario['ativo'] == 0) ? 'selected' : '' ?>>Inativo</option>
        </select>
    </label><br><br>

    <label>Descrição:<br>
        <textarea name="descricao" rows="4" cols="50"><?= htmlspecialchars($editarUsuario['descricao'] ?? '') ?></textarea>
    </label><br><br>

    <button type="submit"><?= $editarUsuario ? "Salvar Alterações" : "Adicionar Usuário" ?></button>
    <?php if ($editarUsuario): ?>
        <a href="<?= $_SERVER['PHP_SELF'] ?>">Cancelar</a>
    <?php endif; ?>
</form>

<hr>

<!-- Lista Usuários -->
<h2>Lista de Usuários</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Telefone</th>
            <th>Email</th>
            <th>Tipo</th>
            <th>Gênero</th>
            <th>Data Nasc.</th>
            <th>Tentativas</th>
            <th>Último Login</th>
            <th>Bloqueado Até</th>
            <th>Ativo</th>
            <th>Criado</th>
            <th>Atualizado</th>
            <th>Descrição</th>
            <th>Ações</th>
        </tr>
    </thead>
    <tbody>
    <?php if (empty($usuarios)): ?>
        <tr><td colspan="15" style="text-align:center;">Nenhum usuário cadastrado.</td></tr>
    <?php else: ?>
        <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?= $usuario['id'] ?></td>
                <td><?= htmlspecialchars($usuario['nome']) ?></td>
                <td><?= htmlspecialchars($usuario['telefone']) ?></td>
                <td><?= htmlspecialchars($usuario['email']) ?></td>
                <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                <td><?= htmlspecialchars($usuario['genero']) ?></td>
                <td><?= $usuario['datanasc'] ? (new DateTime($usuario['datanasc']))->format('d/m/Y') : '-' ?></td>
                <td><?= $usuario['tentativas'] ?></td>
                <td><?= $usuario['ultimo_login'] ? (new DateTime($usuario['ultimo_login']))->format('d/m/Y H:i') : '-' ?></td>
                <td><?= $usuario['bloqueado_ate'] ? (new DateTime($usuario['bloqueado_ate']))->format('d/m/Y H:i') : '-' ?></td>
                <td><?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?></td>
                <td><?= $usuario['criado'] ? (new DateTime($usuario['criado']))->format('d/m/Y H:i') : '-' ?></td>
                <td><?= $usuario['atualizado_em'] ? (new DateTime($usuario['atualizado_em']))->format('d/m/Y H:i') : '-' ?></td>
                <td><?= nl2br(htmlspecialchars($usuario['descricao'])) ?></td>
                <td>
                    <form method="get" style="display:inline;">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                        <button type="submit">Editar</button>
                    </form>
                    <form method="post" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                        <button type="submit">Excluir</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
