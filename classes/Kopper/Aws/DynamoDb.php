<?php

namespace Kopper\Aws;

use Kopper\Cache\LocalCache;
use Kopper\Environment;
use Aws\DynamoDb\DynamoDbClient;
use Aws\DynamoDb\Exception\ResourceInUseException;
use Aws\DynamoDb\Enum\Select;
use Aws\DynamoDb\Iterator\ItemIterator;

class DynamoDb extends DbClient {

  const DAY_FORMAT = 'Y_m_d';
  const ALL_TABLES_KEY = 'dynamo-tables';

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = DynamoDbClient::factory($this->config);
  }

  protected function isValidKey(array $key) {
    return true;
  }

  public function getItem($tableName, array $key) {
    $result = $this->client->getItem(array(
      'ConsistentRead' => true,
      'TableName' => $this->getRealEnvName($tableName),
      'Key' => $this->client->formatAttributes($key)
    ));

    return $result['Item'];
  }

  public function putItem($tableName, array $item) {
    $this->client->putItem(array(
      'TableName' => $this->getRealEnvName($tableName),
      'Item' => $this->client->formatAttributes($item)
    ));
  }

  public function updateItem($tableName, array $key, array $item) {
    $this->client->updateItem(array(
      'TableName' => $this->getRealEnvName($tableName),
      'Key' => $this->client->formatAttributes($key),
      'AttributeUpdates' => $item
    ));
  }

  public function deleteItem($tableName, array $key) {
    $this->client->deleteItem(array(
      'TableName' => $this->getRealEnvName($tableName),
      'Key' => $this->client->formatAttributes($key)
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

    $this->client->waitUntilTableExists(array('TableName' => $fullTableName));

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
    $this->client->waitUntilTableNotExists(array('TableName' => $this->getRealEnvName($tableName)));

    $this->listTables(true);
  }

  public function count($tableName, array $conditions = null) {
    $params = array(
      'TableName' => $this->getRealEnvName($tableName),
      'Select', Select::COUNT
    );

    if (empty($conditions) == false) {
      $params['ScanFilter'] = $conditions;
    }

    $result = $this->client->scan($params);

    return $result['Count'];
  }

  public function getAll($tableName, array $fields = null) {
    $params = array(
      'TableName' => $this->getRealEnvName($tableName)
    );

    if (empty($fields) == false) {
      $params['Select'] = Select::SPECIFIC_ATTRIBUTES;
      $params['AttributesToGet'] = $fields;
    }

    $iterator = new ItemIterator($this->client->getScanIterator($params));

    return $iterator;
  }

  public function scan($tableName, array $conditions = null, array $fields = null) {
    $params = array(
      'TableName' => $this->getRealEnvName($tableName)
    );

    if (empty($fields) == false) {
      $params['Select'] = Select::SPECIFIC_ATTRIBUTES;
      $params['AttributesToGet'] = $fields;
    }

    if (empty($conditions) == false) {
      $params['ScanFilter'] = $conditions;
    }

    $iterator = new ItemIterator($this->client->getScanIterator($params));

    return $iterator;
  }

}
