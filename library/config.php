<?php

/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */

class config
{

  /*
   * Path to the library relative to this file (set in the construct)
   */
  protected $library = null;
  /*
   * Regenerate session id or not (false will disable the functionality) Set it
   * to true for a default value of 1800 seconds (30 minutes) Or set it to
   * whatever value you like to set in seconds
   */
  protected $sessionReginerate = false;
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

  /*
   * Enable/disable debug
   */
  public $debug = false;

  /*
   * DB configuration properties
   */
  protected $_dsn = 'mysql:host=127.0.0.1;dbname=codup';

  protected $_dbpass = "hggiHmfv";

  protected $_dbuser = "root";

  protected $layoutEnabled = true;

  protected function __construct()
  {
    if(null === $this->library){
      $this->library = __dir__;
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