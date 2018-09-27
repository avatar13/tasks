<?php

require_once 'Model/TableSchema.php';

use PHPSQLParser\PHPSQLCreator;
use PHPSQLParser\PHPSQLParser;

use task3\Model\TableSchema;

/**
 * task3.localhost
 * User: Avatar - avatar130891@gmail.com
 */
class SqlProcessor
{
	/** @var PHPSQLParser */
	private $parser;

	/** @var PHPSQLCreator */
	private $creator;
	private $config;

	private $tableSchemaList = null;
	private $columnList = [];

	public function __construct($config)
	{
		$this->parser = new PHPSQLParser();
		$this->creator = new PHPSQLCreator();
		$this->config = $config;
	}

	/**
	 *
	 * @param $sqlRawStatement
	 * @return string
	 * @throws \PHPSQLParser\exceptions\UnsupportedFeatureException
	 */
	public function processSqlStatement($sqlRawStatement)
	{
		$parsed = $this->parser->parse($sqlRawStatement);
		if (!$parsed)
			return $sqlRawStatement;

		$lastInsertedTableSchema = null;
		$tableSchema = null;

		foreach ($parsed as $command => $statement) {

			if ($command == 'INSERT') {

				foreach ($statement as $item) {

					// если вставка в таблицу, которую нужно трансформировать
					if ($item['expr_type'] == 'table') {

						if (!isset($item['no_quotes']) || !isset($item['no_quotes']['parts']))
							return $sqlRawStatement;

						$tableSchema = $this->parseTableStatement($item, $this->config);
						// если не смогли распарсить, то пропускаем
						if (!$tableSchema)
							continue;

						// если схема таблицы уже есть, только выберем что она активная
						if (isset($this->tableSchemaList[$tableSchema->getName()])) {
							$lastInsertedTableSchema = $this->tableSchemaList[$tableSchema->getName()];
							continue;
						}

						$lastInsertedTableSchema = $tableSchema;
						$this->tableSchemaList[$tableSchema->getName()] = $tableSchema;
					}

					if ($item['expr_type'] == 'column-list' && isset($item['sub_tree']) && $lastInsertedTableSchema) {
						$lastInsertedTableSchema->setColumnList($this->parseColumnListStatement($item));
					}
				}
			}

			if ($command == 'TABLE') {

				if (!isset($statement['no_quotes']) || !isset($statement['no_quotes']['parts']))
					continue;

				$tableSchema = $this->parseTableStatement($statement, $this->config);
				if (!$tableSchema)
					continue;

				foreach ($statement as $item) {
					if (!isset($item['expr_type']) || $item['expr_type'] != 'bracket_expression')
						continue;

					if (!isset($item['sub_tree']))
						continue;

					foreach ($item['sub_tree'] as $subTreeItem) {
						if ($subTreeItem['expr_type'] != 'column-def')
							continue;

						if (!isset($subTreeItem['sub_tree']))
							continue;

						$tableSchema->addToColumnList($this->parseColumnListStatement($subTreeItem));
					}
				}

				$this->tableSchemaList[$tableSchema->getName()] = $tableSchema;
			}

			if (!in_array($command, ['INSERT', 'VALUES', 'CREATE'])) {
				return $sqlRawStatement;
			}

			//var_dump($lastInsertedTableSchema);
			if ($command == 'VALUES' && $lastInsertedTableSchema) {
				foreach ($statement as $key => $rowItem) {

					if (!isset($rowItem['data']))
						continue;

					for ($i = 0; $i < count($rowItem['data']); $i++) {
						$columnList = $lastInsertedTableSchema->getColumnList();
						if (!isset($columnList[$i]))
							continue;

						$tableConfig = $lastInsertedTableSchema->getConfig();
						$columnConfig = $tableConfig[$columnList[$i]];
						$cell = $rowItem['data'][$i];
						$cell['base_expr'] = $this->transform($cell['base_expr'], $columnConfig);
						$rowItem['data'][$i] = $cell;
					}
					$statement[$key] = $rowItem;
				}

				$parsed[$command] = $statement;
				$this->columnList = [];
			}
		}

		return $this->creator->create($parsed);
	}


	private function parseColumnListStatement($item)
	{
		$tableColumnOrder = [];

		foreach ($item['sub_tree'] as $subItem) {
			// если column reference, то запишем в порядке
			if ($subItem['expr_type'] == 'colref') {
				$columnName = end($subItem['no_quotes']['parts']);
				$tableColumnOrder[] = $columnName;
			}
		}

		return $tableColumnOrder;
	}

	/**
	 * @param $item
	 * @param $config
	 * @return null|TableSchema
	 */
	private function parseTableStatement($item, $config)
	{
		$tableInfo = null;

		$tableName = end($item['no_quotes']['parts']);
		$tableName = str_replace('`', '', $tableName);

		if (!isset($config[$tableName]))
			return null;

		return new TableSchema($tableName, $config[$tableName]);
	}

	private function transform($data, $config)
	{
		// если не установлен тип
		if (!isset($config['type']))
			return $data;

		$defaultValuesByTypesList = [
			'email' => 'asd@test.com',
			'phone' => '+79053334455',
			'integer' => 1,
			'string' => 'default_string'
		];

		if (isset($config['value']))
			return $this->typeTransformer($config['type'], $config['value']);

		return $this->typeTransformer($config['type'], $defaultValuesByTypesList[$config['type']]);
	}

	private function typeTransformer($type, $value)
	{
		if (in_array($type, ['integer'])) {
			return $value;
		}

		return "'" . $value . "'";
	}
}