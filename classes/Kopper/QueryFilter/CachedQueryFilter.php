<?php

namespace Kopper\QueryFilter;

use Kopper\Cache\GlobalCache;
use Propel\Runtime\ActiveQuery\ModelCriteria;

class CachedQueryFilter extends QueryFilter {

  public function paginate(ModelCriteria $query, $page = 1, $maxPerPage = 25) {
    $queryMd5 = md5($query->toString());
    $key = $query->getTableMap()->getName() . '_query_' . $queryMd5 . '_' . $page;

    $records = GlobalCache::get($key);

    if (empty($records) === true) {      
      $records = $query->paginate($page, $maxPerPage);
      
      if($records->isEmpty() === false){
        GlobalCache::set($key, $records);
      }
    }

    return $records;
  }

}
