CREATE DATABASE IF NOT EXISTS biblioteca
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE biblioteca;

CREATE TABLE IF NOT EXISTS leitores (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(180) NOT NULL,
    UNIQUE KEY uq_leitores_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS livros (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(200) NOT NULL,
    autor VARCHAR(150) NOT NULL,
    ano SMALLINT UNSIGNED NOT NULL,
    quantidade INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS emprestimos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    livro_id INT UNSIGNED NOT NULL,
    leitor_id INT UNSIGNED NOT NULL,
    data_emprestimo DATE NOT NULL,
    data_devolucao DATE NULL,
    CONSTRAINT fk_emprestimos_livros
      FOREIGN KEY (livro_id) REFERENCES livros(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_emprestimos_leitores
      FOREIGN KEY (leitor_id) REFERENCES leitores(id)
      ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_emprestimos_livro_aberto (livro_id, data_devolucao),
    INDEX idx_emprestimos_leitor_aberto (leitor_id, data_devolucao)
) ENGINE=InnoDB;
