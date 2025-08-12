<?php
session_start();

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

include '../conexao.php';

// ======= CADASTRO DE ANIMAL =======
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $datanasc = !empty($_POST['datanasc']) ? $_POST['datanasc'] : null;
    $especie_nome = $_POST['especie']; // Nome da espécie enviado pelo formulário
    $raca = !empty($_POST['raca']) ? $_POST['raca'] : null;
    $sexo = !empty($_POST['sexo']) ? $_POST['sexo'] : null;
    $porte = !empty($_POST['porte']) ? $_POST['porte'] : null;
    $usuario_id = $_SESSION["id"];

    try {
        // Primeiro busca o ID da espécie
        $stmt_especie = $pdo->prepare("SELECT id FROM Especies WHERE nome = :nome");
        $stmt_especie->execute([':nome' => $especie_nome]);
        $especie = $stmt_especie->fetch(PDO::FETCH_ASSOC);

        if (!$especie) {
            echo "<div class='alert error'>Espécie não encontrada!</div>";
            exit();
        }

        $especie_id = $especie['id'];

        // Agora insere o animal com o especie_id correto
        $sql = "INSERT INTO Animais (nome, datanasc, especie_id, raca, porte, sexo, usuario_id)
                VALUES (:nome, :datanasc, :especie_id, :raca, :porte, :sexo, :usuario_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':datanasc' => $datanasc,
            ':especie_id' => $especie_id,
            ':raca' => $raca,
            ':porte' => $porte,
            ':sexo' => $sexo,
            ':usuario_id' => $usuario_id
        ]);

        echo "<div class='alert success'>Animal cadastrado com sucesso!</div>";
    } catch (PDOException $e) {
        echo "<div class='alert error'>Erro ao cadastrar animal: " . $e->getMessage() . "</div>";
    }
}

// ======= LISTAGEM DE ANIMAIS POR DONO =======
$animais = [];

try {
    $sql = "SELECT 
                a.nome AS animal_nome, 
                e.nome AS especie, 
                a.raca, 
                a.sexo, 
                a.porte
            FROM Animais a
            JOIN Especies e ON a.especie_id = e.id
            WHERE a.usuario_id = :usuario_id
            ORDER BY a.nome";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $_SESSION["id"]]);
    $animais = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert error'>Erro ao listar animais: " . $e->getMessage() . "</div>";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - PetShop</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e9f3d;
            --primary-dark: #1e5128;
            --primary-light: #d8e9a8;
            --secondary: #191a19;
            --light: #f5f5f5;
            --gray: #e0e0e0;
            --dark-gray: #757575;
            --white: #ffffff;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f9f9f9;
            color: var(--secondary);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .profile-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 30px;
            text-align: center;
        }

        .avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 15px;
            box-shadow: var(--shadow);
        }

        .profile-header h1 {
            color: var(--primary-dark);
            margin-bottom: 10px;
        }

        /* Navigation */
        .profile-nav {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 30px;
            flex-wrap: wrap;
        }

        .profile-nav button {
            padding: 12px 24px;
            background-color: var(--white);
            border: 2px solid var(--primary);
            border-radius: 30px;
            color: var(--primary-dark);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 16px;
        }

        .profile-nav button:hover {
            background-color: var(--primary-light);
        }

        .profile-nav button.active {
            background-color: var(--primary);
            color: white;
        }

        /* Content Sections */
        .content-section {
            display: none;
            background-color: var(--white);
            border-radius: var(--radius);
            padding: 30px;
            box-shadow: var(--shadow);
            margin-bottom: 30px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .content-section.active {
            display: block;
        }

        .content-section h2 {
            color: var(--primary-dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--gray);
        }

        /* Profile Info */
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            background-color: var(--light);
            padding: 15px;
            border-radius: 8px;
        }

        .info-item strong {
            color: var(--primary-dark);
            display: block;
            margin-bottom: 5px;
        }

        /* Animal Form */
        .flex-container {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
        }

        .form-container,
        .pets-container {
            flex: 1;
            min-width: 300px;
        }

        .form-animal {
            background-color: var(--white);
            padding: 25px;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .form-animal label {
            display: block;
            margin-top: 15px;
            font-weight: 600;
            color: var(--secondary);
        }

        .form-animal input,
        .form-animal select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 1px solid var(--gray);
            border-radius: 6px;
            background-color: var(--light);
            transition: var(--transition);
        }

        .form-animal input:focus,
        .form-animal select:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px var(--primary-light);
        }

        .form-animal button {
            margin-top: 20px;
            padding: 12px;
            background-color: var(--primary);
            border: none;
            border-radius: 6px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: var(--transition);
        }

        .form-animal button:hover {
            background-color: var(--primary-dark);
        }

        /* Pets List */
        .pets-list {
            margin-top: 20px;
        }

        .pet-card {
            background-color: var(--light);
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: var(--transition);
        }

        .pet-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }

        .pet-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: var(--primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary-dark);
            font-size: 20px;
        }

        .pet-info {
            flex: 1;
        }

        .pet-name {
            font-weight: 600;
            color: var(--primary-dark);
            margin-bottom: 5px;
        }

        .pet-details {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            font-size: 14px;
            color: var(--dark-gray);
        }

        .pet-detail {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .no-pets {
            text-align: center;
            padding: 30px;
            color: var(--dark-gray);
            font-style: italic;
        }

        /* Config Section */
        .config-form {
            max-width: 500px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .btn-logout {
            background-color: #e74c3c !important;
            margin-top: 30px;
        }

        .btn-logout:hover {
            background-color: #c0392b !important;
        }

        /* Alerts */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .flex-container {
                flex-direction: column;
            }

            .profile-nav {
                flex-direction: column;
                align-items: center;
            }

            .profile-nav button {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <div class="avatar">
                <?php
                $nome_completo = $_SESSION["nome"];
                $partes = explode(' ', trim($nome_completo));

                $inicial_nome = strtoupper(mb_substr($partes[0], 0, 1));
                $inicial_sobrenome = isset($partes[1]) ? strtoupper(mb_substr($partes[1], 0, 1)) : '';

                echo $inicial_nome . $inicial_sobrenome;
                ?>
            </div>
            <h1>Olá, <?= explode(' ', $_SESSION["nome"])[0] ?>!</h1>
            <p>Bem-vindo ao seu perfil PetShop</p>
        </div>

        <!-- Navigation -->
        <div class="profile-nav">
            <button class="active" onclick="showSection('perfil')">
                <i class="fas fa-user"></i> Perfil
            </button>
            <button onclick="showSection('pets')">
                <i class="fas fa-paw"></i> Meus Pets
            </button>
            <button onclick="showSection('config')">
                <i class="fas fa-cog"></i> Configurações
            </button>
        </div>

        <!-- Profile Section -->
        <div id="perfil" class="content-section active">
            <h2><i class="fas fa-id-card"></i> Informações do Perfil</h2>
            <div class="profile-info">
                <div class="info-item">
                    <strong><i class="fas fa-signature"></i> Nome Completo</strong>
                    <?= $_SESSION["nome"] ?>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-phone"></i> Telefone</strong>
                    <?= $_SESSION["telefone"] ?>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-envelope"></i> E-mail</strong>
                    <?= $_SESSION["email"] ?>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-birthday-cake"></i> Data de Nascimento</strong>
                    <?= $_SESSION["datanasc"] ?>
                </div>
                <div class="info-item">
                    <strong><i class="fas fa-venus-mars"></i> Gênero</strong>
                    <?= $_SESSION["genero"] ?>
                </div>
            </div>
        </div>

        <!-- Pets Section -->
        <div id="pets" class="content-section">
            <div class="flex-container">
                <!-- Add Pet Form -->
                <div class="form-container">
                    <div class="form-animal">
                        <h2><i class="fas fa-plus-circle"></i> Cadastrar Novo Pet</h2>
                        <p>Preencha os dados do seu animal de estimação.</p>

                        <form action="" method="post">
                            <label for="nome">Nome do Pet:</label>
                            <input type="text" name="nome" id="nome" required placeholder="Ex: Thor">

                            <label for="especie">Espécie:</label>
                            <select name="especie" id="especie" required>
                                <option value="">Selecione</option>
                                <?php
                                require '../conexao.php'; // Ajuste o caminho do seu arquivo de conexão
                                
                                try {
                                    $stmt = $pdo->query("SELECT DISTINCT nome FROM Especies ORDER BY nome ASC");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<option value='" . htmlspecialchars($row['nome']) . "'>" . htmlspecialchars($row['nome']) . "</option>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<option disabled>Erro ao carregar espécies</option>";
                                }
                                ?>
                            </select>


                            <label for="raca">Raça:</label>
                            <input type="text" name="raca" id="raca" placeholder="Ex: Golden Retriever">

                            <label for="datanasc">Data de Nascimento:</label>
                            <input type="date" name="datanasc" id="datanasc">

                            <div class="flex-container" style="gap: 10px; margin-top: 15px;">
                                <div style="flex: 1;">
                                    <label for="sexo">Sexo:</label>
                                    <select name="sexo" id="sexo">
                                        <option value="">Não definido</option>
                                        <option value="Macho">Macho</option>
                                        <option value="Fêmea">Fêmea</option>
                                    </select>
                                </div>
                                <div style="flex: 1;">
                                    <label for="porte">Porte:</label>
                                    <select name="porte" id="porte">
                                        <option value="">Não definido</option>
                                        <option value="Pequeno">Pequeno</option>
                                        <option value="Medio">Médio</option>
                                        <option value="Grande">Grande</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit"><i class="fas fa-save"></i> Cadastrar Pet</button>
                        </form>
                    </div>
                </div>

                <!-- Pets List -->
                <div class="pets-container">
                    <div class="form-animal">
                        <h2><i class="fas fa-paw"></i> Meus Pets</h2>

                        <div class="pets-list">
                            <?php if (!empty($animais)): ?>
                                <?php foreach ($animais as $animal): ?>
                                    <div class="pet-card">
                                        <div class="pet-icon">
                                            <?= $animal['especie'] == 'Cachorro' ? '🐶' :
                                                ($animal['especie'] == 'Gato' ? '🐱' :
                                                    ($animal['especie'] == 'Hamster' ? '🐹' :
                                                        ($animal['especie'] == 'Peixe' ? '🐠' : '🐾'))) 
                                            ?>
                                        </div>

                                        <div class="pet-info">
                                            <div class="pet-name"> <?= htmlspecialchars($animal['animal_nome']) ?> </div>
                                            <div class="pet-details">
                                                <span class="pet-detail">
                                                    <i class="fas fa-dog"></i> <?= htmlspecialchars($animal['especie']) ?>
                                                </span>
                                                <?php if ($animal['raca']): ?>
                                                    <span class="pet-detail">
                                                        <i class="fas fa-dna"></i> <?= htmlspecialchars($animal['raca']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($animal['sexo']): ?>
                                                    <span class="pet-detail">
                                                        <i class="fas fa-venus-mars"></i> <?= htmlspecialchars($animal['sexo']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if ($animal['porte']): ?>
                                                    <span class="pet-detail">
                                                        <i class="fas fa-weight-hanging"></i>
                                                        <?= htmlspecialchars($animal['porte']) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>

                                <div class="no-pets">
                                    <i class="fas fa-paw" style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;"></i>
                                    <p>Você ainda não cadastrou nenhum pet.</p>
                                </div>
                            <?php endif; ?>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Config Section -->
        <div id="config" class="content-section">
            <h2><i class="fas fa-cog"></i> Configurações da Conta</h2>

            <div class="config-form">
                <!-- Update Phone -->
                <div class="form-animal" style="margin-bottom: 20px;">
                    <h3><i class="fas fa-phone-alt"></i> Alterar Telefone</h3>
                    <form action="alterar_telefone.php" method="post">
                        <div class="form-group">
                            <label for="telefone">Novo Telefone:</label>
                            <input type="text" name="telefone" id="telefone" required>
                        </div>
                        <button type="submit"><i class="fas fa-sync-alt"></i> Atualizar Telefone</button>
                    </form>
                </div>

                <!-- Change Password -->
                <div class="form-animal" style="margin-bottom: 20px;">
                    <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
                    <form action="alterar_senha.php" method="post">
                        <div class="form-group">
                            <label for="senha_atual">Senha Atual:</label>
                            <input type="password" name="senha_atual" id="senha_atual" required>
                        </div>
                        <div class="form-group">
                            <label for="nova_senha">Nova Senha:</label>
                            <input type="password" name="nova_senha" id="nova_senha" required>
                        </div>
                        <div class="form-group">
                            <label for="confirmar_senha">Confirmar Nova Senha:</label>
                            <input type="password" name="confirmar_senha" id="confirmar_senha" required>
                        </div>
                        <button type="submit"><i class="fas fa-key"></i> Alterar Senha</button>
                    </form>
                </div>

                <!-- Logout -->
                <form action="../logout.php" method="post">
                    <button type="submit" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Sair da Conta</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });

            // Show selected section
            document.getElementById(sectionId).classList.add('active');

            // Update active button
            document.querySelectorAll('.profile-nav button').forEach(button => {
                button.classList.remove('active');
            });

            event.currentTarget.classList.add('active');
        }
    </script>

    <?php include '../menu.php'; ?>
</body>

</html>