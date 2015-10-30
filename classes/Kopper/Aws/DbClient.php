<?php

namespace Kopper\Aws;

use Exception;

abstract class DbClient extends AwsClient {

	abstract protected function isValidKey(array $key);

	protected function invalidKey(array $key) {
		throw new Exception('invalid key - ' . json_encode($key));
	}

	abstract public function getItem($tableName, array $key);

	abstract public function putItem($tableName, array $item);

	abstract public function updateItem($tableName, array $key, $item);

	abstract public function deleteItem($tableName, array $key);

	abstract public function listTables($clearCache = false);

	abstract public function createTable($tableName, array $schema = null);

	public function createTableIfNotExist($tableName, array $schema = null) {
		$tables = $this->listTables();
		$fullTableName = $this->getRealEnvName($tableName);

		if (in_array($fullTableName, $tables) == false) {
			$this->createTable($tableName, $schema);
		}
	}

	abstract public function deleteTable($tableName);

	abstract public function getAll($tableName);
}
