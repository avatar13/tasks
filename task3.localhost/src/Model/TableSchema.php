<?php

namespace task3\Model;

/**
 * task3.localhost
 * User: Avatar - avatar130891@gmail.com
 * Date: 26.09.2018
 * Time: 6:31
 */
class TableSchema
{
	private $tableName;
	private $config;
	private $columnList = [];
	
	public function __construct($tableName, $config)
	{
		$this->tableName = $tableName;
		$this->config = $config;
	}

	public function addToColumnList($column)
	{
		if (empty($column))
			return;
		
		$this->columnList = array_merge($this->columnList, $column);
	}

	public function setColumnList($columnList)
	{
		$this->columnList = $columnList;
	}

	public function getColumnList()
	{
		return $this->columnList;
	}

	/**
	 * @return mixed
	 */
	public function getName()
	{
		return $this->tableName;
	}

	/**
	 * @return mixed
	 */
	public function getConfig()
	{
		return $this->config;
	}
}