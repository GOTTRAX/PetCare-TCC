<?php
session_start();
if (!isset($_SESSION["id"])) {
    header("Location: ../index.php"); 
    exit();
} 
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>PetCare - Clínica Veterinária</title>

    <link rel="icon" type="image/png" href="https://img.icons8.com/ios/452/cat.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../CSS/styles.css">
</head>

<body>
    <section class="hero">
        <div class="hero-text">
            <span class="tag">Clínica Veterinária PetCare</span>
            <h1>Cuidado <span class="highlight">especializado</span> 
            para seu melhor amigo</h1> <p>Oferecemos atendimento
            veterinário de qualidade com uma equipe de profissionais 
            dedicados ao bem-estar do seu pet.</p>

            <div class="buttons">
                <a href="../Teste/tcc-pet-main/html/consultas.html" class="btn primary">Agendar Consulta</a>
                <a href="#" class="btn secondary">Contato</a>
            </div>

        </div>
        <div class="image-container">
            <img src="https://images.pexels.com/photos/6235225/pexels-photo-6235225.jpeg" 
                 alt="Imagem principal">
        </div>
    </section>

    <section id="servicos" class="servicos">
        <div class="container">
            <span class="tag">Nossos Serviços</span>
            <h2>Serviços especializados para seu pet</h2>
            <p>Oferecemos uma ampla gama de serviços veterinários de alta qualidade para garantir a saúde e o bem-estar do seu animal de estimação.</p>
            
            <div class="grid">
                <div class="card">
                    <div class="icon">🩺</div>
                    <h3>Consultas</h3>
                    <p>Atendimento clínico com profissionais especializados para cuidar da saúde do seu pet.</p>
                </div>
                <div class="card">
                    <div class="icon">✂️</div>
                    <h3>Banho & Tosa</h3>
                    <p>Serviços de estética completos realizados por profissionais capacitados.</p>
                </div>
                <div class="card">
                    <div class="icon">💊</div>
                    <h3>Farmácia</h3>
                    <p>Medicamentos e produtos de qualidade para o tratamento e bem-estar animal.</p>
                </div>
                <div class="card">
                    <div class="icon">💉</div>
                    <h3>Vacinação</h3>
                    <p>Programa de imunização completo para prevenir doenças e proteger seu pet.</p>
                </div>
                <div class="card">
                    <div class="icon">🔬</div>
                    <h3>Exames</h3>
                    <p>Laboratório completo para diagnósticos precisos e rápidos.</p>
                </div>
                <div class="card">
                    <div class="icon">📋</div>
                    <h3>Cirurgias</h3>
                    <p>Centro cirúrgico equipado para procedimentos simples e complexos.</p>
                </div>
            </div>
        </div>
    </section>
    <?php include 'menu.php'; ?>
    <?php include 'footer.html'; ?>
    <p></p>
    <p></p>
    <script src="../JS/script.js" defer></script>
</body>
</html>



