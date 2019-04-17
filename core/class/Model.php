<?php

/**
* <b>Model</b>:
* Classe abstrata base para todas as models do sistema,
* Realiza manipulação de dados no banco de dados de maneira genérica.*
* @author Weydans Campos de Barros, 06/03/2019.
*/

abstract class Model
{
	private $campos;
	private $binds;
	private $campoId;
	private $bindId;
	private $setUpdate;

   /**
     * <b>saveModel</b>:
     * Realiza cadastro genérico no banco de dados.
     * @param string $tabela Recebe o nome da tabela na base de dados.
     * @param array $campos Recebe os campos da tabela.
     * @param array $data Recebe os dados a serem cadastrados.
     * @return bool Retorna true caso o cadastro seja efetuado com sucesso.
     */
    protected function saveModel(string $tabela, array $campos, array $data) 
    {	
    	try{
    		$sql = new Sql();
    		$data = $sql->prepareData($data);

    		$this->getCampos($campos);
    		$this->getBinds($campos);

    		$sql->query("INSERT INTO {$tabela} ({$this->campos}) VALUES ({$this->binds})", $data);

    		if ($sql->getConn()->lastInsertId() > 0){
    			return true;
    		} else {
                return true;
            }

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}
    }

    /**
     * <b>updateModel</b>:
     * Realiza atualização genérica no banco de dados.
     * @param string $tabela Recebe o nome da tabela na base de dados.
     * @param array $campos Recebe os campos da tabela.
     * @param data $data Recebe os dados a serem cadastrados.
     * @return bool Retorna true caso o atualização seja efetuada com sucesso.
     */
    protected function updateModel(string $tabela, array $campos, array $data) 
    {  
    	try{
    		$sql = new Sql();
    		$data = $sql->prepareData($data);

    		$this->setUpdate($campos);
    		$this->getCampoId($campos);
    		$this->getBindId($campos);

    		$sql->query("UPDATE {$tabela} SET {$this->setUpdate} WHERE {$this->campoId} = {$this->bindId}", $data);

    		return true;

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}		
    }

    /**
     * <b>findModel</b>:
     * Realiza pesquisa genérica por um registro no banco de dados.
     * @param string $tabela Recebe o nome da tabela na base de dados.
     * @param array $campos Recebe os campos da tabela.
     * @param int $id Recebe o id do registro procurado.
     * @return array Retorna um array associativo caso registro exista na tabela.
     */
    protected  function findModel(string $tabela, array $campos, int $id) 
    {
    	try{
    		$sql = new Sql();

    		$this->getCampoId($campos);
    		$this->getBindId($campos);

    		$result = $sql->select("SELECT * FROM {$tabela} WHERE {$this->campoId} = {$this->bindId}", 
    								array($this->bindId => $id));  

    		if (!empty($result)) {
    			return $result[0];
    		} else {
    			return false;
    		}

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}
    }

    /**
     * <b>listAllModel</b>:
     * Recupera todos os registros de uma determinada tabela do banco de dados.
     * @param string $tabela Recebe o nome da tabela na base de dados.
     * @return array Retorna uma matriz numérica de arrays associativos.
     */
    protected function listAllModel(string $tabela) 
    {
    	try{
    		$sql = new Sql;
    		$sql = $sql->selectAll($tabela);

    		return $sql;

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}		
    }

    /**
     * <b>deleteModel</b>:
     * Remove um registro genérico da tabela.
     * @param string $tabela Recebe o nome da tabela na base de dados.
     * @param array $campos Recebe os campos da tabela.
     * @param int $id Recebe o id do registro procurado.
     * @return bool Retorna true caso registro removido com sucesso.
     */
    protected function deleteModel(string $tabela, array $campos, int $id) 
    {
    	try{
    		$sql = new Sql;

    		$this->getCampoId($campos);
    		$this->getBindId($campos);

    		$result = $sql->delete("DELETE FROM {$tabela} WHERE {$this->campoId} = {$this->bindId}", 
    								array($this->bindId => $id));

    		return $result;

    	} catch (PDOException $e){
    		return $e->getMessage();
    	}		
    }

    /**
     * <b>nextIdModel</b>:
     * Obtem o próximo Id a ser inserido na tabela.
     * @param string $tabela Recebe o nome da tabela na base de dados.
     * @param array $campos Recebe os campos da tabela.
     * @return int Retorna o próximo id a ser inserido na tabela.
     */
    protected function nextIdModel(string $tabela, array $campos)
    {
    	try{
    		$array = self::listAllModel($tabela);

    		if (is_array($array) && count($array) > 0){
    			$this->getCampoId($campos);

    			$nextId = ($array[count($array) - 1][$this->campoId]) + 1;

                unset($array);

    			return $nextId;

    		} else {
    			return 1;
    		}
    	} catch (PDOException $e){
    		return $e->getMessage();
    	}
    }

    /**
     * <b>setUpdate</b>:
     * Prepara e monta os binds e os values na query de update.
     * @param array $campos Recebe os campos da tabela.
     */
    private function setUpdate(array $campos)
    {
    	foreach ($campos as $campo) { 
    		$array = $campo . ' = :' . $campo;
    		$this->setUpdate[] = $array;
    	}

    	$this->setUpdate = implode(', ', $this->setUpdate);
    }

    /**
     * <b>getCampos</b>:
     * Transforma o array campos em uma string.
     * @param array $campos Recebe os campos da tabela.
     */
    private function getCampos(array $campos)
    {
    	$this->campos = implode(', ', $campos);
    }

    /**
     * <b>getBinds</b>:
     * Transforma o array campos em uma string com os links do prepared statements.
     * @param array $campos Recebe os campos da tabela.
     */
    private function getBinds(array $campos)
    {
    	$this->binds = ':' . implode(', :', $campos);
    }

    /**
     * <b>getCampoId</b>:
     * Obtem o valor do campo id no array campos.
     * @param array $campos Recebe os campos da tabela.
     */
    private function getCampoId(array $campos)
    {
    	$this->campoId = $campos[0];
    }

    /**
     * <b>getBindId</b>:
     * Obtem o bind do campo id.
     * @param array $campos Recebe os campos da tabela.
     */
    private function getBindId(array $campos)
    {
    	$this->bindId = ':' . $campos[0];
    }
}
