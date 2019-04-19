<?php 

/**
 * <b>Paginator</b>:
 * Classe responsável por realizar paginação de resultados dinamicamente
 * E exibição do paginador padrão, permitindo também a personalização do mesmo.
 * @author Weydans Campos de Barros, 19/04/2019.
 */
class Paginator
{	
	private $page;
	private $resPerPage;
	private $numPages;
	private $totalResults;

	private $obj;
	private $tabela;
	private $campos;
	private $limit;
	private $offset;

	private $result;

	private $view;
	private $styleClass;

	/**
	 * <b>Paginator</b>:
	 * Responsável pela configuração dos atributos principais do objeto.
	 * @param int $page Recebe o valor da variavel querystring que recebe a pagina atual.
	 * @param object $obj Recebe um objeto que está sendo manipulado para paginação.
	 * @param int $page Recebe o número de resultados que serão exibidos por pagina.
	 */
	public function __construct(int $page, object $obj, int $resPerPage)
	{ 
		$this->obj = $obj;
		$this->tabela = $obj->getTable();
		$this->resPerPage = $resPerPage;
		$this->totalResults = count($obj->listAll());
		$this->numPages = ceil($this->totalResults / $resPerPage);		
		$this->page = ($page > $this->numPages) ? $this->numPages : $page;
	}

	/**
	 * <b>exePaginator</b>:
	 * Ordena e centraliza a execução dos métodos responsáveis pela paginação.
	 * @param array $campos Recebe os campos informados pelo programador.
	 * @return array Retorna uma matriz contendo um array associativo com os resultados;
	 */
	public function exePaginator(array $campos = array()) : array
	{
		$this->pager();
		$this->setCampos($campos);
		$this->read();

		return $this->result;
	}

	/**
	 * <b>getPaginator</b>:
	 * Cria e configura o paginador dinâmicamente a ser exibido.
	 * @param string $url Recebe a url base para a paginação (SEM A QUERYSTRING).
	 * @param int $numLinks Recebe o número máximo de links do paginador.
	 * @param string $first Recebe o primeiro link personalizado.
	 * @param string $first Recebe o último link personalizado.
	 * @return string Retorna o HTML do paginator para exibição.
	 */
	public function getPaginator(string $url, int $numLinks = null, string $first = null, string $last = null) : string
	{
		$first  = (!empty($first)) ? $first : $first = "Primeira Página";
		$last = (!empty($last)) ? $last : $last = "Última Página";
		$numLinks = (!empty($numLinks)) ? $numLinks : $numLinks = 5;

		$this->view = "<ul class='paginator {$this->styleClass}'>";
		$this->view .= "<a href='{$url}?page=1'><li><span>{$first}<span></li></a>";

		for ($i = $this->page - $numLinks + 1; $i < $this->page  ; $i++) { 
			if ($i > 0)
				$this->view .= "<a href='{$url}?page={$i}'><li>{$i}</li></a>";			
		}

		$this->view .= "<li class='active'>{$i}</li>";

		for ($i = $this->page; $i < $this->page + $numLinks ; $i++) { 
			if ($i > $this->page && $i <= $this->numPages)
				$this->view .= "<a href='{$url}?page={$i}'><li>{$i}</li></a>";
		}

		$this->view .= "<a href='{$url}?page={$this->numPages}'><li><span>{$last}<span></li></a>";
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
	 * <b>pager</b>:
	 * Configura a página, o limite e o offset.	 *
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
			$this->result = $sql->select("SELECT {$this->campos} FROM {$this->tabela} LIMIT :limit OFFSET :offset", 
				array(
					':limit' => (string) $this->limit, 
					':offset' => (string) $this->offset
				));

		} catch (Exception $e) {
			echo Msg::setMsg($e->getMessage(), ERROR);
		}
	}

}

