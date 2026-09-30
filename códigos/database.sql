CREATE DATABASE IF NOT EXISTS kanban_startup CHARACTER SET utf8mb4;
USE kanban_startup;
CREATE TABLE empresas(id_empresa INT AUTO_INCREMENT PRIMARY KEY,nome_empresa VARCHAR(120) NOT NULL UNIQUE,data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE usuarios(id_usuario INT AUTO_INCREMENT PRIMARY KEY,id_empresa INT NOT NULL,nome VARCHAR(100) NOT NULL,email VARCHAR(120) NOT NULL UNIQUE,senha_hash VARCHAR(255) NOT NULL,
 tipo_usuario ENUM('admin','empresario','patrao') NOT NULL DEFAULT 'empresario',status ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
 data_cadastro DATETIME DEFAULT CURRENT_TIMESTAMP,ultimo_acesso DATETIME NULL,FOREIGN KEY(id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE);
CREATE TABLE status_tarefas(id_status INT AUTO_INCREMENT PRIMARY KEY,id_empresa INT NOT NULL,nome_status VARCHAR(50) NOT NULL,ordem INT NOT NULL,limite_wip INT NULL,
 tipo ENUM('inicio','andamento','fim') NOT NULL,FOREIGN KEY(id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE);
CREATE TABLE tarefas(id_tarefa INT AUTO_INCREMENT PRIMARY KEY,id_empresa INT NOT NULL,id_usuario INT NOT NULL,titulo VARCHAR(150) NOT NULL,descricao TEXT,
 prioridade ENUM('baixa','media','alta') NOT NULL DEFAULT 'media',id_status INT NOT NULL,data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
 data_inicio DATETIME NULL,data_conclusao DATETIME NULL,lead_time INT NULL COMMENT 'segundos',
 FOREIGN KEY(id_empresa) REFERENCES empresas(id_empresa) ON DELETE CASCADE,FOREIGN KEY(id_usuario) REFERENCES usuarios(id_usuario),FOREIGN KEY(id_status) REFERENCES status_tarefas(id_status));
CREATE TABLE historico_movimentacao(id_movimentacao INT AUTO_INCREMENT PRIMARY KEY,id_tarefa INT NOT NULL,id_usuario INT NULL,status_origem INT NULL,status_destino INT NOT NULL,
 data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(id_tarefa) REFERENCES tarefas(id_tarefa) ON DELETE CASCADE);
