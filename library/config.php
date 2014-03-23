<?php

/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	Phiber
 */

class config
{

  /**
   * Enable/Disable logging
   * @var boolean
   */
  protected $log = true;
  /**
   * Default logging handler
   * @var string
   */
  protected $logHandler = 'file';
  /**
   * Log filename
   * @var string A valid filename
   */
  protected $logFile = 'logfile.txt';
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
  public $logLevel = 'warning';
  /*
   * Path to the library relative to this file (set in the construct)
   */
  protected $library = null;
  /*
   * If set to true the session will be destroyed after 1800 seconds (30
   * minutes) of inactivity Alternatively set the value that you like in seconds
   * Set it to false to disable the functionality
   */
  protected $inactive = false;
  /*
   * The action method that should be called from your controller in case a
   * none-existant action is called (or none specified)
   */
  protected $defaultMethod = 'main';

  protected $application = null;

  /*
   * DB configuration properties
   */
  public $_dsn = 'mysql:host=127.0.0.1;dbname=codup';

  protected $_dbpass = "password";

  protected $_dbuser = "root";
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
  /*
   * No need for a getter for each of the properties or the methods
   */
  public function __get($var)
  {

    if(property_exists(__CLASS__, $var)){

      return $this->{$var};
    }

  }

}
?>