<?php
session_start();
include("conexao.php"); // deve definir a variável $pdo (não $conn)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome       = $_POST["nome"];
    $email      = $_POST["email"];
    $senha      = $_POST["senha"];
    $confSenha  = $_POST["confirmaSenha"];
    $telefone   = $_POST["telefone"];
    $datanasc   = $_POST["datanasc"];
    $cpf        = $_POST["cpf"]; 

    if ($senha !== $confSenha) {
        die("As senhas não coincidem.");
    }

    // Verificar se o e-mail já está cadastrado
    $verifica = $pdo->prepare("SELECT id FROM Usuarios WHERE email = ?");
    $verifica->execute([$email]);

    if ($verifica->rowCount() > 0) {
        die("Este e-mail já está cadastrado.");
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    // Inserir novo usuário
    $sql = "INSERT INTO Usuarios (nome, cpf, email, senha_hash, telefone, datanasc)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$nome, $cpf, $email, $senhaHash, $telefone, $datanasc])) {
        $_SESSION["usuario_id"] = $pdo->lastInsertId();  
        $_SESSION["usuario_email"] = $email;

        header("Location: Cliente/home.php");
        exit();
    } else {
        echo "Erro ao registrar: ";
        print_r($stmt->errorInfo());
    }
}
?>


<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <link rel="stylesheet" href="../CSS/login.css">
  
</head>
<body>

  <div class="login-card">
    <img src="https://st2.depositphotos.com/5056293/9389/v/450/depositphotos_93899252-stock-illustration-vector-sign-veterinary.jpg" alt="Logo" class="logo">
    
    <h2>Criar Conta</h2>
    
    <p>Preencha os campos abaixo para criar uma conta.</p>

    <form method="POST" action="">
      <div class="input-group">
        <label for="nome">Nome Completo</label>
        <input type="text" 
               id="nome" 
               name="nome" 
               placeholder="Seu nome completo" required>
      </div>

      <div class="input-group">
        <label for="cpf">CPF</label>
        <input type="text" 
               id="cpf" 
               name="cpf" 
               placeholder="000.000.000-00" required>
      </div>

      <div class="input-group">
        <label for="email">Email</label>
        <input type="email" 
               id="email" 
               name="email" 
               placeholder="seu@email.com" required>
      </div>

      <div class="row">
        <div class="input-group half-width">
          <label for="senha">Senha</label>
          <input type="password" 
                 id="senha" 
                 name="senha" 
                 placeholder="••••••" required>
          <span class="eye-icon" onclick="toggleSenha('senha')">
            <i class="fa fa-eye"></i>
          </span>
        </div>

        <div class="input-group half-width">
          <label for="confirmar">Confirmar Senha</label>
          <input type="password" 
                 id="confirmar" 
                 name="confirmaSenha" 
                 placeholder="••••••" required>
          <span class="eye-icon" onclick="toggleSenha('confirmar')">
            <i class="fa fa-eye"></i>
          </span>
        </div>

      </div>

      <div class="row">
        <div class="input-group half-width">
          <label for="telefone">Telefone</label>
          <input type="tel" 
                 id="telefone" 
                 name="telefone" 
                 placeholder="(xx) xxxxx-xxxx" required>
        </div>

        <div class="input-group half-width">
          <label for="datanasc">Data de Nascimento</label>
          <input type="date"   
                 id="datanasc" 
                 name="datanasc" required>
        </div>
      </div>

      <button type="submit" class="btn">Registrar <span>&rarr;</span></button>

      <div class="register-link"> Já tem uma conta? <a href="../index.php">Faça login</a> </div>

      <footer>© 2023 PetCare Clínica Veterinária. Todos os direitos reservados.</footer>

    </form>

  </div>

  <script>
    function toggleSenha(id) {
      const input = document.getElementById(id);
      input.type = input.type === "password" ? "text" : "password";
    }
  </script>
</body>
</html>