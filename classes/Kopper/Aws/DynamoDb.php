<?php

namespace Kopper\Aws;

use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\ResourceInUseException;
use Aws\DynamoDb\Marshaler;
use Kopper\Cache\LocalCache;
use Kopper\Environment;

class DynamoDb extends DbClient {

	const DAY_FORMAT = 'Y_m_d';
	const ALL_TABLES_KEY = 'dynamo-tables';

	public function __construct($config = array()) {
		parent::__construct($config);

		$this->client = new DynamoDbClient($this->config);
	}

	protected function isValidKey(array $key) {
		return true;
	}

	public function getItem($tableName, array $key) {
		$marshaler = new Marshaler();

		$result = $this->client->getItem(array(
			'ConsistentRead' => true,
			'TableName' => $this->getRealEnvName($tableName),
			'Key' => $marshaler->marshalItem($key)
		));

		if (isset($result['Item']) === true) {
			return $marshaler->unmarshalItem($result['Item']);
		} else {
			return null;
		}
	}

	public function putItem($tableName, array $item, $condition = null, $expressionValuesMap = null) {
		$marshaler = new Marshaler();

		$params = array(
			'TableName' => $this->getRealEnvName($tableName),
			'Item' => $marshaler->marshalItem($item)
		);

		if (empty($condition) === false) {
			$params['ConditionExpression'] = $condition;

			if (empty($expressionValuesMap) === false) {
				$params['ExpressionAttributeValues'] = $expressionValuesMap;
			}
		}

		$this->client->putItem($params);
	}

	public function updateItem($tableName, array $key, $updateExpression, $expressionValuesMap = null) {
		$marshaler = new Marshaler();

		$params = array(
			'TableName' => $this->getRealEnvName($tableName),
			'Key' => $marshaler->marshalItem($key),
			'ReturnValues' => 'UPDATED_NEW'
		);

		$params['UpdateExpression'] = $updateExpression;

		if (empty($expressionValuesMap) === false) {
			$params['ExpressionAttributeValues'] = $expressionValuesMap;
		}

		$result = $this->client->updateItem($params);

		if (isset($result['Attributes']) === true) {
			return $marshaler->unmarshalItem($result['Attributes']);
		} else {
			return null;
		}
	}

	public function deleteItem($tableName, array $key) {
		$marshaler = new Marshaler();

		$this->client->deleteItem(array(
			'TableName' => $this->getRealEnvName($tableName),
			'Key' => $marshaler->marshalItem($key)
		));
	}

	public function listTables($clearCache = false) {
		$tables = LocalCache::get(self::ALL_TABLES_KEY);

		if ($tables === false || $clearCache == true) {
			$result = $this->client->listTables();

			$tables = $result['TableNames'];
			LocalCache::set(self::ALL_TABLES_KEY, $tables);
		}

		return $tables;
	}

	public function createTable($tableName, array $schema = null) {
		$fullTableName = $this->getRealEnvName($tableName);
		$schema['TableName'] = $fullTableName;

		if (empty($schema['ProvisionedThroughput']) == true) {
			if (APPLICATION_ENV == Environment::PRODUCTION) {
				$schema['ProvisionedThroughput'] = array(
					'ReadCapacityUnits' => 60,
					'WriteCapacityUnits' => 60
				);
			} else {
				$schema['ProvisionedThroughput'] = array(
					'ReadCapacityUnits' => 5,
					'WriteCapacityUnits' => 5
				);
			}
		}

		try {
			$this->client->createTable($schema);
		} catch (ResourceInUseException $e) {
			//already created wait for table existis
		}

		$this->client->waitUntil('TableExists', array('TableName' => $fullTableName));

		$this->listTables(true);
	}

	public function updateThroughput($tableName, $read, $write) {
		$this->client->updateTable(array(
			'TableName' => $this->getRealEnvName($tableName),
			'ProvisionedThroughput' => array(
				'ReadCapacityUnits' => $read,
				'WriteCapacityUnits' => $write
			)
		));
	}

	public function deleteTable($tableName) {
		$this->client->deleteTable(array('TableName' => $this->getRealEnvName($tableName)));
		$this->client->waitUntil('TableNotExists', array('TableName' => $this->getRealEnvName($tableName)));

		$this->listTables(true);
	}

	public function count($tableName, array $conditions = null) {
		$params = array(
			'TableName' => $this->getRealEnvName($tableName),
			'Select', 'COUNT'
		);

		if (empty($conditions) == false) {
			$params['ScanFilter'] = $conditions;
		}

		$result = $this->client->scan($params);

		return $result['Count'];
	}

	public function getAll($tableName) {
		return $this->scan($tableName, array());
	}

	public function scan($tableName, array $params) {
		$params['TableName'] = $this->getRealEnvName($tableName);
		return $this->client->scan($params);
	}

	public function query($tableName, array $params) {
		$params['TableName'] = $this->getRealEnvName($tableName);
		return $this->client->query($params);
	}

}
