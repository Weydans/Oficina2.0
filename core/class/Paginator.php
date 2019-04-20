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

	private $obj;
	private $tabela;
	private $campos;
	private $condicao;
	private $values;
	private $limit;
	private $offset;

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
	 */
	public function __construct(string $url, int $page, object $obj, int $resPerPage)
	{ 
		$this->url = $url;
		$this->obj = $obj;
		$this->tabela = $obj->getTable();
		$this->resPerPage = $resPerPage;
		$this->totalResults = count($obj->listAll());
		$this->numPages = ceil($this->totalResults / $resPerPage);

		$this->setPage($page);		
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
	public function exePaginator(array $campos = array(), string $condicao = null, array $values = array()) : array
	{
		$this->pager();
		$this->setCampos($campos);
		$this->setCondicao($condicao);
		$this->setValues($values);
		$this->read();

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
		$this->view .= "<a href='{$this->url}1'><li><span>{$first}<span></li></a>";

		for ($i = $this->page - $numLinks + 1; $i < $this->page  ; $i++) { 
			if ($i > 0)
				$this->view .= "<a href='{$this->url}{$i}'><li>{$i}</li></a>";			
		}

		$this->view .= "<li class='active'>{$i}</li>";

		for ($i = $this->page; $i < $this->page + $numLinks ; $i++) { 
			if ($i > $this->page && $i <= $this->numPages)
				$this->view .= "<a href='{$this->url}{$i}'><li>{$i}</li></a>";
		}

		$this->view .= "<a href='{$this->url}{$this->numPages}'><li><span>{$last}<span></li></a>";
		$this->view .= "</ul>";

		return $this->view;
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
	private function setPage(int $page)
	{
		if ($page > $this->numPages){ 
			$this->page = $this->numPages;
			header("Location: {$this->url}{$this->numPages}");

		} else {
			if ($page > 0){
				$this->page = $page;
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
			$this->values = prepareData($values);
		}

		$this->values[':limit'] = (string) $this->limit; 
		$this->values[':offset'] = (string) $this->offset;
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
	 * <b>read</b>:
	 * Realiza a leitura das colunas selecionadas dinamicamente em uma determinada tabela
	 * Respeitando os valores de limit e ofset.
	 */
	private function read()
	{
		try {
			$sql = new Sql;
			$this->result = $sql->select("SELECT {$this->campos} FROM {$this->tabela} {$this->condicao} LIMIT :limit OFFSET :offset", $this->values);

		} catch (Exception $e) {
			echo Msg::setMsg($e->getMessage(), ERROR);
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

