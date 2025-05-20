// API Para Completar CEP
/*document.addEventListener("DOMContentLoaded", function () {
    document.querySelector("input[name='cep']").addEventListener("blur", function () {
        let cep = this.value.replace(/\D/g, ''); // Remove caracteres não numéricos

        if (cep.length !== 8) {
            alert("CEP inválido! Digite um CEP com 8 números.");
            return;
        }

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
            .then(response => response.json())
            .then(data => {
                if (data.erro) {
                    alert("CEP não encontrado!");
                    return;
                }

                document.querySelector("input[name='endereco']").value  = data.logradouro || "";
                document.querySelector("input[name='bairro']").value    = data.bairro || "";
                document.querySelector("input[name='cidade']").value    = data.localidade || "";
                document.querySelector("input[name='estado']").value    = data.uf || "";
            })
            .catch(error => console.error('Erro ao buscar o CEP:', error));
    });
});*/  

// Imagem 
const heroImage = document.querySelector(".hero-image img");

function adjustImageSize() {
    const screenWidth = window.innerWidth;

    if (screenWidth < 768) {
        heroImage.style.maxWidth = "80%";
        heroImage.style.width    = "auto";
    } else {
        heroImage.style.maxWidth = "500px";
        heroImage.style.width    = "100%";
    }
}
// Lupa de busca
window.addEventListener("load"  , adjustImageSize);
window.addEventListener("resize", adjustImageSize);

const search    = document.querySelector('.search');
const btnsearch = document.querySelector('.btn-search');
const input     = document.querySelector('.input');

btnsearch.addEventListener('click', () => {
    search.classList.toggle('active');
    input.focus();
});


//login
document.addEventListener("DOMContentLoaded", function() {
    // Selecionando os elementos dos ícones e campos de senha
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm-password');
    const passwordToggle = document.getElementById('password-toggle');
    const confirmPasswordToggle = document.getElementById('confirm-password-toggle');
    
    // Função para alternar a visibilidade da senha
    passwordToggle.addEventListener('click', function() {
        if (password.type === "password") {
            password.type = "text"; // Mostra a senha
            passwordToggle.innerHTML = '<i class="fa fa-eye-slash"></i>'; // Ícone de olho fechado
        } else {
            password.type = "password"; // Oculta a senha
            passwordToggle.innerHTML = '<i class="fa fa-eye"></i>'; // Ícone de olho aberto
        }
    });
    
    // Função para alternar a visibilidade da confirmação da senha
    confirmPasswordToggle.addEventListener('click', function() {
        if (confirmPassword.type === "password") {
            confirmPassword.type = "text"; // Mostra a senha
            confirmPasswordToggle.innerHTML = '<i class="fa fa-eye-slash"></i>'; // Ícone de olho fechado
        } else {
            confirmPassword.type = "password"; // Oculta a senha
            confirmPasswordToggle.innerHTML = '<i class="fa fa-eye"></i>'; // Ícone de olho aberto
        }
    });
});