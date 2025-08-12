<?php
// ======= CONEXÃO COM O BANCO =======
$host = "localhost";
$dbname = "PetCare"; // ajuste se precisar
$user = "root";
$pass = "root";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// ======= FUNÇÃO PARA CALCULAR IDADE =======
function calcularIdade($dataNasc) {
    if (!$dataNasc) return "Não informado";
    $nascimento = new DateTime($dataNasc);
    $hoje = new DateTime();
    return $nascimento->diff($hoje)->y . " ano(s)";
}

// ======= AÇÃO DE EXCLUIR =======
if (isset($_GET['acao']) && $_GET['acao'] === 'excluir' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("DELETE FROM Animais WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ======= LISTAS AUXILIARES =======
$especies = $pdo->query("SELECT id, nome FROM Especies")->fetchAll(PDO::FETCH_ASSOC);
$usuarios = $pdo->query("SELECT id, nome FROM Usuarios")->fetchAll(PDO::FETCH_ASSOC);

// ======= AÇÃO DE ADICIONAR =======
if (isset($_POST['acao']) && $_POST['acao'] === 'adicionar') {
    $stmt = $pdo->prepare("INSERT INTO Animais (nome, datanasc, especie_id, raca, usuario_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_POST['nome'],
        $_POST['datanasc'],
        $_POST['especie_id'],
        $_POST['raca'],
        $_POST['usuario_id']
    ]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ======= AÇÃO DE EDITAR =======
if (isset($_POST['acao']) && $_POST['acao'] === 'editar' && isset($_POST['id'])) {
    $stmt = $pdo->prepare("UPDATE Animais SET nome=?, datanasc=?, especie_id=?, raca=?, usuario_id=? WHERE id=?");
    $stmt->execute([
        $_POST['nome'],
        $_POST['datanasc'],
        $_POST['especie_id'],
        $_POST['raca'],
        $_POST['usuario_id'],
        $_POST['id']
    ]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

// ======= PEGAR DADOS PARA EDITAR =======
$animalEditar = null;
if (isset($_GET['acao']) && $_GET['acao'] === 'editar' && isset($_GET['id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Animais WHERE id = ?");
    $stmt->execute([$_GET['id']]);
    $animalEditar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ======= FILTROS =======
$filtro = $_GET['filtro'] ?? null;
$valor = $_GET['valor'] ?? null;

// ======= MONTAR QUERY DINÂMICA =======
$sql = "
    SELECT a.id, a.nome, a.datanasc, e.nome AS especie, a.raca, u.nome AS dono
    FROM Animais a
    JOIN Especies e ON a.especie_id = e.id
    JOIN Usuarios u ON a.usuario_id = u.id
";

$params = [];

if ($filtro === 'especie' && $valor) {
    $sql .= " WHERE e.nome = :valor";
    $params[':valor'] = $valor;
} elseif ($filtro === 'dono' && $valor) {
    $sql .= " WHERE u.nome = :valor";
    $params[':valor'] = $valor;
} elseif ($filtro === 'cachorros-idade') {
    $sql .= " WHERE e.nome = 'Cachorro' ORDER BY a.datanasc DESC"; // mais novo primeiro (data nascimento maior)
} else {
    $sql .= " ORDER BY a.id ASC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$animais = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>CRUD Animais</title>
</head>
<body>

<h1>CRUD de Animais</h1>

<!-- FORMULÁRIO DE ADICIONAR/EDITAR -->
<h2><?= $animalEditar ? 'Editar Animal' : 'Adicionar Animal' ?></h2>
<form method="post">
    <input type="hidden" name="acao" value="<?= $animalEditar ? 'editar' : 'adicionar' ?>">
    <?php if ($animalEditar): ?>
        <input type="hidden" name="id" value="<?= $animalEditar['id'] ?>">
    <?php endif; ?>

    Nome: <input type="text" name="nome" value="<?= htmlspecialchars($animalEditar['nome'] ?? '') ?>" required><br>
    Data de Nascimento: <input type="date" name="datanasc" value="<?= htmlspecialchars($animalEditar['datanasc'] ?? '') ?>"><br>
    Espécie:
    <select name="especie_id" required>
        <?php foreach ($especies as $e): ?>
            <option value="<?= $e['id'] ?>" <?= (isset($animalEditar['especie_id']) && $animalEditar['especie_id'] == $e['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($e['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    Raça: <input type="text" name="raca" value="<?= htmlspecialchars($animalEditar['raca'] ?? '') ?>"><br>
    Dono:
    <select name="usuario_id" required>
        <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= (isset($animalEditar['usuario_id']) && $animalEditar['usuario_id'] == $u['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($u['nome']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>
    <button type="submit"><?= $animalEditar ? 'Atualizar' : 'Adicionar' ?></button>
    <?php if ($animalEditar): ?>
        <a href="<?= $_SERVER['PHP_SELF'] ?>">Cancelar</a>
    <?php endif; ?>
</form>

<hr>

<!-- FILTROS -->
<form method="GET">
    <select name="filtro">
        <option value="">-- Filtrar por --</option>
        <option value="especie" <?= ($filtro === 'especie') ? 'selected' : '' ?>>Espécie</option>
        <option value="dono" <?= ($filtro === 'dono') ? 'selected' : '' ?>>Dono</option>
        <option value="cachorros-idade" <?= ($filtro === 'cachorros-idade') ? 'selected' : '' ?>>Cachorros (do mais novo p/ mais velho)</option>
    </select>
    <input type="text" name="valor" placeholder="Digite o valor (se necessário)" value="<?= htmlspecialchars($valor ?? '') ?>">
    <button type="submit">Filtrar</button>
</form>

<!-- LISTA DE ANIMAIS -->
<h2>Lista de Animais</h2>
<table border="1" cellpadding="5">
    <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Espécie</th>
        <th>Raça</th>
        <th>Idade</th>
        <th>Dono</th>
        <th>Ações</th>
    </tr>
    <?php if ($animais): ?>
        <?php foreach ($animais as $animal): ?>
        <tr>
            <td><?= $animal['id'] ?></td>
            <td><?= htmlspecialchars($animal['nome']) ?></td>
            <td><?= htmlspecialchars($animal['especie']) ?></td>
            <td><?= htmlspecialchars($animal['raca']) ?></td>
            <td><?= calcularIdade($animal['datanasc']) ?></td>
            <td><?= htmlspecialchars($animal['dono']) ?></td>
            <td>
                <a href="?acao=editar&id=<?= $animal['id'] ?>">Editar</a> | 
                <a href="?acao=excluir&id=<?= $animal['id'] ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7">Nenhum animal cadastrado.</td></tr>
    <?php endif; ?>
</table>

</body>
</html>
