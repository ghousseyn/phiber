<?php
/**
 * Wire class.
 * @version    1.0
 * @author     Housseyn Guettaf <ghoucine@gmail.com>
 * @package    Phiber
 */
namespace Phiber;

class wire
{

    const PHIBER_SESSION_NAMESPACE = 'phiber';


    protected $vars = array();
    protected $confFile;

    public function __construct($confFile = null)
    {
        spl_autoload_register(array($this, 'autoload'), true, true);
        $this->confFile = $confFile;
    }

    public function baseUri($base)
    {
        if (null == $base || $base == '/') {
            return;
        }
        $base = '/' . trim($base, '/') . '/';
        $part = ltrim($_SERVER['REQUEST_URI'], $base);
        $_SERVER['REQUEST_URI'] = '/' . $part;
        $this->phiber->setBase($base);
    }

    public static function getInstance()
    {
        return new static();
    }

    public function boot()
    {
        phiber::getInstance()->run();
    }

    public function addRoute($rule, $route)
    {
        $this->phiber->addNewRoute($rule, $route);
    }

    public function addRoutesFile($path)
    {
        if (stream_resolve_include_path($path)) {
            $routes = include $path;
            if (is_array($routes)) {
                foreach ($routes as $route) {
                    $this->addRoute($route[0], $route[1]);
                }
            }

        }

    }

    protected function _redirect($url, $replace = true, $code = 307)
    {
        header("Location: $url", $replace, $code);
    }

    public function addLib($name, $src = null)
    {
        if (null === $src) {
            $src = $name;
        }

        $this->phiber->libs[$name] = $src;

    }

    private function isHttpMethod($method)
    {
        return (strtoupper($method) === strtoupper($this->phiber->method));
    }

    protected function isPost()
    {
        return count($_POST);
    }

    protected function isGet()
    {
        return $this->isHttpMethod('get');
    }

    protected function isPut()
    {
        return $this->isHttpMethod('put');
    }

    protected function isHead()
    {
        return $this->isHttpMethod('head');
    }

    protected function isDelete()
    {
        return $this->isHttpMethod('delete');
    }

    protected function isOptions()
    {
        return $this->isHttpMethod('options');
    }

    protected function isTrace()
    {
        return $this->isHttpMethod('trace');
    }

    protected function isAjax()
    {
        return $this->phiber->ajax;
    }

    protected function _requestParam($var, $default = null)
    {
        $vars = $this->phiber->request;
        if (is_array($vars) && isset($vars[$var])) {
            return $vars[$var];
        }
        return $default;

    }

    protected function register($name, $value)
    {
        $this->session->set($name, $value);
    }

    protected function get($index)
    {
        return $this->session->get($index);
    }

    protected function isFlagSet($flag)
    {
        return \Phiber\Flag\flag::_isset($flag, $this->get('phiber_flags'));
    }

    protected function setFlag($flag, $value)
    {
        $flags = $this->session->get('phiber_flags');
        \Phiber\Flag\flag::_set($flag, $value, $flags);
        $this->session->set('phiber_flags', $flags);
    }

    public function setLog($logger = null, $params = null, $name = null)
    {

        if (null === $logger) {
            $logger = $this->config->PHIBER_LOG_DEFAULT_HANDLER;
        } elseif (!file_exists($this->config->library . '/logger/' . $logger . '.php')) {
            throw new \Exception('Log handler ' . $logger . ' could not be located!');
        }
        if (null === $params) {
            $params = array('default', $this->config->logDir . '/' . $this->config->PHIBER_LOG_DEFAULT_FILE);
        }
        if (!is_array($params)) {
            $params = array($params, $this->config->logDir . '/' . $params . '.log');
        }

        $logWriter = "Phiber\\Logger\\$logger";
        $writer = new $logWriter($params, $this->config->logLevel);

        if (null == $name) {
            $name = 'log';
        }
        $writer->level = $this->config->logLevel;
        $this->phiber->logger[$name] = array($logger, $params);
        return $writer;
    }

    public function logger($name = 'log')
    {
        $logger = $this->phiber->logger;
        if (null !== $logger && isset($logger[$name])) {
            $log = $logger[$name];
            $class = "Phiber\\Logger\\$log[0]";
            if (stream_resolve_include_path($this->config->library . DIRECTORY_SEPARATOR . 'logger' . DIRECTORY_SEPARATOR . $log[0] . '.php')) {
                $logObject = new $class($log[1], $this->config->logLevel);
                return ($logObject instanceof Logger\logger) ? $logObject : $this->setLog();
            }

        }
        return $this->setLog();
    }

    protected function sendJSON($data, $options = null)
    {
        $this->view->disableLayout();
        $this->view->disableView();

        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: Mon, 16 Jul 1997 02:00:00 GMT');
        header('Content-type: application/json; charset=utf-8');

        echo json_encode($data, $options);
    }

    public function getObservers($event)
    {
        if (isset($this->phiber->observers[$event])) {
            return $this->phiber->observers[$event];
        }
    }

    public function removeObserver($event, $name)
    {
        if (isset($this->phiber->observers[$event][$name])) {
            unset($this->phiber->observers[$event][$name]);
            return true;
        }
        return false;
    }

    protected function hashObject()
    {
        $class = get_called_class();
        $reflect = new \ReflectionClass($class);
        return sha1($class . $reflect->getFileName());
    }

    protected function attach($event, $observer = null, $hash = null, $runMethod = null)
    {
        if (null === $observer) {
            $observer = $this;
        }
        if (null === $hash) {
            $hash = $this->hashObject();
        }
        return Event\eventfull::attach($observer, $event, $hash, $runMethod);
    }

    protected function detach($event, $observer = null, $hash = null)
    {
        if (null === $observer) {
            $observer = $this;
        }
        if (null === $hash) {
            $hash = $this->hashObject();
        }
        return Event\eventfull::detach($observer, $event, $hash);
    }

    protected function notify(Event\event $event)
    {
        Event\eventfull::notify($event);
    }

    public function autoload($class)
    {

        if ('Phiber\\config' === $class && null !== $this->confFile) {

            if (stream_resolve_include_path($this->confFile)) {
                require $this->confFile;
                return true;
            } else {
                trigger_error("Could not find configuration file: " . $this->confFile, E_USER_ERROR);
            }

        }
        $path = $this->config->library . DIRECTORY_SEPARATOR;
        if (strpos($class, '\\') === false) {

            if (stream_resolve_include_path($this->config->application . DIRECTORY_SEPARATOR . $class . '.php')) {

                require $this->config->application . DIRECTORY_SEPARATOR . $class . '.php';
                return;
            }

            $module = $this->config->application . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $this->route['module'] . DIRECTORY_SEPARATOR;

            if (stream_resolve_include_path($module . $class . '.php')) {

                require $module . $class . '.php';

            }
            return true;
        }

        $parts = explode('\\', $class);

        $count = count($parts);

        if ($parts[0] !== 'Phiber') {

            $libs = $this->phiber->libs;

            if (isset($libs[$parts[0]])) {

                $path = $this->config->application . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . $libs[$parts[0]] . DIRECTORY_SEPARATOR;
                unset($parts[0]);
            } else {
                $path = $this->config->application . DIRECTORY_SEPARATOR;

            }
        } else {
            unset($parts[0]);

        }
        $path .= strtolower(implode(DIRECTORY_SEPARATOR, $parts)) . '.php';

        if (stream_resolve_include_path($path)) {

            require $path;
            return;
        }

    }

    public function __set($var, $val)
    {

        $this->vars[$var] = $val;

    }

    /**
     * @property object $view    An instance of the view class
     * @property array $route    Current route
     * @property string $content The path to the selected template (partial view)
     * @property object $config  An instance of the config class
     * @param string $var        Property name
     */
    public function __get($var)
    {

        switch ($var) {

            case 'view':
                return view::getInstance();
            case 'route':
                return $this->phiber->currentRoute;
            case 'phiber_content_view_path':
                return $this->view->viewPath;
            case 'config':
                return config::getInstance();
            case 'session':
                return Session\session::getInstance();
            case 'phiber';
                return phiber::getInstance();
        }
        if (isset($this->vars[$var])) {
            return $this->vars[$var];
        }
        if (isset($this->phiber->vars[$var])) {
            return $this->phiber->vars[$var];
        }
    }
}

?>