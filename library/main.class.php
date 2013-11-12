<?php

/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */

class main implements app
{

    private $route = array();

    private $path = null;

    protected $_requestVars = array();

    protected $vars = array();

    protected $bootstrap = null;

    protected $plugins = null;

    protected $config = null;

    protected $debug = null;

    protected $context = null;

    protected function __construct ()
    {
        
        $this->config = $this->load('config');
        if ($this->config->debug) {
            $this->debug = $this->load('debug');
            $this->debug->start();
        }
        $this->register('layoutEnabled', $this->config->layoutEnabled);
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
        return new self();
    }

    function run ()
    {
        
        $this->checkSession();
        $this->bootstrap = $this->load('bootstrap');
        $this->register('post', false);
        $this->register('get', true);
        $this->register('ajax', false);
        $this->register('context', null);
        $this->router();
        $this->getView();
        $this->plugins();
        $this->dispatch();
        if ($this->config->debug) {
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

    function plugins ()
    {
        
        foreach ($this->bootstrap->plugins as $plugin) {
            $this->load($plugin, null, $this->config->library . "/plugins/" . $plugin . "/")->run();
        }
    }

    function checkSession ()
    {
        if ($this->config->inactive) {
            if (isset($_SESSION['user']['activity']) &&
                     (time() - $_SESSION['user']['activity'] >
                     $this->load('tools')->orDefault($this->config->inactive, 
                            1800))) {
                        
                        session_unset();
                // session_destroy();
            }
        }
        if ($this->config->sessionReginerate) {
            if (isset($_SESSION['user']['created']) &&
                     (time() - $_SESSION['user']['created'] >
                     $this->load('tools')->orDefault(
                            $this->config->sessionReginerate, 1800))) {
                        session_regenerate_id(true);
                $_SESSION['user']['created'] = time();
            }
        }
        // $this->stack("Inactivity: ".($this->load('tools')->convertTime(time()
    // - $_SESSION['user']['activity'])));
    }

    function getView ()
    {
        $this->view = $this->load('view');
        $path[] = $this->route['module'];
        $path[] = $this->route['controller'];
        $path[] = $this->route['action'];
        if ($path[0] == 'default') {
            array_shift($path);
            $path = $this->config->library . "/views/" . implode("/", $path) . ".php";
        } else {
            $path = $this->config->library . "/modules/" . array_shift($path) . "/views/" . implode("/",$path) . ".php";
        }
                        
        $tpl = file_get_contents($path);
        $layout = null;
                        
        if ($this->get('layoutEnabled')) {
            $layout = $this->config->library . "/layouts/layout.php";
                                    
            if (file_exists($layout)) {
                 $layout = file_get_contents($layout);
            }
        }
                                
        $this->view->viewPath = $path;
                                
        $this->register('view', $this->view);
                                
    }

    function _view ()
    {
        return $this->get('view');
    }

    function renderLayout ($layout = null)
    {
        if (null != $layout) {
             include_once $layout;
        } else {
             include_once $this->config->library . "/layouts/layout.php";
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
                                                    
            $this->route = array("module" => $module, 
                                 "controller" => $controller, 
                                 "action" => $action, 
                                 "vars" => $this->get('_request'));
             if (! empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                 $this->register('ajax', true);
             }
             
         } else {
             $route = array("module" => "default", 
                            "controller" => "index", 
                            "action" => "index");
             $this->route = $route;
             $this->errorStack( __class__ . ":" .__line__ . ": Error: Not a valid URI");
                                        
        }
             $this->register('route', $this->route);

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
        $mod = $this->route["module"];
        $controller = $this->route["controller"];
        $action = $this->route["action"];
                                        
        $instance = $this->load($controller, null, $this->path);
                                        
        if (array_search($action, get_class_methods($instance))) {
            return $instance->{$action}();
        } else {
            $this->errorStack( __class__ . ":" . __line__ . ": Error: Action " . $action . " does not exist!");
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
                                
        return $vars[$var];

    }

    function setVars ($parts)
    {
       foreach ($parts as $k => $val) {
          if ($k == 0 || ($k % 2) == 0) {
               $this->_requestVars[str_replace(" ", "_", $parts[$k])] = $parts[$k + 1];
          }

       }                            
                            
    }

    private function hasController ($parts, $module)
    {
        if ($module == "default") {
            
            $this->path = $this->config->library . "/"; 
                                           
        } else {
            
            $this->path = $this->config->library . "/modules/" . $module . "/";
                                        
        }
                                        
            if (! empty($parts[0]) && file_exists($this->path . "class." . $parts[0] . ".php")) {
                
                return true;
                                        
            }
                                        
        return false;
                                    
    }

    private function hasAction ($parts,  $controller, $module)                                    
    {
                                        
        if ($module == "default") {
            
            $this->path = $this->config->library . "/";
                                                
        } else {
            
            $this->path = $this->config->library . "/modules/" . $module . "/";
                                                
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
                                                
        unset($_SESSION['stack']);
                                            
    }

    protected function errorStack ($msg)                                            
    {
                                                
        $_SESSION['stack'] = array();
                                                
        $_SESSION['error'][] = $msg;
                                                
        $_SESSION['error'] = array_merge($_SESSION['error'], $_SESSION['stack']);                                                
                                                
        $this->stackFlush();
                                            
                                            
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

    function load ($class, $params = null, $path = null)                                            
    {
                                                
        if (null == $path) {
                                                    
            $path = __DIR__ . "/";
                                                
        }
                                                
        $incpath = $path . "class." . $class . ".php";
                                                        
        $hash = substr(md5($incpath), 0, 8);
                                                        
        if ($this->isLoaded($hash)) {
                                                                        
            return $this->get($hash);
                                                        
        }
        if (! file_exists($incpath)) {
                                                            
            return;
                                                        
        }
                                                        
        include_once $incpath;
                                                        
        $parameters = "";

        if (null != $params && is_array($params) && ! is_object($params)) {
             
            $parameters = implode(",", $params);
                                                        
        } else {
                                                            
            $parameters = $params;
                                                        
        }
            $instance = $class::getInstance($parameters);
            
            $this->register($hash, $instance);
                                                        
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
                                                        
        }
                                                        
        return $this->vars[$var];
                                                    
    }
                                                
}

interface app
{
                                                    
    static function getInstance ();
                                                
}
                                                
?>
