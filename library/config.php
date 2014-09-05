<?php
/**
 * Config dummy class.
 * @version 	1.0
 * @author 	Housseyn Guettaf <ghoucine@gmail.com>
 * @package 	Phiber
 */
namespace Phiber;

class config
{
  private static $instance = null;

  public $PHIBER_TIMEZONE = 'Africa/Algiers';
  /**
   * Is this a dev instance or a production application
   */
  protected $PHIBER_MODE = 'dev';
  /**
   * DB configuration properties
  */
  public $PHIBER_DB_DSN = '<db-dsn>';

  public $PHIBER_DB_PASS = '<db-password>';

  public $PHIBER_DB_USER = '<db-user>';
  /**
   * Enable/Disable logging
   * @var boolean
   */
  public $PHIBER_LOG = true;
  /**
   * Default logging handler
   * @var string
   */
  public $PHIBER_LOG_DEFAULT_HANDLER = 'file';
  /**
   * Log filename
   * @var string A valid filename
   */
  public $PHIBER_LOG_DEFAULT_FILE = 'logfile.log';
  /**
   * Session will be destroyed after 1800 seconds (30
   * minutes) of inactivity Alternatively set the value that you like in seconds
  */
  public $PHIBER_SESSION_INACTIVE = 1800;
  /**
   * The session will be destroyed after 1800 seconds (30
   * minutes) of inactivity Alternatively set the value that you like in seconds
  */
  public $PHIBER_SESSION_REGENERATE = 60;
  /**
   * The action method that should be called from your controller in case a
   * none-existant action is called (or none specified)
   */
  public $PHIBER_CONTROLLER_DEFAULT_METHOD = 'main';
  /**
   *
   * Default controller
   */

  public  $PHIBER_CONTROLLER_DEFAULT = 'index';
  /**
   * Layout file
   */
  public  $PHIBER_LAYOUT_FILE = 'layout.php';
  /**
   * Directory of the logs please set an absolute path. Must be writable by the server
   * @var string A Valid absolute path (directories will not be created for you)
   */
  protected $logDir = null;

  /**
   * Sets log level inclusive to previous levels i.e setting it to 'alert'
   * will log 'alert' and 'emergency' level events and 'debug' will log everything  and display all errors
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
  protected $logLevel = 'debug';

  public $STOP_ON_WARNINGS = true;

  public $STOP_ON_USER_WARNINGS = true;

  protected $library;

  protected $application;


  /**
   * Enable/Disable layout globally
   * @var boolean
   */
  protected $layoutEnabled = true;

  private function __construct()
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
    if(null !== self::$instance){
      return self::$instance;
    }
    return self::$instance = new self;
  }

  public function __get($var)
  {

    if(property_exists(__CLASS__, $var)){

      return $this->{$var};
    }

  }

}
?>
