
/**
 * oficina
 * Banco de dados responsável pelo armazenamento,
 * manipulação e consulta de todos os dados da oficina.
 * @author Weydans Barros, 17/04/2019
 */

DROP DATABASE IF EXISTS oficina;
CREATE DATABASE IF NOT EXISTS oficina DEFAULT CHARACTER SET utf8 ;

USE oficina ;

DROP TABLE IF EXISTS orcamento;
CREATE TABLE IF NOT EXISTS orcamento (
  orcamento_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  orcamento_cliente VARCHAR(45) NOT NULL,
  orcamento_data_hora TIMESTAMP NOT NULL,
  orcamento_vendedor VARCHAR(45) NOT NULL,
  orcamento_descricao VARCHAR(255) NOT NULL,
  orcamento_valor INT(8) NOT NULL,
  PRIMARY KEY (orcamento_id),
  UNIQUE INDEX orcamento_id_UNIQUE (orcamento_id ASC)
  )
ENGINE = InnoDB;
