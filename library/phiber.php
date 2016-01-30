<?php

/**
 * The main framework class
 * @version     1.0
 * @author      Housseyn Guettaf <ghoucine@gmail.com>
 * @package     Phiber
 */
namespace Phiber;


class phiber extends wire
{
    public $observers;
    public $libs = array();
    public $ajax = false;
    public $currentRoute;
    public $request;
    public $logger;
    public $actionPrefix = 'action';

    private static $instance;

    private $controller;
    private $path;
    private $uri;
    private $routes = array();
    private $_requestVars = array();
    private $plugins = array();
    private $stop = false;
    private $method;
    private $base = '/';
    private $events = array();


    const EVENT_BOOT = 'phiber.boot';

    const EVENT_SHUTDOWN = 'phiber.shutdown';

    const EVENT_DISPATCH = 'phiber.dispatch';

    const EVENT_URINOTFOUND = 'phiber.urinotfound';

    public static function getInstance()
    {
        if (null !== self::$instance) {
            return self::$instance;
        }
        return self::$instance = new self;
    }

    private function getRoutes()
    {
        return (count($this->routes)) ? $this->routes : null;
    }

    public function addNewRoute($rule, $route)
    {
        $this->routes[$rule] = $route;
    }

    public function setBase($base)
    {
        $this->base = $base;
    }

    public function getBase()
    {
        return $this->base;
    }
    private function isHttpMethod($method)
    {
        return (strtoupper($method) === strtoupper($this->method));
    }

    public function isPost()
    {
        return $this->isHttpMethod('post');
    }

    public function isGet()
    {
        return $this->isHttpMethod('get');
    }

    public function isPut()
    {
        return $this->isHttpMethod('put');
    }

    public function isHead()
    {
        return $this->isHttpMethod('head');
    }

    public function isDelete()
    {
        return $this->isHttpMethod('delete');
    }

    public function isOptions()
    {
        return $this->isHttpMethod('options');
    }

    public function isTrace()
    {
        return $this->isHttpMethod('trace');
    }

    public function isAjax()
    {
        return $this->ajax;
    }

    private function getPlugins()
    {

        $path = $this->config->application . DIRECTORY_SEPARATOR . 'plugins';
        $plugins = $path . DIRECTORY_SEPARATOR . 'plugins.php';

        if (stream_resolve_include_path($plugins)) {
            $pluginsList = include $plugins;
        } else {
            $pluginsList['g5475ff2f44a9102d8b7'] = 'phiber';
        }

        if ($this->config->PHIBER_MODE === 'production' && stream_resolve_include_path($plugins) && is_array($pluginsList)) {
            return $pluginsList;
        }

        /*
         * //Auto discovery
        *
        */

        foreach (new \DirectoryIterator($path) as $plugin) {
            if ($plugin->isDot()) {
                continue;
            }
            $dir = $path . "/" . $plugin->getFilename();
            if (is_dir($dir)) {
                $this->plugins[] = $plugin->getFilename();
            }
        }

        if (count(array_diff($pluginsList, $this->plugins)) !== 0 || count(array_diff($this->plugins, $pluginsList)) !== 0 || isset($pluginsList['g5475ff2f44a9102d8b7'])) {
            $code = '<?php return ' . tools::transcribe($this->plugins) . '; ?>';
            file_put_contents($plugins, $code);

        }

        return $this->plugins;

    }

    private function plugins()
    {
        $plugins = $this->getPlugins();
        if (count($plugins)) {
            foreach ($plugins as $plugin) {
                require $this->config->application . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . $plugin . '.php';
                $plugin::getInstance()->run();
            }
        }

    }

    private function uriNormalize($uri)
    {
        $needles = array('/?', '?', '/?', '&', '=');
        $replace = array('/', '/?', '/', '/', '/',);
        return trim(str_replace($needles, $replace, $uri));
    }

    private function router(array $routes = null)
    {

        $this->uri = urldecode($_SERVER['REQUEST_URI']);

        $this->method = $_SERVER['REQUEST_METHOD'];

        if ($this->isValidURI($this->uri)) {

            if (strpos($this->uri, '?') !== false) {

                if (count(explode('/', $this->uri)) < 3) {
                    $params = ltrim(strstr($this->uri, '?'), '?');
                    $this->setVars(explode('/', $this->uriNormalize($params)));
                    $this->uri = strstr($this->uri, '?', true);
                }

            }

            if (null !== $routes && ($this->routeMatchSimple($routes, $this->uri) || $this->routeMatchRegex($routes, $this->uri))) {

                return;
            }

            $this->uri = $this->uriNormalize($this->uri);

            $parts = explode("/", $this->uri);
            unset($parts[0]);
            $parts = array_values($parts);
            if (!empty($parts[0]) && is_dir($this->config->application . '/modules/' . $parts[0])) {

                $module = array_shift($parts);
                $controller = $this->hasController($parts, $module);
                $action = $this->hasAction($parts, $controller);

            } else {

                $module = 'default';
                array_shift($parts);
                $controller = $this->hasController($parts, $module);
                $action = $this->hasAction($parts, $controller);

            }
            if (count($parts)) {

                $this->setVars($parts);
            }

            if ($this->isPost()) {
                $this->_requestVars = $_POST;
            }

            $route = array('module' => $module, 'controller' => $controller, 'action' => $action, 'vars' => $this->_requestVars);
        } else {
            $route = array('module' => 'default', 'controller' => $this->config->PHIBER_CONTROLLER_DEFAULT, 'action' => $this->config->PHIBER_CONTROLLER_DEFAULT_METHOD);
            $this->path = $this->config->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'default' . DIRECTORY_SEPARATOR;

        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            $this->ajax = true;
        }
        $this->currentRoute = $route;
        $this->request = $this->_requestVars;
    }

    private function routeMatchSimple($routes, $current)
    {
        if (strpos($current, '~') !== false) {
            return;
        }
        if (isset($routes[$current])) {

            if (is_array($routes[$current])) {
                $this->route = $routes[$current];
            } elseif (is_callable($routes[$current])) {
                $fn = $routes[$current];
                $this->view->disableLayout();
                $this->view->disableView();
                $rt = $fn($this);
                if ($rt === true) {
                    return false;
                }
                $this->stop = true;
            } elseif ($this->isValidURI($routes[$current])) {

                $_SERVER['REQUEST_URI'] = $routes[$current];

                $this->router();

            }

            return true;
        }
        return false;
    }

    private function routeMatchRegex($routes, $current)
    {

        foreach ($routes as $def => $route) {
            if (strpos($def, '~') === false) {
                continue;
            }
            if (preg_match_all($def, $current, $matches)) {
                array_shift($matches);
                if (is_array($route)) {
                    if (isset($route['vars']) && is_array($route['vars'])) {

                        foreach ($route['vars'] as $key => $var) {
                            if (strpos($var, ':') !== false) {
                                $route['vars'][ltrim($var, ':')] = $matches[$key][0];
                                unset($route['vars'][$key]);
                            }
                        }
                    }
                    $this->route = $route;
                } elseif (is_callable($route)) {
                    $this->view->disableLayout();
                    $this->view->disableView();
                    $rt = $route($this);
                    if ($rt === true) {
                        return false;
                    }
                    $this->stop = true;
                } elseif (strpos($route, ':') !== false) {
                    $route = trim($route, '/');
                    $parts = explode('/', $route);
                    $url = '';
                    $pos = 0;
                    foreach ($parts as $k => $part) {
                        if (strpos($part, ':') !== false) {

                            $url .= ltrim($part, ':') . '/' . $matches[$pos][0] . '/';
                            $pos++;
                        } else {
                            $url .= $part . '/';
                        }
                    }
                    $url = '/' . rtrim($url, '/');
                    if ($this->isValidURI($url)) {
                        $_SERVER['REQUEST_URI'] = $url;
                        $this->router();
                    }

                } elseif ($this->isValidURI($route)) {

                    $_SERVER['REQUEST_URI'] = $route;
                    $this->router();

                }

                return true;
            }

        }
        return false;
    }

    private function isValidURI($uri)
    {

        if (preg_match('~^(?:[/\\w\\s-\,\$\.\*\!\'\(\)\~?=&]+)+/?$~u', $uri)) {

            return true;
        }
        return false;
    }

    public function run()
    {
        new \bootstrap\start($this);

        Event\eventful::notify(new Event\event(self::EVENT_BOOT, __class__));

        $this->router($this->getRoutes());

        $this->plugins();

        if (!$this->stop) {

            $this->dispatch();

        }
        Event\eventful::notify(new Event\event(self::EVENT_SHUTDOWN, __class__));

    }
    public function pushEvent(Event\event $event)
    {
        $this->events[$event->getName()][] = $event;
    }
    public function removeEvent(Event\event $event)
    {
        $eventIndex = array_search($event, $this->events[$event->getName()]);
        if ($eventIndex !== false) {
            unset($this->events[$event->getName()][$eventIndex]);
            return true;
        }
        return false;
    }
    public function getCurrentEvents()
    {
        return $this->events;
    }
    private function setVars($parts)
    {
        foreach ($parts as $k => $val) {
            if ($k == 0 || ($k % 2) == 0) {
                $value = (isset($parts[$k + 1])) ? $parts[$k + 1] : null;
                $this->_requestVars[$parts[$k]] = $value;
            }

        }

    }

    private function hasController(&$parts, $module)
    {

        $this->path = $this->config->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR;
        $controller = $this->config->PHIBER_CONTROLLER_DEFAULT;

        if (!empty($parts[0]) && stream_resolve_include_path($this->path . $parts[0] . '.php')) {

            $controller = array_shift($parts);

        }
        $this->controller = $this->loadController($controller);

        return $controller;

    }

    private function hasAction(&$parts, $controller)
    {

        if (!empty($parts[0]) && method_exists($this->controller, $this->actionPrefix . $parts[0])) {

            return $this->actionPrefix . ucfirst(array_shift($parts));

        }

        array_shift($parts);
        return $this->actionPrefix . ucfirst($this->config->PHIBER_CONTROLLER_DEFAULT_METHOD);

    }


    private function dispatch()
    {
        $action = $this->route['action'];
            if (is_callable(array($this->controller, $action))) {
                $this->controller->{$action}();
            } else {
                $event = new Event\event(self::EVENT_URINOTFOUND, __class__, 'Could not call specified action!', 'error');
                Event\eventful::notify($event);
                $action = $this->actionPrefix . $this->config->PHIBER_CONTROLLER_DEFAULT_METHOD;
                $this->controller->{$action}();
            }
        Event\eventful::notify(new Event\event(self::EVENT_DISPATCH, __class__));
    }

    private function loadController($controller)
    {
        require $this->path . $controller . '.php';
        return new $controller($this);
    }

    public function addObserver($name, $object)
    {
        $this->observers[$name] = $object;
    }

    public static function getEvents()
    {
        return array(self::EVENT_BOOT, self::EVENT_DISPATCH, self::EVENT_SHUTDOWN, self::EVENT_URINOTFOUND);
    }
}

?>