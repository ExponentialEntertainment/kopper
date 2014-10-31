<?php

namespace Kopper\Logger;

use Exception;
use Kopper\Utility;
use Kopper\Environment;
use Kopper\Config;

class Logger {

  /**
   * 
   * @return \Kopper\Logger\Logger
   */
  public static function getInstance() {
    $className = Config::get('logger.class');

    if (empty($className) === true) {
      return new static();
    } else {
      return new $className();
    }
  }

  public function log(Exception $e) {
    $exceptionInfo = Utility::exceptionToInfo($e, true);

    if (Utility::isCLI() == true) {
      $this->logCli($exceptionInfo);
    } else {
      $exceptionInfo['stack'] = $e->getTraceAsString();
      $this->logNonCli($exceptionInfo);
    }

    switch (APPLICATION_ENV) {
      case Environment::LOCAL:
        $this->logLocal($exceptionInfo);
        break;
      case Environment::DEVELOPMENT:
        $this->logDev($exceptionInfo);
        break;
      case Environment::TEST:
        $this->logTest($exceptionInfo);
        break;
      case Environment::PRODUCTION:
      default:
        $this->logProd($exceptionInfo);
        break;
    }
  }

  protected function logCli($exceptionInfo) {
    print_r($exceptionInfo);
  }

  protected function logNonCli($exceptionInfo) {
    $logMessage = 'error: ' . $exceptionInfo['exception'] . ' - code: ' . $exceptionInfo['code'] . ' - message: ' . $exceptionInfo['message'] . ' - file: ' . $exceptionInfo['file'] . "({$exceptionInfo['line']})" . ' - server: ' . $exceptionInfo['server'] . ' - remote: ' . $exceptionInfo['remote'] . ' - uri: ' . $exceptionInfo['uri'] . ' - stack: ' . $exceptionInfo['stack'];
    error_log($logMessage);
  }

  protected function logLocal($exceptionInfo) {
    //do nothing
  }

  protected function logDev($exceptionInfo) {
    $this->email($exceptionInfo);
  }

  protected function logTest($exceptionInfo) {
    $this->email($exceptionInfo);
  }

  protected function logProd($exceptionInfo) {
    //do nothing
  }

  protected function email($exceptionInfo) {
    $subject = Config::get('env.prefix') . ' error - ' . APPLICATION_ENV;

    $body = array(
      'Text' => array(
        'Data' => json_encode($exceptionInfo, JSON_PRETTY_PRINT)
      )
    );

    Utility::sendEmail(Config::get('email.admins'), $subject, $body, Config::get('email.system.error'));
  }

}
