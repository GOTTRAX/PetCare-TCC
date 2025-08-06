
Create database PetCare;
use PetCare;

CREATE TABLE Usuarios (
id INT auto_increment primary KEY,
nome varchar(100) NOT NULL,
cpf varchar(14) NOT NULL UNIQUE,
telefone varchar(15),
email varchar(100) NOT NULL UNIQUE,
senha_hash varchar(255) NOT NULL,
tipo_usuario ENUM('Cliente', 'Veterinario', 'Secretaria', 'Cuidador') DEFAULT 'Cliente',
genero ENUM('Masculino', 'Feminino', 'Outro') DEFAULT 'Outro',
tentativas INT DEFAULT 0,
datanasc DATE,
bloqueado_ate datetime DEFAULT NULL,
ultimo_login DATETIME DEFAULT NULL,
ativo BOOLEAN DEFAULT TRUE,
criado DATETIME DEFAULT CURRENT_TIMESTAMP,
atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
descricao text(244) null
);


CREATE TABLE Servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,           -- Nome do serviço (ex: Consulta, Vacina, Cirurgia)
    descricao TEXT,                       -- Descrição detalhada do serviço
    preco DECIMAL(10,2),                  -- Preço base (pode ser NULL se for variável)
    duracao_estimada INT,                 -- Duração em minutos (opcional)
    tipo ENUM('Consulta', 'Banho', 'Tosa', 'Cirurgia', 'Exame', 'Outro') DEFAULT 'Outro',
    ativo BOOLEAN DEFAULT TRUE,           -- Se o serviço ainda está em uso
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
ALTER TABLE Agendamentos ADD COLUMN servico_id INT NOT NULL;

ALTER TABLE Agendamentos
ADD FOREIGN KEY (servico_id) REFERENCES Servicos(id);

CREATE TABLE Equipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,  
    profissao VARCHAR(100),
    descricao TEXT,
    foto VARCHAR(255),

    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id)
);


CREATE TABLE Animais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    datanasc DATE,
    especie ENUM('Cachorro', 'Gato', 'Hamster', 'Peixe') NOT NULL,
    raca VARCHAR(80),
    porte enum('Pequeno', 'Medio', 'Grande'),
    sexo ENUM('Macho', 'Fêmea') DEFAULT NULL,
    usuario_id INT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id)
);
describe Agendamentos;
CREATE TABLE Agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    animal_id INT NOT NULL,
    veterinario_id INT,
    data_hora DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_final TIME NOT NULL,
    status ENUM('agendado', 'confirmado', 'cancelado') DEFAULT 'agendado',
    observacoes TEXT,

    FOREIGN KEY (cliente_id) REFERENCES Usuarios(id),
    FOREIGN KEY (animal_id) REFERENCES Animais(id),
    FOREIGN KEY (veterinario_id) REFERENCES Usuarios(id)
);
ALTER TABLE Consultas
    ADD COLUMN secretario_id INT NULL,
    ADD COLUMN status ENUM('pendente', 'aceita', 'recusada') DEFAULT 'pendente',
    ADD COLUMN mensagem TEXT NULL,
    MODIFY COLUMN data_consulta DATETIME NOT NULL,
    ADD CONSTRAINT fk_consultas_secretario_id FOREIGN KEY (secretario_id) REFERENCES Usuarios(id);

CREATE TABLE Consultas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agendamento_id INT UNIQUE NOT NULL,
    veterinario_id INT NOT NULL,
    data_consulta DATETIME DEFAULT CURRENT_TIMESTAMP,
    diagnostico TEXT,
    tratamento TEXT,
    receita TEXT,

    FOREIGN KEY (agendamento_id) REFERENCES Agendamentos(id),
    FOREIGN KEY (veterinario_id) REFERENCES Usuarios(id)
);

CREATE TABLE Prontuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    observacoes TEXT NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (consulta_id) REFERENCES Consultas(id)
);

CREATE TABLE Redef_Senha (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    expira_em DATETIME NOT NULL,
    usado_em DATETIME DEFAULT NULL,

    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id)
);

CREATE TABLE Logs_Acesso (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT,
    email_tentado VARCHAR(100),
    sucesso BOOLEAN NOT NULL,
    ip_origem VARCHAR(45),
    navegador TEXT,
    data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id)
);
    

