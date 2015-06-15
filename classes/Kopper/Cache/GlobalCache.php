<?php

namespace Kopper\Cache;

class GlobalCache implements Cache {

  private static $instance;

  private static function isMemcached() {
    return class_exists('Memcached', false);
  }

  public static function getInstance() {
    if (self::$instance == null) {
      if (self::isMemcached() === true) {
        $memcache = new \Memcached();
        $memcache->setOption(\Memcached::OPT_BINARY_PROTOCOL, true);
      } else {
        $memcache = new \Memcache();
      }

      self::$instance = $memcache;
    }

    return self::$instance;
  }

  public static function init($servers, $port = 11211) {
    $memcache = self::getInstance();

    if (is_array($servers) === true) {
      foreach ($servers as $server) {
        $memcache->addServer($server, $port);
      }
    }else{
      $memcache->addServer($servers, $port);
    }
  }

  public static function exists($key) {
    if (self::get($key) === false) {
      return false;
    }

    return true;
  }

  public static function get($key) {
    $memcache = self::getInstance();
    return $memcache->get($key);
  }

  public static function add($key, $data, $ttl = 3600) {
    $memcache = self::getInstance();

    if (self::isMemcached() === true) {
      return $memcache->add($key, $data, $ttl);
    } else {
      return $memcache->add($key, $data, 0, $ttl);
    }
  }

  public static function set($key, $data, $ttl = 3600) {
    $memcache = self::getInstance();
    if (self::isMemcached() === true) {
      $memcache->set($key, $data, $ttl);
    } else {
      $memcache->set($key, $data, 0, $ttl);
    }
  }

  public static function delete($key) {
    $memcache = self::getInstance();
    $memcache->delete($key);
  }

  public static function clear() {
    $memcache = self::getInstance();
    $memcache->flush();
  }

}
