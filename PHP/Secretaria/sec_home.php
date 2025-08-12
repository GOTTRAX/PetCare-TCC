<?php
session_start();

if (!isset($_SESSION["id"]) || $_SESSION["tipo_usuario"] !== "Secretaria") {
    header("Location: ../index.php");
    exit();
}

include '../conexao.php'; // Aqui seu $pdo é carregado

if (isset($pdo)) {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}

$mensagem = "";
$erro = "";

if (isset($_POST['especies'])) {
    $especies_input = trim($_POST['especies']);
    if ($especies_input !== "") {
        $especies = explode(',', $especies_input);

        try {
            foreach ($especies as $nome) {
                $nome = trim($nome);
                if ($nome === '')
                    continue;

                $stmt = $pdo->prepare("SELECT id FROM Especies WHERE LOWER(nome) = LOWER(?) LIMIT 1");
                $stmt->execute([$nome]);
                $existe = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$existe) {
                    $insert = $pdo->prepare("INSERT INTO Especies (nome) VALUES (?)");
                    $insert->execute([$nome]);
                }
            }
            $mensagem = "Espécies cadastradas com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar espécies: " . $e->getMessage();
        }
    } else {
        $erro = "Nenhuma espécie informada.";
    }
}

if (isset($_POST['adicionar_animal'])) {
    // Recebe e valida dados
    $nome = trim($_POST['nome'] ?? '');
    $datanasc = trim($_POST['datanasc'] ?? '');
    $especie_id = $_POST['especie_id'] ?? null;
    $raca = trim($_POST['raca'] ?? '');
    $porte = $_POST['porte'] ?? null;
    $sexo = $_POST['sexo'] ?? null;
    $usuario_id = $_POST['usuario_id'] ?? null;

    if ($nome === '' || empty($usuario_id) || empty($especie_id)) {
        $erro = "Preencha pelo menos Nome, Espécie e Proprietário.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO Animais (nome, datanasc, especie_id, raca, porte, sexo, usuario_id, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $nome,
                $datanasc !== '' ? $datanasc : null,
                $especie_id,
                $raca !== '' ? $raca : null,
                $porte !== '' ? $porte : null,
                $sexo !== '' ? $sexo : null,
                $usuario_id
            ]);
            $mensagem = "Animal cadastrado com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao cadastrar animal: " . $e->getMessage();
        }
    }
}

if (isset($_POST['editar_animal'])) {
    $animal_id = intval($_POST['animal_id'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $datanasc = trim($_POST['datanasc'] ?? '');
    $especie_id = $_POST['especie_id'] ?? null;
    $raca = trim($_POST['raca'] ?? '');
    $porte = $_POST['porte'] ?? null;
    $sexo = $_POST['sexo'] ?? null;
    $usuario_id = $_POST['usuario_id'] ?? null;

    if ($animal_id <= 0) {
        $erro = "ID do animal inválido.";
    } elseif ($nome === '' || empty($usuario_id) || empty($especie_id)) {
        $erro = "Preencha pelo menos Nome, Espécie e Proprietário.";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE Animais SET nome = ?, datanasc = ?, especie_id = ?, raca = ?, porte = ?, sexo = ?, usuario_id = ? WHERE id = ?");
            $stmt->execute([
                $nome,
                $datanasc !== '' ? $datanasc : null,
                $especie_id,
                $raca !== '' ? $raca : null,
                $porte !== '' ? $porte : null,
                $sexo !== '' ? $sexo : null,
                $usuario_id,
                $animal_id
            ]);
            $mensagem = "Animal atualizado com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao atualizar animal: " . $e->getMessage();
        }
    }
}

if (isset($_POST['excluir_animal'])) {
    $animal_id = intval($_POST['animal_id'] ?? 0);
    if ($animal_id <= 0) {
        $erro = "ID do animal inválido para exclusão.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM Animais WHERE id = ?");
            $stmt->execute([$animal_id]);
            $mensagem = "Animal excluído com sucesso!";
        } catch (Exception $e) {
            $erro = "Erro ao excluir animal: " . $e->getMessage();
        }
    }
}

try {
    $sql = "SELECT * FROM Usuarios";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erro = "Erro ao buscar usuários: " . $e->getMessage();
    $usuarios = [];
}

try {
    $sql_especies = "SELECT * FROM Especies ORDER BY nome";
    $stmt_especies = $pdo->prepare($sql_especies);
    $stmt_especies->execute();
    $especies = $stmt_especies->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erro = $erro ? $erro . " | Erro ao buscar espécies: " . $e->getMessage() : "Erro ao buscar espécies: " . $e->getMessage();
    $especies = [];
}

try {
    $sql_animais = "SELECT a.*, u.nome as dono_nome, e.nome as especie_nome,
                    TIMESTAMPDIFF(YEAR, a.datanasc, CURDATE()) as idade,
                    (SELECT MAX(data_consulta) FROM Consultas WHERE animal_id = a.id) as ultima_consulta
                    FROM Animais a
                    LEFT JOIN Usuarios u ON a.usuario_id = u.id
                    LEFT JOIN Especies e ON a.especie_id = e.id
                    ORDER BY a.nome";
    $stmt_animais = $pdo->prepare($sql_animais);
    $stmt_animais->execute();
    $animais = $stmt_animais->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erro = $erro ? $erro . " | Erro ao buscar animais: " . $e->getMessage() : "Erro ao buscar animais: " . $e->getMessage();
    $animais = [];
}

try {
    $sql_perfil = "SELECT * FROM Usuarios WHERE id = ?";
    $stmt_perfil = $pdo->prepare($sql_perfil);
    $stmt_perfil->execute([$_SESSION["id"]]);
    $perfil = $stmt_perfil->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $erro = $erro ? $erro . " | Erro ao buscar perfil: " . $e->getMessage() : "Erro ao buscar perfil: " . $e->getMessage();
    $perfil = [];
}

$animal_edicao = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT a.*, e.nome as especie_nome FROM Animais a LEFT JOIN Especies e ON a.especie_id = e.id WHERE a.id = ?");
            $stmt->execute([$id]);
            $animal_edicao = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            $erro = $erro ? $erro . " | Erro ao carregar animal para edição: " . $e->getMessage() : "Erro ao carregar animal para edição: " . $e->getMessage();
            $animal_edicao = null;
        }
    }
}

$animal_visualizar = null;
if (isset($_GET['ver'])) {
    $id = intval($_GET['ver']);
    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("SELECT a.*, u.nome as dono_nome, u.telefone as dono_telefone, u.email as dono_email, u.cpf as dono_cpf,
                                   e.nome as especie_nome,
                                   TIMESTAMPDIFF(YEAR, a.datanasc, CURDATE()) as idade
                                   FROM Animais a
                                   LEFT JOIN Usuarios u ON a.usuario_id = u.id
                                   LEFT JOIN Especies e ON a.especie_id = e.id
                                   WHERE a.id = ?");
            $stmt->execute([$id]);
            $animal_visualizar = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            $erro = $erro ? $erro . " | Erro ao carregar animal para visualização: " . $e->getMessage() : "Erro ao carregar animal para visualização: " . $e->getMessage();
            $animal_visualizar = null;
        }
    }
}

function formatarData($data)
{
    if ($data && $data !== "0000-00-00" && $data !== null) {
        return date("d/m/Y", strtotime($data));
    }
    return "-";
}

function formatarHora($hora)
{
    if ($hora && $hora !== "00:00:00" && $hora !== null) {
        return date("H:i", strtotime($hora));
    }
    return "-";
}

function calcularIdade($datanasc)
{
    if ($datanasc && $datanasc !== "0000-00-00" && $datanasc !== null) {
        $hoje = new DateTime();
        $nascimento = new DateTime($datanasc);
        $idade = $hoje->diff($nascimento);
        return $idade->y;
    }
    return 0;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    if (isset($_POST['acao']) && $_POST['acao'] === 'excluir' && isset($_POST['id'])) {
        $stmt = $pdo->prepare("DELETE FROM Usuarios WHERE id=?");
        $stmt->execute([$_POST['id']]);
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
    }
}

$editarUsuario = null;
if (isset($_GET['acao'], $_GET['id']) && $_GET['acao'] === 'editar') {
    $stmt = $pdo->prepare("SELECT * FROM Usuarios WHERE id=?");
    $stmt->execute([$_GET['id']]);
    $editarUsuario = $stmt->fetch(PDO::FETCH_ASSOC);
}

$usuarios = $pdo->query("SELECT * FROM Usuarios ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);

$tiposUsuario = ['Cliente', 'Veterinario', 'Secretaria', 'Cuidador'];
$generos = ['Masculino', 'Feminino', 'Outro'];

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Sistema Veterinário</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css">
    <style>
        :root {
    --primary-color: #4e73df;
    --primary-hover: #3a5bc7;
    --secondary-color: #1cc88a;
    --danger-color: #e74a3b;
    --warning-color: #f6c23e;
    --info-color: #36b9cc;
    --dark-color: #5a5c69;
    --gray-color: #858796;
    --light-color: #f8f9fc;
    --light-gray: #e3e6f0;
    --white: #ffffff;
    --positive: #1cc88a;
    --negative: #e74a3b;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f5f5f5;
    color: var(--dark-color);
}

.app-container {
    display: flex;
    min-height: 100vh;
}

/* Sidebar Styles */
.sidebar {
    width: 250px;
    background-color: var(--white);
    height: 100vh;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    position: fixed;
    z-index: 100;
    transition: all 0.3s;
}

.sidebar-header {
    padding: 20px;
    border-bottom: 1px solid var(--light-gray);
    text-align: center;
}

.sidebar-header h3 {
    color: var(--primary-color);
    font-size: 1.2rem;
    font-weight: 700;
}

.sidebar-menu {
    padding: 20px 0;
}

.menu-item {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: var(--dark-color);
    text-decoration: none;
    transition: all 0.3s;
    position: relative;
}

.menu-item i {
    margin-right: 10px;
    width: 20px;
    text-align: center;
    font-size: 0.9rem;
}

.menu-item span {
    flex: 1;
}

.menu-item:hover {
    background-color: var(--light-color);
    color: var(--primary-color);
}

.menu-item.active {
    background-color: var(--light-color);
    border-left: 3px solid var(--primary-color);
    color: var(--primary-color);
    font-weight: 600;
}

.badge {
    background-color: var(--primary-color);
    color: white;
    border-radius: 10px;
    padding: 2px 8px;
    font-size: 0.7rem;
    font-weight: bold;
    margin-left: 10px;
}

/* Main Content Styles */
.main-content {
    flex: 1;
    margin-left: 250px;
    transition: all 0.3s;
}

/* Header Styles */
.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: var(--white);
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    position: sticky;
    top: 0;
    z-index: 99;
}

.header-left h2 {
    color: var(--dark-color);
    font-size: 1.5rem;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 20px;
}

.notification-icon {
    position: relative;
    cursor: pointer;
    color: var(--gray-color);
    font-size: 1.2rem;
}

.notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background-color: var(--danger-color);
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.user-profile {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.user-profile img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.user-profile span {
    font-weight: 600;
}

/* Content Styles */
.content {
    padding: 20px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Dashboard Cards */
.dashboard-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background-color: var(--white);
    border-radius: 8px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    overflow: hidden;
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

.card-header {
    display: flex;
    justify-content: space-between;
    padding: 20px;
}

.card-title {
    color: var(--gray-color);
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.card-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: var(--dark-color);
    margin-bottom: 10px;
}

.card-footer {
    display: flex;
    align-items: center;
    font-size: 0.8rem;
}

.card-footer i {
    margin-right: 5px;
}

.card-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.card-icon.green {
    background-color: var(--secondary-color);
}

.card-icon.blue {
    background-color: var(--primary-color);
}

.card-icon.orange {
    background-color: var(--warning-color);
}

.positive {
    color: var(--positive);
}

.negative {
    color: var(--negative);
}

.fade-in {
    animation: fadeIn 0.5s forwards;
    opacity: 0;
}

/* Chart Container */
.chart-container {
    background-color: var(--white);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    margin-bottom: 30px;
}

.chart-container h2 {
    margin-bottom: 20px;
    color: var(--dark-color);
}

#chartType {
    padding: 8px 12px;
    border: 1px solid var(--light-gray);
    border-radius: 4px;
    margin-bottom: 20px;
    background-color: var(--white);
    color: var(--dark-color);
}

/* Form Styles */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: var(--dark-color);
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid var(--light-gray);
    border-radius: 4px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}

textarea.form-control {
    min-height: 100px;
    resize: vertical;
}

/* Table Styles */
.table-container {
    background-color: var(--white);
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
    margin-bottom: 30px;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid var(--light-gray);
}

th {
    background-color: var(--light-color);
    color: var(--dark-color);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.8rem;
}

tr:hover {
    background-color: var(--light-color);
}

/* Button Styles */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    text-decoration: none;
    gap: 8px;
}

.btn i {
    font-size: 0.9rem;
}

.btn-primary {
    background-color: var(--primary-color);
    color: white;
}

.btn-primary:hover {
    background-color: var(--primary-hover);
}

.btn-sm {
    padding: 6px 12px;
    font-size: 0.8rem;
}

.btn-warning {
    background-color: var(--warning-color);
    color: white;
}

.btn-warning:hover {
    background-color: #e0b43c;
}

/* Profile Styles */
.profile-tab {
    max-width: 600px;
    margin: 0 auto;
}

.profile-header {
    text-align: center;
    margin-bottom: 30px;
}

.profile-header img {
    width: 150px;
    height: 150px;
    border-radius: 50%;
    object-fit: cover;
    margin-bottom: 15px;
    border: 5px solid var(--light-color);
}

.profile-header h3 {
    color: var(--dark-color);
    margin-bottom: 5px;
}

.profile-header p {
    color: var(--gray-color);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .sidebar {
        width: 70px;
        overflow: hidden;
    }
    
    .sidebar-header h3,
    .menu-item span,
    .badge {
        display: none;
    }
    
    .menu-item {
        justify-content: center;
    }
    
    .menu-item i {
        margin-right: 0;
        font-size: 1.2rem;
    }
    
    .main-content {
        margin-left: 70px;
    }
    
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 576px) {
    .header {
        flex-direction: column;
        gap: 15px;
        padding: 15px;
    }
    
    .header-right {
        width: 100%;
        justify-content: space-between;
    }
    
    .content {
        padding: 15px;
    }
}

/* Animations */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fade-in {
    animation: fadeIn 0.5s forwards;
    opacity: 0;
}
    </style>
</head>

<body>
    <div class="app-container">
        <!-- Sidebar -->
        <div class="sidebar" id="sidebar">
            <div class="sidebar-header">

                <h3>PetCare</h3>
            </div>
            <div class="sidebar-menu">
                <a href="#" class="menu-item active" data-tab="dashboard">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#" class="menu-item" data-tab="usuarios">
                    <i class="fas fa-users-cog"></i>
                    <span>Gerenciar Usuários</span>
                </a>
                <a href="#" class="menu-item" data-tab="animais">
                    <i class="fas fa-paw"></i>
                    <span>Animais</span>
                    <span class="badge"> <?= count($animais) ?> </span> <!-- isso foi do cacete -->
                </a>
                <a href="#" class="menu-item" data-tab="perfil">
                    <i class="fas fa-user"></i>
                    <span>Meu Perfil</span>
                </a>
                <a href="#" class="menu-item" data-tab="configuracoes">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
                <a href="cadastro_equipe.php" class="menu-item" data-tab="cadequipe">
                    <i class="fas fa-users"></i>
                    <span>Cadastro de equipe</span>
                    <a>

                    </a>
                    <a href="../logout.php" class="menu-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Sair</span> <!-- Nao ta funcionando -->
                    </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="header">
                <div class="header-left">
                    <h2 id="page-title">Dashboard</h2>
                </div>
                <div class="header-right">
                    <div class="notification-icon">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge">3</span>
                    </div>
                    <div class="user-profile" id="user-profile">
                        <img src="https://via.placeholder.com/40" alt="User">
                        <span><?= htmlspecialchars($perfil['nome']) ?></span> <!-- isso tambem foi do cacete -->
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="content">
                <!-- Dashboard Tab -->
                <div class="tab-content active" id="dashboard-tab">
                    <!-- Cards -->
                    <div class="dashboard-cards">
                        <div class="card fade-in" style="animation-delay: 0.1s;">
                            <div class="card-header">
                                <div>
                                    <div class="card-title">Total de Clientes</div>
                                    <div class="card-value"><?= count($usuarios) ?></div>
                                    <!-- atencao  3 usuarios porem quero saber quais sao clientes-->
                                    <div class="card-footer">
                                        <i class="fas fa-arrow-up positive"></i>
                                        <span class="positive">12% este mês</span><!-- atencao -->
                                    </div>
                                </div>
                                <div class="card-icon green">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card fade-in" style="animation-delay: 0.2s;">
                            <div class="card-header">
                                <div>
                                    <div class="card-title"></div>
                                    <div class="card-value"></div><!-- atencao -->
                                    <div class="card-footer">
                                        <i class="fas fa-arrow-down negative"></i>
                                        <span class="negative">2% ontem</span><!-- atencao -->
                                    </div>
                                </div>
                                <div class="card-icon blue">
                                    <i class="fas fa-calendar-check"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card fade-in" style="animation-delay: 0.3s;">
                            <div class="card-header">
                                <div>
                                    <div class="card-title">Animais Cadastrados</div>
                                    <div class="card-value"><?= count($animais) ?></div>
                                    <div class="card-footer">
                                        <i class="fas fa-arrow-up positive"></i>
                                        <span class="positive">5% este mês</span><!-- atencao -->
                                    </div>
                                </div>
                                <div class="card-icon blue">
                                    <i class="fas fa-paw"></i>
                                </div>
                            </div>
                        </div>

                        <div class="card fade-in" style="animation-delay: 0.1;"><!-- atencao -->
                            <div class="card-header">
                                <div>
                                    <div class="card-title"></div>
                                    <div class="card-value"> <!-- atencao --> </div>
                                    <div class="card-footer">
                                        <i class="fas fa-arrow-up positive"></i>
                                        <span class="positive">18% este mês</span>
                                    </div>
                                </div>
                                <div class="card-icon orange">
                                    <i class="fas fa-dollar-sign"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    
                    <div style="margin: 30px; padding: 20px; border: 1px solid #ccc; border-radius: 8px;">
                        <h2>Quais animais sua clínica irá atender?</h2>
                        <form method="POST">
                            <label for="especies">Digite os nomes dos animais (separados por vírgula):</label><br><br>
                            <input type="text" id="especies" name="especies" placeholder="Ex: Cachorro, Gato, Coelho"
                                style="width: 100%; padding: 10px;" required>
                            <br><br>
                            <button type="submit" style="padding: 10px 20px;">Cadastrar Espécies</button>
                        </form>
                    </div>
                    <!-- Aqui vai a parte do gráfico -->
                    <div class="chart-container">
                        <h2>Espécies que Atendemos</h2>

                        <!-- Select para escolher o tipo de gráfico -->
                        <select id="chartType" onchange="updateChartType()">
                            <option value="pie">Pizza</option>
                            <option value="bar">Barra</option>
                            <option value="line">Linha</option>
                        </select>

                        <!-- Div para o gráfico -->
                        <canvas id="animalChart" height="111"></canvas>
                    </div>

                    <!-- Incluindo a biblioteca Chart.js -->
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

                    <script>
                        let chartType = 'pie';
                        let chart = null;
                        let especiesData = [];

                        function fetchData() {
                            fetch('fetch_animais.php')
                                .then(response => response.json())
                                .then(data => {
                                    if (data.length === 0 || data.erro) {
                                        alert('Nenhuma espécie encontrada!');
                                    } else {
                                        especiesData = data;
                                        generateChart();
                                    }
                                })
                                .catch(error => {
                                    console.error('Erro ao buscar os dados:', error);
                                });
                        }

                        function generateChart() {
                            const ctx = document.getElementById('animalChart').getContext('2d');

                            if (chart) {
                                chart.destroy();
                            }

                            const labels = especiesData.map(e => e.especie_nome);
                            const data = especiesData.map(e => e.quantidade);
                            const colors = labels.map(() => getRandomColor());

                            chart = new Chart(ctx, {
                                type: chartType,
                                data: {
                                    labels: labels,
                                    datasets: [{
                                        label: 'Quantidade por Espécie',
                                        data: data,
                                        backgroundColor: colors
                                    }]
                                }
                            });
                        }

                        function updateChartType() {
                            chartType = document.getElementById('chartType').value;
                            generateChart();
                        }

                        function getRandomColor() {
                            return '#' + Math.floor(Math.random() * 16777215).toString(16);
                        }

                        fetchData();
                    </script>
                </div>



                <!-- Usuários Tab -->
                <div class="tab-content" id="usuarios-tab">
                    <div class="table-container">

                        <h1>Gerenciar Usuários</h1>

                        <!-- Formulário Adicionar / Editar -->
                        <h2><?= $editarUsuario ? "Editar Usuário #{$editarUsuario['id']}" : "Adicionar Novo Usuário" ?>
                        </h2>
                        <form method="post">
                            <input type="hidden" name="acao" value="<?= $editarUsuario ? 'editar' : 'adicionar' ?>">
                            <?php if ($editarUsuario): ?>
                                <input type="hidden" name="id" value="<?= $editarUsuario['id'] ?>">
                            <?php endif; ?>

                            <label>Nome:<br>
                                <input type="text" name="nome"
                                    value="<?= htmlspecialchars($editarUsuario['nome'] ?? '') ?>" required>
                            </label><br><br>

                            <label>CPF:<br>
                                <input type="text" name="cpf"
                                    value="<?= htmlspecialchars($editarUsuario['cpf'] ?? '') ?>" required>
                            </label><br><br>

                            <label>Telefone:<br>
                                <input type="text" name="telefone"
                                    value="<?= htmlspecialchars($editarUsuario['telefone'] ?? '') ?>">
                            </label><br><br>

                            <label>Email:<br>
                                <input type="email" name="email"
                                    value="<?= htmlspecialchars($editarUsuario['email'] ?? '') ?>" required>
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
                                <input type="date" name="datanasc"
                                    value="<?= htmlspecialchars($editarUsuario['datanasc'] ?? '') ?>">
                            </label><br><br>

                            <label>Tentativas de Login:<br>
                                <input type="number" name="tentativas" min="0"
                                    value="<?= htmlspecialchars($editarUsuario['tentativas'] ?? 0) ?>">
                            </label><br><br>

                            <label>Último Login (YYYY-MM-DD HH:MM:SS):<br>
                                <input type="text" name="ultimo_login"
                                    value="<?= htmlspecialchars($editarUsuario['ultimo_login'] ?? '') ?>"
                                    placeholder="YYYY-MM-DD HH:MM:SS">
                            </label><br><br>

                            <label>Bloqueado até (YYYY-MM-DD HH:MM:SS):<br>
                                <input type="text" name="bloqueado_ate"
                                    value="<?= htmlspecialchars($editarUsuario['bloqueado_ate'] ?? '') ?>"
                                    placeholder="YYYY-MM-DD HH:MM:SS">
                            </label><br><br>

                            <label>Ativo:<br>
                                <select name="ativo" required>
                                    <option value="1" <?= (isset($editarUsuario['ativo']) && $editarUsuario['ativo'] == 1) ? 'selected' : '' ?>>Ativo</option>
                                    <option value="0" <?= (isset($editarUsuario['ativo']) && $editarUsuario['ativo'] == 0) ? 'selected' : '' ?>>Inativo</option>
                                </select>
                            </label><br><br>

                            <label>Descrição:<br>
                                <textarea name="descricao" rows="4"
                                    cols="50"><?= htmlspecialchars($editarUsuario['descricao'] ?? '') ?></textarea>
                            </label><br><br>

                            <button
                                type="submit"><?= $editarUsuario ? "Salvar Alterações" : "Adicionar Usuário" ?></button>
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
                                    <tr>
                                        <td colspan="15" style="text-align:center;">Nenhum usuário cadastrado.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td><?= $usuario['id'] ?></td>
                                            <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                            <td><?= htmlspecialchars($usuario['telefone']) ?></td>
                                            <td><?= htmlspecialchars($usuario['email']) ?></td>
                                            <td><?= htmlspecialchars($usuario['tipo_usuario']) ?></td>
                                            <td><?= htmlspecialchars($usuario['genero']) ?></td>
                                            <td><?= $usuario['datanasc'] ? (new DateTime($usuario['datanasc']))->format('d/m/Y') : '-' ?>
                                            </td>
                                            <td><?= $usuario['tentativas'] ?></td>
                                            <td><?= $usuario['ultimo_login'] ? (new DateTime($usuario['ultimo_login']))->format('d/m/Y H:i') : '-' ?>
                                            </td>
                                            <td><?= $usuario['bloqueado_ate'] ? (new DateTime($usuario['bloqueado_ate']))->format('d/m/Y H:i') : '-' ?>
                                            </td>
                                            <td><?= $usuario['ativo'] ? 'Ativo' : 'Inativo' ?></td>
                                            <td><?= $usuario['criado'] ? (new DateTime($usuario['criado']))->format('d/m/Y H:i') : '-' ?>
                                            </td>
                                            <td><?= $usuario['atualizado_em'] ? (new DateTime($usuario['atualizado_em']))->format('d/m/Y H:i') : '-' ?>
                                            </td>
                                            <td><?= nl2br(htmlspecialchars($usuario['descricao'])) ?></td>
                                            <td>
                                                <form method="get" style="display:inline;">
                                                    <input type="hidden" name="acao" value="editar">
                                                    <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                                                    <button type="submit">Editar</button>
                                                </form>
                                                <form method="post" style="display:inline;"
                                                    onsubmit="return confirm('Tem certeza que deseja excluir este usuário?');">
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
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Adicionar Usuário
                        </a>
                    </div>
                </div>

                <!-- agr eu to aq -->
                <div class="tab-content" id="animais-tab">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>especie</th>
                                    <th>Raça</th>
                                    <th>Idade</th>
                                    <th>Dono</th>
                                    <th>Última Consulta</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($animais)): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center; padding: 20px;">Nenhum animal cadastrado
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($animais as $animal): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($animal['id']) ?></td>
                                            <td><?= htmlspecialchars($animal['nome']) ?></td>
                                            <td><?= htmlspecialchars($animal['especie_id']) ?></td>
                                            <td><?= htmlspecialchars($animal['raca']) ?></td>
                                            <td><?= htmlspecialchars($animal['idade']) ?> anos</td>
                                            <td><?= htmlspecialchars($animal['dono_nome']) ?></td>
                                            <td><?= formatarData($animal['ultima_consulta']) ?></td>
                                            <td>
                                                <div style="display: flex; gap: 5px;">
                                                    <a href="#" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-sm btn-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="display: flex; justify-content: flex-end;">
                        <a href="#" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Adicionar Animal
                        </a>
                    </div>
                </div>

                <!-- Perfil Tab -->
                <div class="tab-content" id="perfil-tab">
                    <div style="max-width: 600px; margin: 0 auto;">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <img src="https://via.placeholder.com/150" alt="Foto de Perfil"
                                style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin-bottom: 15px;">
                            <h3><?= htmlspecialchars($perfil['nome']) ?></h3>
                            <p style="color: var(--gray-color);"><?= htmlspecialchars($perfil['tipo_usuario']) ?></p>
                        </div>

                        <form>
                            <div class="form-group">
                                <label class="form-label">Nome Completo</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($perfil['nome']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control"
                                    value="<?= htmlspecialchars($perfil['email']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">CPF</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($perfil['cpf']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Telefone</label>
                                <input type="text" class="form-control"
                                    value="<?= htmlspecialchars($perfil['telefone']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Data de Nascimento</label>
                                <input type="date" class="form-control"
                                    value="<?= htmlspecialchars($perfil['datanasc']) ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Nova Senha</label>
                                <input type="password" class="form-control"
                                    placeholder="Deixe em branco para manter a atual">
                            </div>

                            <div class="form-group">
                                <label class="form-label">Confirmar Nova Senha</label>
                                <input type="password" class="form-control">
                            </div>

                            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Salvar Alterações
                                </button>
                            </div>
                        </form>
                    </div>
                </div class="tab-content" id="perfil-tab">>
                <button></button>
                <a href="cadastro_equipe.php"> cadastro de quipe</a>
                <div>

                </div>
            </div> 
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        document.querySelectorAll('.menu-item').forEach(item => {
            item.addEventListener('click', function (e) {
                e.preventDefault();

                document.querySelectorAll('.menu-item').forEach(i => i.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));

                this.classList.add('active');

                const tabId = this.getAttribute('data-tab') + '-tab';
                document.getElementById(tabId).classList.add('active');

                const pageTitle = this.querySelector('span').textContent;
                document.getElementById('page-title').textContent = pageTitle;
            });
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }

        const consultasCtx = document.getElementById('consultasChart').getContext('2d');
        const consultasChart = new Chart(consultasCtx, {
            type: 'line',
            data: {
                labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30'],
                datasets: [{
                    label: 'Consultas',
                    data: [5, 7, 3, 8, 4, 6, 9, 5, 7, 8, 6, 7, 9, 8, 6, 7, 9, 8, 7, 6, 8, 9, 7, 8, 6, 7, 9, 8, 7, 9],
                    backgroundColor: 'rgba(72, 149, 239, 0.2)',
                    borderColor: 'rgba(72, 149, 239, 1)',
                    borderWidth: 2,
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });

        const animaisCtx = document.getElementById('animaisChart').getContext('2d');
        const animaisChart = new Chart(animaisCtx, {
            type: 'doughnut',
            data: {
                labels: ['Cães', 'Gatos', 'Pássaros', 'Outros'],
                datasets: [{
                    data: [45, 30, 15, 10],
                    backgroundColor: [
                        '#4361ee',
                        '#3f37c9',
                        '#4895ef',
                        '#4cc9f0'
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right'
                    }
                },
                cutout: '70%'
            }
        });

        window.addEventListener('resize', function () {
            consultasChart.resize();
            animaisChart.resize();
        });
    </script>
</body>

</html>