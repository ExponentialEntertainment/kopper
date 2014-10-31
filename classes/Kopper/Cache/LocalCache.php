<?php

namespace Kopper\Cache;

class LocalCache implements Cache {

  public static function exists($key) {
    return apc_exists($key);
  }
  
  public static function get($key) {
    return apc_fetch($key);
  }
  
  public static function add($key, $data, $ttl = 3600) {
    return apc_add($key, $data, $ttl);
  }

  public static function set($key, $data, $ttl = 3600) {    
    apc_store($key, $data, $ttl);
  }
  
  public static function delete($key) {
    apc_delete($key);
  }

  public static function clear() {
    apc_clear_cache('user');
  }

}