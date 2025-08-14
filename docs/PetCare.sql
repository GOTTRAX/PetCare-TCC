Create database PetCare;
use PetCare;

--  usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios =
select * from usuario;
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
-- usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios = usuarios =  usuarios = usuarios = usuarios = usuarios =



ALTER TABLE Agendamentos ADD COLUMN servico_id INT NOT NULL;
ALTER TABLE Agendamentos
ADD FOREIGN KEY (servico_id) REFERENCES Servicos(id);
alter table Equipe add column nome varchar(100) after id;
CREATE TABLE Equipe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,  
    profissao VARCHAR(100),
    descricao TEXT,
    foto VARCHAR(255),

    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id)
);
SELECT DISTINCT especie FROM Animais;



select *from usuarios;
select * from animais;

create table Especies(
id int AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(100) NOT NULL
);


CREATE TABLE Animais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    datanasc DATE,
    especie_id INT NOT NULL,
    raca VARCHAR(80),
    porte ENUM('Pequeno', 'Medio', 'Grande'),
    sexo ENUM('Macho', 'Fêmea') DEFAULT NULL,
    usuario_id INT NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (usuario_id) REFERENCES Usuarios(id),
    FOREIGN KEY (especie_id) REFERENCES Especies(id)
);



CREATE TABLE Agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    animal_id INT NOT NULL,
    veterinario_id INT,
    data_hora DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_final TIME NOT NULL,
    status ENUM('pendente', 'confirmado', 'cancelado') DEFAULT 'pendente',
    observacoes TEXT,

    FOREIGN KEY (cliente_id) REFERENCES Usuarios(id),
    FOREIGN KEY (animal_id) REFERENCES Animais(id),
    FOREIGN KEY (veterinario_id) REFERENCES Usuarios(id)
);


INSERT INTO Agendamentos 
(cliente_id, animal_id, veterinario_id, data_hora, hora_inicio, hora_final, status, observacoes)
VALUES
(1, 2, 2, '2025-08-15', '09:00:00', '09:30:00', 'pendente', 'Consulta de rotina para vacinação'),

(1, 2, 2, '2025-08-16', '14:00:00', '14:45:00', 'confirmado', 'Exame de retorno pós-cirurgia'),

(1, 2, 2, '2025-08-17', '10:00:00', '10:30:00', 'pendente', 'Avaliação de sintomas de apatia'),

(1, 2, 2, '2025-08-18', '15:00:00', '15:30:00', 'cancelado', 'Cliente solicitou cancelamento por viagem');

-- Ver todos os clientes
SELECT id, nome, tipo_usuario FROM Usuarios;
select * from Agendamentos;
-- Ver todos os animais
SELECT id, nome FROM Animais;

CREATE TABLE Consultas (
    id INT NOT NULL AUTO_INCREMENT,
    animal_id INT NOT NULL,
    agendamento_id INT NOT NULL UNIQUE,
    veterinario_id INT NOT NULL,
    secretario_id INT NULL,
    data_consulta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    diagnostico TEXT,
    tratamento TEXT,
    receita TEXT,
    mensagem TEXT NULL,

    PRIMARY KEY (id),

    CONSTRAINT fk_consultas_animal_id
        FOREIGN KEY (animal_id) REFERENCES Animais(id),
    CONSTRAINT fk_consultas_agendamento_id
        FOREIGN KEY (agendamento_id) REFERENCES Agendamentos(id),
    CONSTRAINT fk_consultas_veterinario_id
        FOREIGN KEY (veterinario_id) REFERENCES Usuarios(id),
    CONSTRAINT fk_consultas_secretario_id
        FOREIGN KEY (secretario_id) REFERENCES Usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE Prontuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    consulta_id INT NOT NULL,
    observacoes TEXT NOT NULL,
    data_registro DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (consulta_id) REFERENCES Consultas(id)
);


-- configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes =
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

CREATE TABLE Servicos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT NULL,
    preco_normal DECIMAL(10,2) NOT NULL,
    preco_feriado DECIMAL(10,2) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Dias_Trabalhados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    dia_semana ENUM('Segunda', 'Terça', 'Quarta', 'Quinta', 'Sexta', 'Sábado', 'Domingo') NOT NULL,
    horario_abertura TIME NOT NULL,
    horario_fechamento TIME NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Periodos_Inativos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    motivo VARCHAR(255) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE Feriados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    data DATE NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizado_em DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

 -- configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes = configuracoes =

