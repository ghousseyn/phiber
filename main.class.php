<?php
class main {
	
	private	static $instance = null;
	
	private $route = array();
	private $path = null;
	protected $_layoutEnabled = false;
	protected $_requestVars = array();	
	protected $view = null;
	protected $vars = array();


	protected function __construct(){

		if(!isset($_SESSION)){
			session_start();
		}
				
		$this->stack(__class__);
	}
	
	static function getInstance(){
		if(null == self::$instance){
			self::$instance = new main;
		}
		return self::$instance;
	}
	function run(){
		$this->register('post',false);
		$this->register('get',true);
		$this->router();
		$this->getView();
		$this->dispatch();
		$this->view->showTime();
	
	}
	
	function getView(){	
			$path[] = $this->route['module'];
			$path[] = $this->route['controller'];
			$path[] = $this->route['action'];
			if($path[0] == 'default'){
				array_shift($path);
				$path = "./views/".implode("/",$path).".php";
			}else{
				$path = "./modules/".array_shift($path)."/views/".implode("/",$path).".php";
			}
			
 			
			$tpl = file_get_contents($path);
			$layout = null;

			if($this->_layoutEnabled){
				$layout = "./layouts/layout.php";

				if(file_exists($layout)){
					$layout = file_get_contents($layout);
				}
			}
			

		$this->view = $this->load('view');

		$this->view->viewPath = $path;	

		$this->register('view',$this->view);
		//var_dump($path);
	}
	function _view(){
		return $this->get('view');
	} 
	function renderLayout($layout = null){
		if(null != $layout){
			include_once $layout;
		}else{
			include_once "./layouts/layout.php";
		}
	}
	function router(){
		$uri  = urldecode($_SERVER['REQUEST_URI']);

		if($this->isValidURI($uri)){
			$parts = explode("/",$uri);
			array_shift($parts);
			$bootstrap = $this->load('bootstrap');
			
			if(!empty($parts[0]) && $bootstrap->isModule($parts[0])){
				
				$module = array_shift($parts);
				
				if($this->hasController($parts,$module)){
					$controller = array_shift($parts);

				}else{
					$controller = "index";
					array_shift($parts);
				}

				if($this->hasAction($parts, $controller)){
					$action = array_shift($parts);
				}else{
					$action = "index";
					array_shift($parts);
				}
			}else{
				$module = "default";
				array_shift($parts);
				$controller = "index";
				array_shift($parts);
				$action = "index";
				array_shift($parts);
			}
			
			
			
			if(count($parts)) {
				$this->setVars($parts);
			}
			if ($_POST) {
  				$this->register('post',true);
				$this->register('get',false);
		 		$this->_requestVars = $_POST;	
			}

			$this->register('_request',$this->_requestVars);

			$this->route = array("module" => $module, "controller" => $controller, "action" => $action, "vars" => $this->get('_request'));
			
		}else{		
			$route = array("module" => "default", "controller" => "index", "action" => "index"); 
			$this->route = $route;	
			$this->errorStack(__class__.":".__line__.": Error: Not a valid URI");
					
		}
	
	}
	function isValidURI($uri){
	
		if (preg_match("~^(?:[/\w\s-]+)+/?$~", $uri) ) {
    			return true;
		}
		return false;
	}
	function isPost(){
		return $this->get('post');
	}
	function isGet(){
		return $this->get('get');
	}
	function getRoute(){
		return $this->route;
	}
	function dispatch(){

		$mod = $this->route["module"];
		$controller = $this->route["controller"];
		$action = $this->route["action"];
	
		

		$instance = $this->load($controller,null,$this->path);
	
		//$instance->init();
		if(array_search($action, get_class_methods($instance))){
			return $instance->{$action}();
		}else{

			$this->errorStack(__class__.":".__line__.": Error: Action ".$action." does not exist!");
			return $instance->index();
		}
		
		
	}
	function _request($var){
		$vars = $this->get('_request');
		
		return $vars[$var];
	}
	function setVars($parts){
		
		foreach($parts as $k => $val){
			if($k == 0 || ($k%2) == 0){
				$this->_requestVars[$parts[$k]] = $parts[$k+1];
			}
		}
		
	}
	private function hasController($parts, $module){
		if($module == "default"){
			$this->path = "./";
		}else{
			$this->path = "./modules/".$module."/";		
		}
		
		if(!empty($parts[0]) && file_exists($this->path."class.".$parts[0].".php")){
			return true;
		}

		return false;
	} 
	private function hasAction($parts, $controller){
		
		if(!empty($parts[0])){
			return true;
		}
		return false;
	} 
	function register($name, $value){
		$_SESSION[$name] = $value;
	}
	
	protected function stack($msg){
		$_SESSION['stack'][] = $msg;
	}

	protected function stackFlush(){
		unset($_SESSION['stack']);
	}

	protected function errorStack($msg){
		$_SESSION['error'][] = $msg;
		$_SESSION['error'] = array_merge($_SESSION['error'], $_SESSION['stack']);
		
		$this->stackFlush();
		
	}

	protected function errorStackFlush(){
		
		unset($_SESSION['error']);
	}

	function get($index){
		if(array_key_exists($index, $_SESSION)){
			return $_SESSION[$index];
		}	
	}

	function load($class, $params = null, $path = ""){
		include_once $path."class.".$class.".php";
		
		$parameters = "";
		if(null != $params && is_array($params) && !is_object($params)){
			$parameters = implode(",",$params);
		}else{
			$parameters = $params;
		}
		return $class::getInstance($parameters);
	}
	
	function __set($var, $val){
		$this->vars[$var] = $val;
	}
	function __get($var){
		return $this->vars[$var];
	}
}
?>
