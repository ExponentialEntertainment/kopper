<?php

namespace Kopper\Aws\Model;

use Aws\DynamoDb\Marshaler;
use Kopper\Aws\DynamoDb;

abstract class DynamoDbQuery {

  protected $db;

  public function __construct() {
    $this->db = DynamoDb::getInstance();
  }

  public static function create() {
    return new static();
  }

  abstract protected function createObject(array $data);

  abstract public function tableName();

  protected function populate(array $data) {
    if (empty($data) === false) {
      return $this->createObject($data);
    } else {
      return null;
    }
  }
  
  protected function populateArray(array $rawItems){
    $marshaler = new Marshaler();

    $items = array();

    foreach ($rawItems as $rawItem) {
      array_push($items, $this->createObject($marshaler->unmarshalItem($rawItem)));
    }

    return $items;
  }

  public function find() {
    return $this->populateArray($this->db->getAll($this->tableName()));
  }

}
