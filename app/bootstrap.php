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

$app->get('/', function(){
	$home = new OrcamentoController;
	$home->pageForm();
});

$app->post('/cadastro', function(){
	$cadastro = new OrcamentoController;
	$cadastro->cadastrar();
});

$app->get('/cadastro', function(){
	$home = new OrcamentoController;
	$home->pageForm();
});

$app->post('/atualizacao', function(){
	$atualizacao = new OrcamentoController;
	$atualizacao->editar();
});

$app->get('/{id}/atualizacao', function($id){
	$atualizacao = new OrcamentoController;
	$atualizacao->editar($id);
});

$app->get('/lista', function(){
	$lista = new OrcamentoController;
	$lista->listar();
});

$app->get('/{id}/exclusao', function($id){
	$lista = new OrcamentoController;
	$lista->excluir($id);
});

$app->run();
