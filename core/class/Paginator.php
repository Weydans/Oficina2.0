<?php 

/**
 * <b>Paginator</b>:
 * Classe responsável por realizar paginação de resultados dinamicamente
 * E exibição do paginador padrão, permitindo também a personalização do mesmo.
 * @author Weydans Campos de Barros, 19/04/2019.
 */
class Paginator
{	
	private $url;
	private $page;
	private $resPerPage;
	private $numPages;
	private $totalResults;
	private $paramBusca;

	private $obj;
	private $tabela;
	private $campos;
	private $condicao;
	private $values;
	private $limit;
	private $offset;
	private $ordenacao;

	private $allResults;
	private $result;

	private $view;
	private $styleClass;

	/**
	 * <b>Paginator</b>:
	 * Responsável pela configuração dos atributos principais do objeto.
	 * @param string $url Recebe a url base para a paginação ( ex: 'http://oficina2.0.com/lista?page=' ).
	 * @param int $page Recebe o valor da variavel querystring que recebe a pagina atual.
	 * @param object $obj Recebe um objeto que está sendo manipulado para paginação.
	 * @param int $page Recebe o número de resultados que serão exibidos por pagina.
	 * @param string $paramBosca Querystring contendo os valores da pesquisa.
	 */
	public function __construct(string $url, $page, object $obj, int $resPerPage, string $paramBusca = null)
	{ 
		$this->url = $url;
		$this->obj = $obj;
		$this->tabela = $obj->getTable();
		$this->resPerPage = $resPerPage;
		$this->page = $page;
		$this->paramBusca = $paramBusca;
	}

	/**
	 * <b>exePaginator</b>:
	 * Ordena e centraliza a execução dos métodos responsáveis pela paginação.
	 * Obs: os links das condições devem ser compátiveis com as chaves do array $values, 
	 * porem, com os valores precedidos de ':' (dois pontos);
	 * @param array $campos Recebe os campos informados pelo programador.
	 * @param string $condicao Recebe a condição de leitura no banco.
	 * @param array $values Recebe um array associativo noqual as chaves devem ser identicas
	 * aos links da query, porem sem serem precedidos de ':' (dois pontos).
	 * @return array Retorna uma matriz contendo um array associativo com os resultados;
	 */
	public function exePaginator(array $campos = array(), string $condicao = null, array $values = array(), string $ordenacao = null) : array
	{
		$this->ordenacao = $ordenacao;

		$this->setParamBusca();
		$this->setCampos($campos);
		$this->setValues($values);
		$this->setCondicao($condicao);
		$this->setTotalResults();
		$this->setNumPages();
		$this->setPage($this->page);	

		$this->pager();
		//$this->read();
		$this->setResultBusca();

		return $this->result;
	}

	/**
	 * <b>exePaginator</b>:
	 * Ordena e centraliza a execução dos métodos responsáveis pela paginação.
	 * Obs: os links das condições devem ser compátiveis com as chaves do array $values, 
	 * porem, com os valores precedidos de ':' (dois pontos);
	 * @param array $campos Recebe os campos informados pelo programador.
	 * @param string $condicao Recebe a condição de leitura no banco.
	 * @param array $values Recebe um array associativo noqual as chaves devem ser identicas
	 * aos links da query, porem sem serem precedidos de ':' (dois pontos).
	 * @return array Retorna uma matriz contendo um array associativo com os resultados;
	 */
	public function exeBusca(array $campos = array(), string $condicao = null, array $values = array(), string $ordenacao = null) : array
	{
		$this->ordenacao = $ordenacao;

		$this->setParamBusca();
		$this->setCampos($campos);
		$this->setValues($values);
		$this->setCondicao($condicao);
		$this->setTotalResults();
		$this->setNumPages();
		$this->setPage();	

		$this->pager();
		//$this->read();

		$this->setResultBusca();

		return $this->result;
	}

	/**
	 * <b>getPaginator</b>:
	 * Cria e configura o paginador dinâmicamente a ser exibido.
	 * @param string $first Recebe o primeiro link personalizado.
	 * @param string $first Recebe o último link personalizado.
	 * @param int $numLinks Recebe o número máximo de links do paginador.
	 * @return string Retorna o HTML do paginator para exibição.
	 */
	public function getPaginator(string $first = null, string $last = null, int $numLinks = null) : string
	{
		$first  = (!empty($first)) ? $first : $first = "Primeira Página";
		$last = (!empty($last)) ? $last : $last = "Última Página";
		$numLinks = (!empty($numLinks)) ? $numLinks : $numLinks = 5;

		$this->view = "<ul class='paginator {$this->styleClass}'>";
		$this->view .= "<a href='{$this->url}1{$this->paramBusca}'><li><span>{$first}<span></li></a>";

		for ($i = $this->page - $numLinks + 1; $i < $this->page  ; $i++) { 
			if ($i > 0)
				$this->view .= "<a href='{$this->url}{$i}{$this->paramBusca}'><li>{$i}</li></a>";			
		}

		$this->view .= "<li class='active'>{$i}</li>";

		for ($i = $this->page; $i < $this->page + $numLinks ; $i++) { 
			if ($i > $this->page && $i <= $this->numPages)
				$this->view .= "<a href='{$this->url}{$i}{$this->paramBusca}'><li>{$i}</li></a>";
		}

		$this->view .= "<a href='{$this->url}{$this->numPages}{$this->paramBusca}'><li><span>{$last}<span></li></a>";
		$this->view .= "</ul>";

		return $this->view;
	}

	/**
	 * <b>getAllResults</b>
	 * @return array Retorna uma matraiz de arrays associativos com todos os resultados. 
	 */
	public function getAllResults()
	{
		return $this->allResults;
	}

	/**
	 * <b>getTotalResults</b> 
	 * @return int Retorna o total de registros encontrados.
	 */
	public function getTotalResults()
	{
		return $this->totalResults;
	}

	/**
	 * <b>setParamBusca</b>
	 * Configura o atributo $this->paramBusca 
	 * como uma string vazia caso nao seja informado nenhum valor.
	 */
	private function setParamBusca()
	{
		if (empty($this->paramBusca)){
			$this->paramBusca = '';
		}
	}

	/**
	 * <b>setStyleClass</b>:
	 * Permite ao programador informar uma classe css para estilização personalizada.
	 * @param string $styleClass Nome da classe no arquivo de estilo CSS.
	 */
	public function setStyleClass(string $styleClass)
	{
		$this->styleClass = $styleClass;
	}

	/**
	 * <b>setPage</b>
	 * Cnfigura o atributo $this->page.
	 * @param int $page Valor a ser validado para atributo $this->page.
	 */
	private function setPage()
	{
		if (empty($this->page) || !is_numeric($this->page)){
			$url = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			$this->page = 1;

		} elseif ($this->page > $this->numPages){ 
			$this->page = $this->numPages;
			header("Location: {$this->url}{$this->numPages}");

		} else {
			if ($this->page > 0){
				$this->page = $this->page;
			} else {
				$this->page = 1;
			}
		}
	}

	/**
	 * <b>setCampos</b>:
	 * Permite que o programador selecione apenas os campos de interece na paginação.
	 * @param array $campos Recebe um array com o nome dos campos pesquisados.
	 */
	private function setCampos(array $campos = null)
	{
		if ($campos){
			$this->campos = implode(', ', $campos);
		} else {
			$this->campos = '*';
		}
	}

	/**
	 * <b>setCondicao</b>:
	 * Configura as condições para leitura da tabela no banco de dados.
	 * Obs: os links das condições devem ser compátiveis com as chaves do array $values, 
	 * porem, com os valores precedidos de ':' (dois pontos) tornando-os links da query.
	 * @param string $condicao Recebe a condição de leitura no banco.
	 */
	private function setCondicao(string $condicao = null)
	{
		if (empty($condicao)){
			$this->condicao = '';
		} else {
			$this->condicao = $condicao;
		}
	}

	/**
	 * <b>setTotalResults</b>:
	 * Configura o total de resultados encontrados na paginação. 
	 */
	private function setTotalResults()
	{
		try {			
			$sql = new sql;
			$this->allResults = $sql->select("SELECT {$this->campos} FROM {$this->tabela} {$this->condicao} {$this->ordenacao}", $this->values);
			$this->totalResults = count($this->allResults);

		} catch (Exception $e) {
			echo Msg::setMsg( ($e->getMessage() . ' :: ' . $e->getFile() . ' :: ' . $e->getLine()) , ERROR);
		}
	}

	/**
	 * <b>setNumPages</b>:
	 * Configura o total de paginas da paginação. 
	 */
	private function setNumPages()
	{
		$this->numPages = ceil($this->totalResults / $this->resPerPage);
	}

	/**
	 * <b>setValues</b>
	 * Prepara os valores que vão substituir os links da query do prepared statement,
	 * acrecenta ':'(dois pontos) as chaves do array para substituição por links.
	 * @param array $values Recebe um array associativo no qual as chaves devem ser identicas
	 * aos links da query mas com as chaves sem serem precedidas por ':'(dois pontos).
	 */
	private function setValues(array $values = array())
	{
		if (count($values) < 1){
			$this->values = array();
		} else {
			$this->values = $this->prepareData($values);
		}
	}

	/**
	 * <b>pager</b>:
	 * Configura a página, o limite e o offset.
	 */
	private function pager()
	{
		$this->limit = $this->resPerPage;

		if ($this->page == null){
			$this->offset = 0;

		} elseif (is_numeric($this->page)){
			$this->page = ceil($this->page);
			$this->offset = (($this->page * $this->resPerPage) - $this->resPerPage);
		}

	}
	
 	/**
 	 * <b>setResultBusca</b>:
 	 * Configura o resultado da busca de acordo com o resultado da paginação. 
 	 */
 	private function setResultBusca()
 	{		
 		$i = $this->offset;
 		$this->result = array();

 		if (is_array($this->allResults)){
 			foreach ($this->allResults as $key => $value) {
 				if ($key >= $this->offset && $key < $this->offset + $this->limit){
 					$this->result[] = $this->allResults[$i];
 					$i++;		
 				}
 			}
 		}
 	}

	/**
     * <b>prepareData</b>:
     * Prepara o array data para substituição de links do prepared statements.
     * @param array $data Recebe os dados.
     * @return array Retorna dados preparados.
     */
	public function prepareData(array $data)
	{
		$result = [];

		foreach ($data as $key => $value) {
			$result += [':'. $key => $value];
		}

		return $result;
	}

}

