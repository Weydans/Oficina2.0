<?php

/**
* <b>Model</b>:
* Classe abstrata base para todas as models do sistema,
* Realiza manipulação de dados no banco de dados de maneira genérica.*
* @author Weydans Campos de Barros, 06/03/2019.
*/

class Sql 
{	
	/** @var PDO */
	private $conn;

	/**
	* <b>init</b>:
	* Obtem a conexão com o banco de dados
	*/
	public function init()
	{
		$this->conn = Conn::getConnection();
	}

	/**
	* <b>query</b>:
	* Executa uma query genérica.
	* @param string $query Recebe a query a ser executada.
	* @param array $params Recebe um array com os parametros da query.
	* @return mixed Pode retornar array, bool ou msg de erro.
	*/
	public function query(string $query, $params = array())
	{	
		$this->init();
	
		$stmt = $this->conn->prepare($query);
		$this->setParams($stmt, $params);
		$stmt->execute();
		
		return $stmt;
	}
	
	/**
	* <b>select</b>:
	* Recupera um registro de uma determinada tabela.
	* @param string $query Recebe a query a ser executada.
	* @param array $params Recebe um array com os parametros da query.
	* @return array Retorna array contendo o registro buscado.
	*/
	public function select(string $query, array $params)
	{
		$stmt = $this->query($query, $params);
		
		$res = $stmt->fetchAll(PDO::FETCH_ASSOC);

		return $res;	
	}

	/**
	* <b>selectAll</b>:
	* Recupera todos os registros de uma determinada tabela.
	* @param string $tabela Recebe a query a ser executada.
	* @return array Retorna array contendo os registros encontrados.
	*/
	public function selectAll(string $tabela)
	{
		$this->init();

		$query = "SELECT * FROM {$tabela}";
		$stmt = $this->conn->prepare($query);
		$stmt->execute();
		$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

		if (!empty($stmt)){
			return $result;
		} else {
			return false;
		}
	}

	/**
	* <b>delete</b>:
	* Remove um determinado registro de uma determinada tabela.
	* @param string $query Recebe a query a ser executada.
	* @param array $data Recebe um array com os dados do registro a ser removido.
	* @return array Retorna true caso registro removido com sucesso.
	*/
	public function delete(string $query, array $data)
	{
		$this->init();

		$stmt = $this->conn->prepare($query);
		$this->setParams($stmt, $data);	
		$result = $stmt->execute();

		return $result;		
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

	/**
     * <b>setParams</b>:
     * Realiza a substituição dos links pelos valores correspondentes.
     * @param PDOStatement $pdoStatement Recebe o objeto PDPStatement.
     * @param array $values Recebe os dados.
     */
	private function setParams($pdoStatement, $values)
	{
		foreach ($values as $key => $value) {
			$this->setParam($pdoStatement, $key, $value);
		}
	}
	
	/**
     * <b>setParam</b>:
     * Realiza a substituição do link pelo valor correspondente.
     * @param PDOStatement $pdoStatement Recebe o objeto PDPStatement.
     * @param string $key Recebe o link que será substituido.
     * @param mixed $values Recebe o valor que substituira o link.
     */
	private function setParam($pdoStatement, $key, $value)
	{
		$pdoStatement->bindParam($key, $value);
	}
	
	/**
     * <b>setParam</b>:
     * @return PDO Retorna um objeto de conexão com o banco de dados.
     */
	public function getConn()
	{
		return $this->conn;
	}
	
}
