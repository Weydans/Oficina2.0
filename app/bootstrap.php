<?php 

/**
 * Arquivo responsável pelo carregamento das principais dependências do projeto,
 * Responsável por direcionar as rotas do sistema aos seus respectivos controllers.
 * @author Weydans Campos de Barros, 18/04/2019.
 */

require_once('./core/config.php');
require_once('./app/config.php');
require_once('./core/class/Autoload.php');

Autoload::run();

$app = new Route();

var_dump($app); die;

$app->run();
