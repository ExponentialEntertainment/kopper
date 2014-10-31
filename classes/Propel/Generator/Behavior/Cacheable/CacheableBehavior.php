<?php

namespace Propel\Generator\Behavior\Cacheable;

use Propel\Generator\Model\Behavior;

class CacheableBehavior extends Behavior {

  private function getTableName(){
     return $this->getTable()->getName();
  }
  
  private function getTableMapClassName(){
    $tableName = ucfirst($this->getTableName());
    
    return "{$tableName}TableMap";
  }
  
  private function getPrefix() {
    return $this->getTableName() . '_';
  }
  
  public function objectMethods($builder) {    
    $script = "
      public function cacheKey() {        
        return '{$this->getPrefix()}' . {$this->getTableMapClassName()}::getInstanceKey(\$this);
      }
    ";
        
    return $script;
  }

  public function queryMethods($builder) {
    $builder->declareClass('\\Kopper\\Cache\\GlobalCache');

    $script = "
      protected function storeInCache(\$object) {
        {$this->getTableMapClassName()}::addInstanceToPool(\$object);
      }

      protected function getFromCache(\$id) {
        return {$this->getTableMapClassName()}::getInstanceFromPool(\$id);
      }

      protected function storeIdInCache(\$key, \$id) {
        GlobalCache::set('{$this->getPrefix()}' . \$key, \$id);
      }

      protected function getIdFromCache(\$key) {
        return GlobalCache::get('{$this->getPrefix()}'  . \$key);
      }
    ";

    return $script;
  }
  
  public function staticMethods($builder){
    $builder->declareClass('\\Propel\\Runtime\\ActiveQuery\\CachedInstancePoolTrait');
  }


  public function tableMapFilter(&$script) {
    $script = preg_replace('/InstancePoolTrait/m', 'CachedInstancePoolTrait', $script);
  }

}
