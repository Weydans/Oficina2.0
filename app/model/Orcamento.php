<?php

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

	public function save(array $data)
	{
		return $this->saveModel(self::$table, self::$columns, $data);
	}

	public function update(array $data)
	{
		return $this->updateModel(self::$table, self::$columns, $data);
	}

	public function find(int $id)
	{
		return $this->findModel(self::$table, self::$columns, $id);
	}

	public function listAll()
	{
		return $this->listAllModel(self::$table);
	}

	public function delete(int $id)
	{
		return $this->deleteModel(self::$table, self::$columns, $id);
	}

	public function nextId()
	{
		return $this->nextIdModel(self::$table, self::$columns);
	}

	public function getTable()
	{
		return self::$table;
	}

}
