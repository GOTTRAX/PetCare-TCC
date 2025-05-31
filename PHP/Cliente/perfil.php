<?php
session_start();

// Redireciona se o usuário não estiver logado
if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}

include '../conexao.php'; // aqui você deve ter uma variável $pdo (objeto PDO)

// ======= CADASTRO DE ANIMAL =======
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome       = $_POST['nome'];
    $datanasc   = !empty($_POST['datanasc']) ? $_POST['datanasc'] : null;
    $especie    = $_POST['especie'];
    $raca       = !empty($_POST['raca']) ? $_POST['raca'] : null;
    $sexo       = !empty($_POST['sexo']) ? $_POST['sexo'] : null;
    $porte      = !empty($_POST['porte']) ? $_POST['porte'] : null;
    $usuario_id = $_SESSION["id"];

    try {
        $sql = "INSERT INTO Animais (nome, datanasc, especie, raca, porte, sexo, usuario_id)
                VALUES (:nome, :datanasc, :especie, :raca, :porte, :sexo, :usuario_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':datanasc' => $datanasc,
            ':especie' => $especie,
            ':raca' => $raca,
            ':porte' => $porte,
            ':sexo' => $sexo,
            ':usuario_id' => $usuario_id
        ]);

        echo "<p style='color: green;'>Animal cadastrado com sucesso!</p>";
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Erro ao cadastrar animal: " . $e->getMessage() . "</p>";
    }
}

// ======= LISTAGEM DE ANIMAIS POR DONO =======
$animais_por_dono = [];

try {
    $sql = "SELECT a.nome AS animal_nome, a.especie, a.raca, a.sexo, a.porte, u.nome AS dono_nome
            FROM Animais a
            JOIN Usuarios u ON a.usuario_id = u.id
            ORDER BY u.nome, a.nome";

    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($resultados as $row) {
        $dono = $row['dono_nome'];
        $animais_por_dono[$dono][] = $row;
    }
} catch (PDOException $e) {
    echo "Erro ao listar animais: " . $e->getMessage();
}
?>





<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minha Área</title>
    <link rel="stylesheet" href="../../CSS/styles.css">
</head>
<body>
    <br><br>
    <br><br>

    <div class="container">
        <h1>Minha Área</h1>
    <div class="avatar">
        <span>
            <?php
            $iniciais = '';
            $partes_nome = explode(' ', $dono);
            foreach ($partes_nome as $parte) {
            $iniciais .= strtoupper(mb_substr($parte, 0, 1));
            }
            echo $iniciais;
            ?>
        </span>
    </div>

    <div class="buttons">
        <button onclick="showSection('perfil')">Perfil</button>
        <button onclick="showSection('cadastrar-animal')">Animal</button>
        <button onclick="showSection('config')">Configurações</button>
    </div>

    <!-- Seção: Perfil -->
    <div id="perfil" class="content active">
        <h2>Perfil do Usuário</h2>
        <p><strong>Nome:               </strong> <?= $_SESSION["nome"] ?></p>
        <p><strong>Telefone:           </strong> <?= $_SESSION["telefone"] ?></p>
        <p><strong>E-mail:             </strong> <?= $_SESSION["email"] ?></p>
        <p><strong>Data de Nascimento: </strong> <?= $_SESSION["datanasc"] ?></p>
        <p><strong>Gênero:             </strong> <?= $_SESSION["genero"] ?></p>
    </div>

    <!-- Seção: Cadastro de Animal + Lista de Animais -->
    <div id="cadastrar-animal" class="content">
        <div class="flex-container">
            <!-- Formulário -->
            <div class="form-animal">
                <h2>Cadastrar Animal</h2>
                <span>Preencha os dados do animal que deseja cadastrar.<br>
                      Caso não saiba, pode deixar em branco.</span>

                <form action="" method="post">
                    <label for="nome">Nome:</label>
                    <input type="text" name="nome" id="nome" required placeholder="Exemplo: Destruidor de lares">

                    <label for="datanasc">Data de Nascimento:</label>
                    <input type="date" name="datanasc" id="datanasc">

                    <label for="especie">Espécie:</label>
                    <select name="especie" id="especie" required>
                        <option value="">Selecione</option>
                        <option value="Cachorro">Cachorro</option>
                        <option value="Gato">Gato</option>
                        <option value="Hamster">Hamster</option>
                        <option value="Peixe">Peixe</option>
                    </select>

                    <label for="raca">Raça:</label>
                    <input type="text" name="raca" id="raca" placeholder="Exemplo: Poodle">

                    <label for="sexo">Sexo:</label>
                    <select name="sexo" id="sexo">
                        <option value="">Não definido</option>
                        <option value="Macho">Macho</option>
                        <option value="Fêmea">Fêmea</option>
                    </select>

                    <label for="porte">Porte:</label>
                    <select name="porte" id="porte">
                        <option value="">Não definido</option>
                        <option value="Pequeno">Pequeno</option>
                        <option value="Medio">Médio</option>
                        <option value="Grande">Grande</option>
                    </select>

                    <button type="submit">Cadastrar</button>
                </form>
            </div>

            <!-- Lista de animais -->
            <div class="lista-animais">
                <h2>Animais Cadastrados</h2>
                <?php if (!empty($animais_por_dono)): ?>
                    <?php foreach ($animais_por_dono as $dono => $animais): ?>
                        <div class="dono-box">
                            <h3><?= htmlspecialchars($dono) ?></h3>
                            <ul>
                                <?php foreach ($animais as $animal): ?>
                                    <li>
                                        <strong>Nome:</strong> <?= htmlspecialchars($animal['animal_nome']) ?> |
                                        <strong>Espécie:</strong> <?= htmlspecialchars($animal['especie']) ?> |
                                        <strong>Raça:</strong> <?= htmlspecialchars($animal['raca']) ?> |
                                        <strong>Sexo:</strong> <?= htmlspecialchars($animal['sexo']) ?> |
                                        <strong>Porte:</strong> <?= htmlspecialchars($animal['porte']) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum animal cadastrado.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Seção: Configurações -->
    <div id="config" class="content">
        <h2>Configurações</h2>
        <p>Aqui você pode modificar suas preferências.</p>

        <!-- Atualizar telefone -->
        <form action="alterar_telefone.php" method="post">
            <label for="telefone">Novo Telefone:</label>
            <input type="text" name="telefone" id="telefone" required>
            <button type="submit">Atualizar Telefone</button>
        </form>

        <!-- Alterar senha -->
        <form action="alterar_senha.php" method="post">
            <label for="senha_atual">Senha Atual:</label>
            <input type="password" name="senha_atual" id="senha_atual" required>

            <label for="nova_senha">Nova Senha:</label>
            <input type="password" name="nova_senha" id="nova_senha" required>

            <label for="confirmar_senha">Confirmar Nova Senha:</label>
            <input type="password" name="confirmar_senha" id="confirmar_senha" required>

            <button type="submit">Alterar Senha</button>
        </form>

        <!-- Logout -->
        <form action="../logout.php" method="post">
            <button type="submit" class="btn-logout">Sair da Conta</button>
        </form>
    </div>
</div>

<!-- Scripts -->
<script>
    function showSection(sectionId) {
        document.querySelectorAll('.content').forEach(section => {
            section.classList.remove('active');
        });
        document.getElementById(sectionId).classList.add('active');
    }
</script>

<?php include '../menu.php'; ?>
<?php include '../footer.html'; ?>
<script src="../../JS/script.js" defer></script>

</body>
</html>
