<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Minha Área</title>
    <link rel="stylesheet" href="../CSS/styles.css">
    <style>
        .buttons button {
    background-color: #00796B; 
    color: white;
    border: none;
    padding: 12px 24px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.2s;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.buttons button:hover {
    background-color: #004D40; 
    transform: scale(1.05);
}

.buttons button:focus {
    outline: 2px solid #004D40;
    outline-offset: 3px;
}

#perfil {
    background-color: #ffffff;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 500px;
    margin: 40px auto;
    text-align: left;
    animation: fadeIn 0.6s ease-in-out;
     border-left: 6px solid #3498db;
}

#perfil h2 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 20px;
    border-bottom: 2px solid #3498db;
    padding-bottom: 10px;
}

#perfil p {
    font-size: 16px;
    margin-bottom: 15px;
    color: #34495e;
    line-height: 1.6;
}

#perfil p strong {
    color: #2980b9;
    font-weight: 600;
}

/* Animação suave na entrada */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(15px); }
    to   { opacity: 1; transform: translateY(0); }
}

#animal {
    background-color: #ffffff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 550px;
    margin: 40px auto;
    text-align: left;
    animation: fadeIn 0.6s ease-in-out;
    border-left: 6px solid #3498db;
}

#animal h2 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 10px;
}

#animal span {
    font-size: 14px;
    color: #7f8c8d;
    display: block;
    margin-bottom: 25px;
    line-height: 1.5;
}

#animal form {
    display: flex;
    flex-direction: column;
}

#animal label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #34495e;
    margin-top: 15px;
}

#animal input,
#animal select {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    transition: border-color 0.3s ease;
}

#animal input:focus,
#animal select:focus {
    border-color: #3498db;
    outline: none;
}

#animal button {
    margin-top: 25px;
    padding: 14px;
    background-color: #3498db;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#animal button:hover {
    background-color: #2980b9;
}

/* Animação de entrada */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}


#config {
    background-color: #ffffff;
    padding: 35px;
    border-radius: 12px;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
    width: 100%;
    max-width: 550px;
    margin: 40px auto;
    text-align: left;
    animation: fadeIn 0.6s ease-in-out;
}

#config h2 {
    font-size: 24px;
    color: #2c3e50;
    margin-bottom: 10px;
}

#config p {
    font-size: 14px;
    color: #7f8c8d;
    margin-bottom: 25px;
}

#config form {
    display: flex;
    flex-direction: column;
    margin-bottom: 30px;
}

#config label {
    font-weight: 600;
    margin-bottom: 6px;
    color: #34495e;
    margin-top: 15px;
}

#config input {
    padding: 12px;
    border-radius: 8px;
    border: 1px solid #ccc;
    font-size: 14px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    transition: border-color 0.3s ease;
}

#config input:focus {
    border-color: #3498db;
    outline: none;
}

#config button {
    margin-top: 20px;
    padding: 14px;
    background-color: #3498db;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

#config button:hover {
    background-color: #2980b9;
}

.btn-logout {
    background-color: #e74c3c;
}

.btn-logout:hover {
    background-color: #c0392b;
}

    </style>
</head>
<body>

    <?php include 'menu.php'; ?>
    <br><br><br><br>

    <div class="container">
        <h1>Minha Área</h1>
        <div class="buttons">
            <button onclick="showSection('perfil')">Perfil</button>
            <button onclick="showSection('animal')">Animal</button>
            <button onclick="showSection('config')">Configurações</button>
        </div>
        
        <div id="perfil" class="content active">
            <h2>Perfil do Usuário</h2>
            <p><strong>Nome:               </strong> <?php echo $_SESSION["nome"];     ?></p>
            <p><strong>Telefone:           </strong> <?php echo $_SESSION["telefone"]; ?></p>
            <p><strong>E-mail:             </strong> <?php echo $_SESSION["email"];    ?></p>
            <p><strong>Data de Nascimento: </strong> <?php echo $_SESSION["datanasc"]; ?></p>
            <p><strong>Gênero:             </strong> <?php echo $_SESSION["genero"];   ?></p>
        </div>

        <div id="animal" class="content">
            <h2>Cadastrar Animal</h2>
            <span>Preencha os dados do animal que deseja cadastrar.<br>
                       caso nao saiba, pode deixar em branco      </span>
            <form action="cadastro_animal.php" method="post">

                <label for="nome">Nome:</label>
                <input type="text" 
                       name="nome" 
                       id="nome" required>

                <label for="especie">Espécie:</label>
                <input type="text"
                       name="especie" 
                       id="especie" required>

                <label for="raca">Raça: </label>
                <input type="text" 
                       name="raca" 
                       id="raca" required>

                <label for="Sexo"> Sexo: </label>
                <select name="sexo" id="sexo" required>
                    <option value="Macho">Macho</option>
                    <option value="Fêmea">Fêmea</option>
                </select>

                <label for="idade">Idade:</label>
                <input type="number" 
                       name="idade" 
                       id="idade" required>

                <label for="peso">Peso:</label>
                <input type="number" 
                       name="peso" 
                       id="peso" required>
                
                <label for="porte">Porte:</label>
                <select name="porte" id="porte" required>
                    <option value="Pequeno">Pequeno</option>
                    <option value="Médio">Médio</option>
                    <option value="Grande">Grande</option>
                </select>
                <button type="submit">Cadastrar</button>
            </form>
        </div>

        <div id="config" class="content">
    <h2>Configurações</h2>
    <p>Aqui você pode modificar suas preferências.</p>

    <form action="alterar_telefone.php" method="post">
        <label for="telefone">Novo Telefone:</label>
        <input type="text" name="telefone" id="telefone" required>
        <button type="submit">Atualizar Telefone</button>
    </form>

    <form action="alterar_senha.php" method="post">
        <label for="senha_atual">Senha Atual:</label>
        <input type="password" name="senha_atual" id="senha_atual" required>

        <label for="nova_senha">Nova Senha:</label>
        <input type="password" name="nova_senha" id="nova_senha" required>

        <label for="confirmar_senha">Confirmar Nova Senha:</label>
        <input type="password" name="confirmar_senha" id="confirmar_senha" required>

        <button type="submit">Alterar Senha</button>
    </form>

    <form action="logout.php" method="post">
        <button type="submit" class="btn-logout">Sair da Conta</button>
    </form>
</div>

    </div>

    <script>
        function showSection(sectionId) {
            document.querySelectorAll('.content').forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }
    </script>
</body>
</html>
