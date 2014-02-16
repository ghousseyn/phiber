<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Hussein Guettaf <ghussein@coda-dz.com>
 * @package     codup
 */
namespace Codup;

class main
{

    private $path = null;

    protected $_requestVars = array();

    protected $vars = array();

    protected function __construct ()
    {
        spl_autoload_register(array($this, 'autoload'));
	if ($this->conf->debug) {

            $this->debug->start();
        }        
        $this->register('layoutEnabled', $this->conf->layoutEnabled);
        if (! isset($_SESSION)) {
            session_start();
            $_SESSION['user']['activity'] = time();
        }
    
    }
    /*
     * Implement the getInstance() method
     */
    
    static function getInstance ()
    {
        return new static();
    }

    function run ()
    {
        
        $this->checkSession();
        $this->register('post', false);
        $this->register('get', true);
        $this->register('ajax', false);
        $this->register('context', null);
        $this->router();
        $this->getView();
        $this->plugins();
        $this->dispatch();
        if ($this->conf->debug) {
            $this->debug->output();
        }
        if ($this->isAjax()) {
            if ($this->get('context') == 'html') {
                
                $this->register('layoutEnabled', false);
                $this->view->showTime();
            }
        } else {
            $this->view->showTime();
        }
    
    }

    function _redirect ($url, $replace = true, $code = 307)
    {
        header("Location: $url", $replace, $code);
    }
    function sendJSON($arr)
    {
	if(is_string($arr)){
	    $this->sendJSON(json_decode($arr));
	}
        if(!is_array($arr)){
	    return false;
	}
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 16 Jul 1997 02:00:00 GMT');
	header('Content-type: application/json; charset=utf-8');
	echo json_encode($arr);
    }
    function plugins ()
    {
        
        foreach ($this->bootstrap->plugins as $plugin) {
            $this->Stack($plugin);
            $this->load($plugin, null, $this->conf->library . "/plugins/" . $plugin . "/")->run(__METHOD__);
        }
    }

    function checkSession ()
    {
        if ($this->conf->inactive) {
            if (isset($_SESSION['user']['activity']) &&
                     (time() - $_SESSION['user']['activity'] >
                     $this->load('tools')->orDefault( (int) $this->conf->inactive, 
                            1800))) {
                        
                        //session_unset();
                  session_destroy();
            }
        }
        if ($this->conf->sessionReginerate) {
            if (isset($_SESSION['user']['created']) &&
                     (time() - $_SESSION['user']['created'] >
                     $this->tools->orDefault(
                            (int) $this->conf->sessionReginerate, 1800))) {
                        session_regenerate_id(true);
                $_SESSION['user']['created'] = time();
            }
        }
       
    }

    function getView ()
    {

        $path[] = $this->route['module'];
        $path[] = $this->route['controller'];
        $path[] = $this->route['action'];
        
        if ($path[0] == 'default') {
            array_shift($path);
            $path = $this->conf->library . "/views/" . implode("/", $path) . ".php";
        } else {
            $path = $this->conf->library . "/modules/" . array_shift($path) . "/views/" . implode("/",$path) . ".php";
        }
                        
       // $tpl = file_get_contents($path);
        $layout = null;
                        
        if ($this->get('layoutEnabled')) {
            $layout = $this->conf->library . "/layouts/layout.php";
                                    
            if (file_exists($layout)) {
                 $layout = file_get_contents($layout);
            }
        }
                                
        $this->view->viewPath = $path;
                                
    }

    function _view ()
    {
        return $this->load('view');
    }

    function renderLayout ($layout = null)
    {
        if (null != $layout) {
             include_once $layout;
        } else {
             include_once $this->conf->library . "/layouts/layout.php";
        }
    }

    function router ()
    {
        $uri = urldecode($_SERVER['REQUEST_URI']);
                                                
        if ($this->isValidURI($uri)) {
            
            $parts = explode("/", $uri);
            
            array_shift($parts);
                                                    
            if (! empty($parts[0]) && $this->bootstrap->isModule($parts[0])) {
                                                        
                $module = array_shift($parts);
                                                        
                if ($this->hasController($parts, $module)) {
                    
                    $controller = array_shift($parts);
                    
                } else {
                    
                    $controller = "index";
                    
                    array_shift($parts);
                    
                }
                                                        
                if ($this->hasAction($parts, $controller, $module)) {
                    
                    $action = array_shift($parts);
                    
                } else {
                    $action = "index";
                    
                    array_shift($parts);
                    
                }
            } else {

                $module = "default";

                array_shift($parts);

                if ($this->hasController($parts, $module)) {

                    $controller = array_shift($parts);
                                                        
                } else {
                     $controller = "index";
                      // array_shift($parts);
                }
                
                if ($this->hasAction($parts,  $controller, $module)) {
                    
                    $action = array_shift($parts);

                } else {
                    $action = "index";
                    array_shift($parts);
                }

            }
                                                    
            if (count($parts)) {
                
                $this->setVars($parts);

            }

            if ($_POST) {
                $this->register('post', true);
                $this->register('get', false);
                $this->_requestVars = $_POST;
            }
                                                    
            $this->register('_request', $this->_requestVars);
                                                    
            $route = array("module" => $module, 
                                 "controller" => $controller, 
                                 "action" => $action, 
                                 "vars" => $this->get('_request'));
             if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                 $this->register('ajax', true);
             }
             
         } else {
$this->errorstack("URL not valid!");
             $route = array("module" => "default", 
                            "controller" => "index", 
                            "action" => "index");
                                        
        }
             $this->register('route', $route);

    }

    function isValidURI (&$uri)
    {
        $uri = str_replace("/?", "/", $uri);
        $uri = str_replace("?", "/?", $uri);
        $uri = str_replace("/?", "/", $uri);
        $uri = str_replace("&", "/", $uri);
        $uri = str_replace("=", "/", $uri);
                                        
        if (preg_match("~^(?:[/\w\s-]+)+/?$~", $uri)) {
            return true;
        }
            return false;
    }

    function isPost ()
    {        
        return $this->get('post');        
    }

    function isGet ()                     
    {        
        return $this->get('get');                                            
    }

    function isAjax ()                                    
    {
        return $this->get('ajax');
    }

    function getRoute ()                                    
    {
        return $this->route;
    }

    function dispatch ()                                    
    {
        //$mod = $this->route["module"];
        $controller = $this->route["controller"];
        $action = $this->route["action"];
         
        $instance = $this->load($controller, null, $this->path);
                                        
        if (array_search($action, get_class_methods($instance))) {
            return $instance->{$action}();
        } else {
            //$this->errorstack("No action: $action in $controller");
            return $instance->index();            
                               
        }
                            
                            
    }

    function contextSwitch ($context)                            
    {
        if ($context == 'html') {
            
            $this->register('context', 'html');
                                
        }
                            
    }

    function _request ($var)                                
    {
        $vars = $this->get('_request');
        if(is_array($vars) && in_array($var,$vars)){                        
        	return $vars[$var];
        }

    }

    function setVars ($parts)
    {
       foreach ($parts as $k => $val) {
          if ($k == 0 || ($k % 2) == 0) {
               $this->_requestVars[$parts[$k]] = $parts[$k + 1];
          }

       }                            
                            
    }

    private function hasController ($parts, $module)
    {
        if ($module == "default") {
            
            $this->path = $this->conf->library . "/"; 
                                           
        } else {
            
            $this->path = $this->conf->library . "/modules/" . $module . "/";
                                        
        }
                                        
            if (! empty($parts[0]) && file_exists($this->path . $parts[0] . ".php")) {
                
                return true;
                                        
            }
                                        
        return false;
                                    
    }

    private function hasAction ($parts,  $controller, $module)                                    
    {
                                        
        if ($module == "default") {
            
            $this->path = $this->conf->library . "/";
                                                
        } else {
            
            $this->path = $this->conf->library . "/modules/" . $module . "/";
                                                
        }
                                                
        if (! empty($parts[0]) && method_exists($this->load($controller, null, $this->path), $parts[0])) {
                                                    
            return true;
                                                
        }
                                                
                                                
        return false;
                                            
    }

                                            
    function register ($name, $value)                                            
    {
                                                
        $_SESSION[$name] = $value;
                                            
    }

    protected function stack ($msg)                                            
    {
                                                
        $_SESSION['stack'][] = $msg;
                                            
    }

    protected function stackFlush ()                                            
    {
                                                
        //unset($_SESSION['stack']);
                                            
    }

    protected function errorStack ($msg)                                            
    {

                                                
        $_SESSION['error'][] = $msg;

                                          
        //$this->stackFlush();
                                            
                                            
    }

    protected function errorStackFlush ()                                  
    {                                                
                                                
        unset($_SESSION['error']);
                                            
    }

    function get ($index)                                            
    {                                                
        if (array_key_exists($index, $_SESSION)) {
                                                    
            return $_SESSION[$index];
                                                
        }
                                            
    }

    function autoload($class){

            $path = __dir__."/";
            
            if(! strstr($class ,'\\')){
               
                if(file_exists($path.$class.".php")){
                    
                    include_once $path.$class.".php";
              
                }
                return;                 
        	}
        	//echo "loadin $path$class.php <br />";
        	$parts = explode('\\',$class);
        	
        	$i = 0;
        	if($parts[0] == '\\'){
        	    $i = 1;
        	}
        
        	$count = count($parts);
        	for($i; $i < $count;$i++){
        	    if($parts[$i] == 'Codup'){
        	        continue;
        	    }
        	    if($i == $count-1){
        	        $path .= $parts[$i].".php";
        	        break;
        	    }
        	    $path = $path. $parts[$i]."/";
        	 
        	}    
        
        	if(file_exists($path)){
        		include_once $path;
        	
        	}
     
       
  
    
    }
                    
    function load ($class, $params = null, $path = null)                                            
    {
      
        $newpath = $path;
        
        if (null == $newpath) {
                                                    
            $newpath = __DIR__ . "/";
                                                
        }
                                                
        $incpath = $newpath . $class . ".php";
                                                        
        $hash = substr(md5($incpath), 0, 8);
                                                        
        if ($this->isLoaded($hash)) {
                                                                        
            return $this->get($hash);
                                                        
        }
        
        $serialisable = array('config','debug','tools','view','bootstrap');
        
        if (! file_exists($incpath)) {
            
            return false;
                                                        
        }
                                                     
        	include_once($incpath);
      
                                                        
        $parameters = "";

        if (null != $params && is_array($params) && ! is_object($params)) {
             
            $parameters = implode(",", $params);
                                                        
        } else {
                                                            
            $parameters = $params;
                                                        
        }
        
        $instance = $class::getInstance($parameters);
            
        //if(in_array($class,$serialisable)){

            $this->register($hash, $instance);
        
       // }                                            
        
        return $instance;
                                                    
    }

    function isLoaded ($hash)                                                    
    {
        if (isset($_SESSION)) {
            
            if (key_exists($hash, $_SESSION)) {
                
                 return true;
                                                            
            }
                                                        
        }
                                                        
        return false;
                                                    
    }
	
    function __set ($var, $val)                                                    
    {
                                                                
        $this->vars[$var] = $val;
                                                    
    }

    function __get ($var)                                                    
    {
	$this->Stack($var);

        switch ($var) {
                                                            
            case 'view':
                                                                
                return $this->load('view');
                                                                
                break;
                                                            
            case 'route':
                                                                
                return $this->get('route');
                                                                
                break;
                                                            
            case 'content':

                return $this->viewPath;
                                                                
                break;
                                                            
            case 'db':
                                                                
                return $this->load('db');
                                                                
                break;
                                                            
            case 'conf':
                                                                
                return $this->load('config');
                                                                
                break;
            case 'bootstrap':
                
                return $this->load('bootstrap');
                
                break;
            case 'tools':
                
                return $this->load('tools');
                
                break;
            case 'debug':
                
                return $this->load('debug');
                
                break;
                                                        
        }
       //if(in_array($var, $this->vars)){                                                 
         	return $this->vars[$var];
      // }
                                                    
    }
                                                
}

                                                
?>
