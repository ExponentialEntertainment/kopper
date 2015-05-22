<?php

namespace Kopper\Aws\Model;

use ArrayObject;
use Kopper\Aws\DynamoDb;

abstract class DynamoDbModel extends ArrayObject {

  protected $db;
  protected $schema;    
  public $tableName;
  
  public function __construct($array) {
    parent::__construct($array);
    
    $this->db = DynamoDb::getInstance();
  }
  
  public function setSchema(array $schema){
    $this->schema = $schema;
  }
  
  public function save(){
    $this->db->createTableIfNotExist($this->tableName, $this->schema);
    $this->db->putItem($this->tableName, $this->getArrayCopy());
  }

}
