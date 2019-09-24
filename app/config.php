<?php

/**
 * Arquivo de configuração do projeto,
 * Responsável por definir padrões de banco de dados, constantes do sistema e etc.
 * @author Weydans Campos de Barros, 18/04/2019.
 */

// CONFIGURAÇÃO GERAL DO SISTEMA
// define('HOME', 'http://' . $_SERVER['HTTP_HOST'] . '/weydans/projetos/Oficina2.0');
define('HOME', 'http://'. $_SERVER['SERVER_NAME'] .'/weydans/projetos/Oficina2.0');
define('BASE_URL', '/weydans/projetos/Oficina2.0');

// CONFIGURAÇÃO DO BANCO DE DADOS
define('DSN', 'mysql:host=localhost;dbname=oficina');
define('USER', 'root');
define('PASS', '');
define('OPTIONS', [ PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8' ]);
				   