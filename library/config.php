<?php

/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */

class config
{

  /*
   * DB configuration properties
  */
  const PHIBER_DB_DSN = 'mysql:host=127.0.0.1;dbname=codup';

  const PHIBER_DB_PASS = "password";

  const PHIBER_DB_USER = "root";
  /**
   * Enable/Disable logging
   * @var boolean
   */
  const PHIBER_LOG = true;
  /**
   * Default logging handler
   * @var string
   */
  const PHIBER_LOG_DEFAULT_HANDLER = 'file';
  /**
   * Log filename
   * @var string A valid filename
   */
  const PHIBER_LOG_DEFAULT_FILE = 'logfile.log';
  /**
   * Session will be destroyed after 1800 seconds (30
   * minutes) of inactivity Alternatively set the value that you like in seconds
  */
  const PHIBER_SESSION_INACTIVE = 1800;
  /**
   * The session will be destroyed after 1800 seconds (30
   * minutes) of inactivity Alternatively set the value that you like in seconds
  */
  const PHIBER_SESSION_REGENERATE = 60;
  /**
   * The action method that should be called from your controller in case a
   * none-existant action is called (or none specified)
   */
  const PHIBER_CONTROLLER_DEFAULT_METHOD = 'main';
  /**
   *
   * @var unknown_type
   */

  const PHIBER_CONTROLLER_DEFAULT = 'index';
  /**
   * Directory of the logs please set an absolute path. Must be writable by the server
   * @var string A Valid absolute path (directories will not be created for you)
   */
  protected $logDir = null;

  /**
   * Sets log level inclusive to previous levels i.e setting it to 'alert'
   * will log 'alert' and 'emergency' level events and 'debug' will log everything
   *
   * 'emergency';
   * 'alert';
   * 'critical';
   * 'error';
   * 'warning';
   * 'notice';
   * 'info';
   * 'debug';
   */
  public $logLevel = 'debug';
  const STOP_ON_WARNINGS = true;
  const STOP_ON_USER_WARNINGS = true;
  /**
   * Path to the library relative to this file (set in the construct)
   */
  protected $library = null;


  protected $application = null;


  /**
   * Enable/Disable layout globally
   * @var boolean
   */
  protected $layoutEnabled = true;

  protected function __construct()
  {
    if(null === $this->library){
      $this->library = __dir__;
    }
    if(null === $this->application){
      $this->application = $this->library.'/../application';
    }
    if(null === $this->logDir){
      $this->logDir = $this->application.'/logs';
    }
  }

  public static function getInstance()
  {
    return new self();
  }

  public function __get($var)
  {

    if(property_exists(__CLASS__, $var)){

      return $this->{$var};
    }

  }

}
?>