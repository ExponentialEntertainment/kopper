<?php

namespace Kopper\Controller;

use Kopper\Controller\RestController;
use Kopper\QueryFilter\QueryFilter;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Map\TableMap;
use Slim\Slim;

abstract class AutoRestController extends RestController {

  public function __construct(Slim $app, array $params = array()) {
    parent::__construct($app, $params);
    
    $this->connectToDatabase();
  }
  
  abstract protected function createObject();

  abstract protected function createQuery();

  public function findByPrimaryKey(ModelCriteria $query, $key){
    return $query->findPk($key);
  }
  
  public function save(ActiveRecordInterface $record) {
    $record->save();
  }

  public function update(ActiveRecordInterface $record, array $params) {
    $record->fromArray($params, TableMap::TYPE_CAMELNAME);
  }
  
  protected function query(QueryFilter $filter, array $params, $query = null){
    unset($params[self::PARAM_ID]);
    
    if(empty($query) === true){
      $query = $this->createQuery();
    }
    
    return $filter->paginate($filter->addFilters($query, $params), $filter->getPage($params));
  }

  public function getAction() {
    $id = $this->param(self::PARAM_ID);

    if (empty($id) === true) {
      $filter = new QueryFilter();
      $records = $this->query($filter, $this->params());

      $response = array(
        'records' => empty($records) ? array() : $records->toArray(null, false, TableMap::TYPE_CAMELNAME),
        'links' => $filter->getLinks($records)
      );
      
      return $this->outputJson($response);
    } else {      
      $record = $this->findByPrimaryKey($this->createQuery(), $id);      
      $record = empty($record) ? null : $record->toArray(TableMap::TYPE_CAMELNAME);

      if (empty($record) === false) {
        return $this->outputJson($record);
      } else {
        $this->notFound();
      }
    }
  }

  public function postAction() {
    $record = $this->createObject();
    $this->update($record, $this->params());
    $this->save($record);

    $this->outputJson($record->toArray(TableMap::TYPE_CAMELNAME));
  }

  public function putAction() {
    $id = $this->param(self::PARAM_ID);

    $record = $this->findByPrimaryKey($this->createQuery(), $id);

    if (empty($record) === false) {
      $this->update($record, $this->params());
      $this->save($record);

      $this->outputJson($record->toArray(TableMap::TYPE_CAMELNAME));
    } else {
      $this->notFound();
    }
  }

  public function deleteAction() {
    $id = $this->param(self::PARAM_ID);

    $this->createQuery()->filterById($id)->delete();

    $this->outputJson(array('deleted' => true), 204);
  }

}
