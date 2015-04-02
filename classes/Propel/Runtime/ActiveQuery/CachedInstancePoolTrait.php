<?php

namespace Propel\Runtime\ActiveQuery;

use Kopper\Cache\GlobalCache;
use Propel\Runtime\Propel;

trait CachedInstancePoolTrait {

  use InstancePoolTrait {
    InstancePoolTrait::addInstanceToPool as defaultAddInstanceToPool;
    InstancePoolTrait::removeInstanceFromPool as defaultRemoveInstanceFromPool;
    InstancePoolTrait::getInstanceFromPool as defaultGetInstanceFromPool;
  }

  protected static function getPrefix() {
    return static::TABLE_NAME . '_';
  }

  protected static function getCacheKey($object) {
    return static::getPrefix() . static::getInstanceKey($object);
  }

  public static function addInstanceToPool($object, $key = null) {
    if (Propel::isInstancePoolingEnabled()) {
      static::defaultAddInstanceToPool($object, $key);
      GlobalCache::set(static::getCacheKey($object), $object);
    }
  }

  public static function removeInstanceFromPool($value) {
    if (Propel::isInstancePoolingEnabled()) {
      static::defaultRemoveInstanceFromPool($value);
      GlobalCache::delete(static::getCacheKey($value));
    }
  }

  public static function getInstanceFromPool($key) {
    $object = static::defaultGetInstanceFromPool($key);

    if (Propel::isInstancePoolingEnabled()) {
      if (empty($object) === true) {
        $object = GlobalCache::get(static::getPrefix() . $key);
      }

      if ($object === false) {
        $object = null;
      } else {
        static::defaultAddInstanceToPool($object, $key);
      }
    }

    return $object;
  }

}
