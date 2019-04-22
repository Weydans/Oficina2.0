<?php

/**
 * <b>Orcamento</b>:
 * Classe de modelo de orçamentos, Padrão DAO,
 * Resposável por fornecer interface entre os cotrolLers e o banco de dados. *
 * @author Weydans Campos de Barros, 18/04/2019.
 */
class Orcamento extends Model
{
	private static $table = 'orcamento';
	private static $columns = [
		'orcamento_id',
		'orcamento_cliente',
		'orcamento_data_hora',
		'orcamento_vendedor',
		'orcamento_descricao',
		'orcamento_valor'
	];

	/**
	 * <b>save</b>:
	 * Realiza cadastro de orçamento no banco de dados.
	 * @param array $data Recebe o array com os dados a serem cadastrados.
	 * @return bool Retorna true caso o cadastro seja efetuado com sucesso.
	 */
	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
	}

	/**
	 * <b>update</b>:
	 * Realiza atualização de orçamento no banco de dados.
	 * @param array $data Recebe o array com os dados a serem atualizados.
	 * @return bool Retorna true caso o atualização seja realizada com sucesso.
	 */
	public function update(array $data)
	{
		return $this->updateModel(self::$table, self::$columns, $data);
	}

	/**
	 * <b>find</b>:
	 * Recupera um determinado orçamento na base de dados.
	 * @param int $id Recebe o id do registro a ser recuperado.
	 * @return array Retorna um array associativo caso o registro exista.
	 */
	public function find(int $id)
	{
		return $this->findModel(self::$table, self::$columns, $id);
	}

 	/**
	 * <b>listAll</b>:
	 * Recupera todos os registros existentes na tabela orcamento do banco de dados.
	 * @return array Retorna uma matriz numérica de arrays associativos contendo os registros.
	 */
	public function listAll()
	{
		return $this->listAllModel(self::$table);
	}
 	
 	/**
	 * <b>delete</b>:
	 * Remove um determinado orçamento da tabela endereco na base de dados.
	 * @param int $id Recebe o id do registro a ser removido.
	 * @return bool Retorna true caso registro seja excluído com sucesso.
	 */
	public function delete(int $id)
	{
		return $this->deleteModel(self::$table, self::$columns, $id);
	}

	/**
	 * <b>nextId</b>:
	 * Obtem o próximo Id a ser inserido na tabela orcamento
	 * @return int Retorna o próximo id a ser inserido na tabela.
	 */
	public function nextId()
	{
		return $this->nextIdModel(self::$table, self::$columns);
	}

	/**
	 * <b>getTable</b>:
	 * @return string Retorna orçamento.
	 */
	public function getTable()
	{
		return self::$table;
	}

	/**
	 * <b>getTable</b>:
	 * @return array Retorna um array numérico com os nomes de todos os campos da tabela orçamento.
	 */
	public static function getColumns()
	{
		return self::$columns;
	}
}
