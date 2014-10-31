<?php

namespace Propel;

use Kopper\Config;
use Propel\Runtime\Connection\ConnectionManagerSingle;
use Propel\Runtime\Propel;

class PropelConnection {

  protected static $instance;
  protected $adapter;
  protected $host;
  protected $database;

  /**
   *
   * @var ConnectionManagerSingle
   */
  protected $manager;

  /**
   *
   * @var \Propel\Runtime\ServiceContainer\ServiceContainerInterface
   */
  protected $serviceContainer;
  
  public static function connect() {
    if (static::$instance == null) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  protected function __construct() {    
    $this->adapter = Config::get('db.adapter');
    $this->host = Config::get('db.host');
    $this->database = Config::get('db.name');

    $this->manager = new ConnectionManagerSingle();
    $this->manager->setConfiguration($this->defaultConfig());

    $this->serviceContainer = Propel::getServiceContainer();
    $this->serviceContainer->setAdapterClass($this->database, $this->adapter);
    $this->serviceContainer->setConnectionManager($this->database, $this->manager);
  }

  public function defaultConfig() {
    return array(
      'adapter' => $this->adapter,
      'dsn' => $this->dsn(),
      'user' => Config::get('db.user'),
      'password' => Config::get('db.password'),
    );
  }

  public function dsn() {
    switch ($this->adapter) {
      case 'mysql':
        $dsn = "{$this->adapter}:host={$this->host};dbname={$this->database};charset=utf8";
        break;
      case 'sqlite':
        $dsn = "{$this->adapter}:{$this->host}";
        break;
    }

    return $dsn;
  }

}
