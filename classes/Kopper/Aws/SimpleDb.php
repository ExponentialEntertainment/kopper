<?php

namespace Kopper\Aws;

use Aws\SimpleDb\SimpleDbClient;
use Kopper\Cache\LocalCache;

class SimpleDb extends DbClient {

  const NAME = 'Name';
  const ALL_TABLES_KEY = 'simpledb-tables';

  public function __construct($config = array()) {
    parent::__construct($config);

    $this->client = new SimpleDbClient($this->config);
  }

  protected function isValidKey(array $key) {
    return (empty($key[self::NAME]) === false && preg_match('/^[a-zA-Z0-9_]+$/', $key[self::NAME]) === 1);
  }

  protected function getKey(array $key) {
    if ($this->isValidKey($key) === true) {
      return (string) $key[self::NAME];
    } else {
      $this->invalidKey($key);
    }
  }

  public function getItem($tableName, array $key) {
    $result = $this->client->getAttributes(array(
      'DomainName' => $this->getRealEnvName($tableName),
      'ItemName' => $this->getKey($key)
    ));

    return $result['Attributes'];
  }

  public function putItem($tableName, array $item) {
    $this->client->putAttributes(array(
      'DomainName' => $this->getRealEnvName($tableName),
      'ItemName' => $this->getKey($item),
      'Attributes' => $item['Attributes']
    ));
  }

  public function updateItem($tableName, array $key, $item) {
    $this->putItem($tableName, $item);
  }

  public function deleteItem($tableName, array $key) {
    $this->client->deleteAttributes(array(
      'DomainName' => $this->getRealEnvName($tableName),
      'ItemName' => $this->getKey($key)
    ));
  }

  public function listTables($clearCache = false) {
    $tables = LocalCache::get(self::ALL_TABLES_KEY);

    if ($tables === false || $clearCache == true) {
      $nextToken = null;
      $tables = array();

      do {
        if (empty($nextToken) == false) {
          $result = $this->client->listDomains(array(
            'NextToken' => $nextToken
          ));
        } else {
          $result = $this->client->listDomains();
        }

        $tables = array_merge($tables, $result['DomainNames']);
        $nextToken = $result['NextToken'];
      } while (empty($nextToken) == false);

      LocalCache::set(self::ALL_TABLES_KEY, $tables);
    }

    return $tables;
  }

  public function createTable($tableName, array $schema = null) {
    $tables = $this->listTables();

    $table = $this->getRealEnvName($tableName);

    if (in_array($table, $tables) === false) {
      $this->client->createDomain(array(
        'DomainName' => $table,
      ));

      $this->listTables(true);
    }
  }

  public function deleteTable($tableName) {
    $this->client->deleteDomain(array(
      'DomainName' => $this->getRealEnvName($tableName),
    ));

    $this->listTables(true);
  }

  public function getAll($tableName, array $fields = null) {
    $tableName = $this->getRealEnvName($tableName);

    return $this->query("select * from $tableName");
  }

  public function query($query, $limit = null, $nextToken = null) {
    $records = array();

    if (empty($limit) === false) {
      $query .= " limit $limit";
    }

    $done = false;

    do {
      if (empty($limit) === true || count($records) < $limit) {
        if (isset($nextToken)) {
          $result = $this->client->select(array(
            'SelectExpression' => $query,
            'NextToken' => $nextToken
          ));
        } else {
          $result = $this->client->select(array(
            'SelectExpression' => $query
          ));
        }

        if (empty($result['Items']) === false) {
          $records = array_merge($records, $result['Items']);
        }

        $nextToken = isset($result['NextToken']) ? $result['NextToken'] : null;

        if (empty($nextToken) === true) {
          $done = true;
        }
      } else {
        $done = true;
      }
    } while ($done === false);

    return (object) array(
        'records' => $records,
        'nextToken' => $nextToken
    );
  }

  public function batchPutItems($tableName, array $items) {
    while (count($items) > 0) {
      $this->client->batchPutAttributes(array(
        'DomainName' => $this->getRealEnvName($tableName),
        'Items' => array_splice($items, 0, 25)
      ));
    }
  }

}
