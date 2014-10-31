<?php

namespace Kopper\Cache;

interface Cache {
  
  public static function exists($key);

  public static function get($key);
  
  public static function add($key, $data, $ttl = 3600);

  public static function set($key, $data, $ttl = 3600);
  
  public static function delete($key);
  
  public static function clear();
}