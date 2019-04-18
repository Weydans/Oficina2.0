<?php

/**
 * Arquivo de configuração do projeto,
 * Responsável por definir padrões de banco de dados, constantes do sistema e etc.
 * @author Weydans Campos de Barros, 18/04/2019.
 */

// CONFIGURAÇÃO GERAL DO SISTEMA
define('HOME', 'http://oficina2.0.com/');

// CONFIGURAÇÃO DO BANCO DE DADOS
define('DSN', 'mysql:host=localhost;dbname=oficina');
define('USER', 'root');
define('PASS', '');
define('OPTION', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8' ]);
				   