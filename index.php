<?php
session_start();
include("PHP/conexao.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = $_POST["email"];
    $password = $_POST["password"];

    $sql = "SELECT id,
                   nome,
                   telefone,
                   email,
                   senha_hash,
                   datanasc,
                   genero,
                   tipo_usuario FROM Usuarios WHERE email = ?";
                   
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        if (password_verify($password, $usuario["senha_hash"])) {
            $_SESSION["id"]           = $usuario["id"];
            $_SESSION["nome"]         = $usuario["nome"];
            $_SESSION["telefone"]     = $usuario["telefone"];
            $_SESSION["email"]        = $usuario["email"];
            $_SESSION["datanasc"]     = $usuario["datanasc"];
            $_SESSION["genero"]       = $usuario["genero"];
            $_SESSION["tipo_usuario"] = $usuario["tipo_usuario"];

            // Atualiza a data/hora do último login
            $updateLogin = $conn->prepare("UPDATE Usuarios SET ultimo_login = NOW() WHERE id = ?");
            $updateLogin->bind_param("i", $usuario["id"]);
            $updateLogin->execute();
            $updateLogin->close();

            // Redirecionamento baseado no tipo de usuário
            switch ($usuario["tipo_usuario"]) {
                case "Veterinario":
                    header("Location: PHP/Vet/vet_home.html");
                    break;
                case "Secretaria":
                    header("Location: PHP/Secretaria/home.php");
                    break;
                case "Cuidador":
                    header("Location: PHP/Cuidadores/home.php");
                    break;
                default:
                    header("Location: PHP/Cliente/home.php");
                    break;
            }
            exit();

        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PetCare Clínica Veterinária</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="CSS/login.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"> <!-- Font Awesome -->
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <img src="assets/depositphotos_93899252-stock-illustration-vector-sign-veterinary.jpg" alt="Logo" class="logo">
            <h2>Acesso ao Sistema</h2>
            <p>Faça login para acessar sua conta</p>
    
            <form method="POST" action="">
                <div class="input-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="seu@email.com" required>
                    <span class="eye-icon" id="email-toggle">
                        <i class="fa fa-envelope"></i> 
                    </span>
                </div>
    
                <div class="input-group">
                    <label for="password">Senha</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="******" required>
                    <span class="eye-icon" id="password-toggle">
                        <i class="fa fa-eye"></i> 
                    </span>
                </div>
    
                <div class="options">
                    <label>
                        <input type="checkbox" name="remember">
                        Lembrar-me
                    </label>
                    <a href="PHP/esqueci-senha.html" class="forgot-password">Esqueceu a senha?</a> 
                </div>
    
                <button type="submit" class="btn">
                    Entrar <span>&rarr;</span>
                </button>
            </form>
    
            <div class="register-link">
                Ainda não tem uma conta? <a href="PHP/registro.php" rel="next">Registre-se</a>
            </div>
    
            <footer>© 2023 PetCare Clínica Veterinária. Todos os direitos reservados.</footer>
        </div>
    </div>

    <script src="../JS/script.js"></script>
</body>
</html>
