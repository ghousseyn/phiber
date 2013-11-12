<?php

class debug extends Codup\main
{

    public $stack;

    private $timestart;

    protected $queries = array();

    protected static $dbg = null;

    protected function __construct ()
    {

    }

    static function getInstance ()
    {
        
        return new self();
    }

    function start ()
    {
        $this->timestart = microtime(true);
    }

    function execTime ()
    {
        return number_format((microtime(true) - $this->timestart), 4);
    }

    function memoryUsage ()
    {
        
        return $this->load('tools')->convertSize(memory_get_usage());
    }

    function stackPush ($msg)
    {
        array_unshift($_SESSION['stack'], $msg);
    }

    function stackTrace ()
    {
        $str = "";
        if (isset($_SESSION['error']) && count($_SESSION['error'])) {
            $str = "<br /> ================== Errors ================<br />";
            
            foreach ($_SESSION['error'] as $k => $en) {
                
                $str .= "[$k] : $en <br />";
            }
        } elseif (isset($_SESSION['stack']) && count($_SESSION['stack'])) {
            $str .= "<br /> ================== Steps =================<br />";
            foreach ($_SESSION['stack'] as $key => $entry) {
                
                $str .= "[$key] : $entry <br />";
            }
        }
        
        unset($_SESSION['stack']);
        
        return $str;
    }

    function output ()
    {
        $str = "<br />-------------------------Debug output-------------------------<br />";
        $str .= "Stack: <br />" . $this->stackTrace() . "<br />";
        $str .= "Execution Time: " . $this->execTime() . "<br />";
        $str .= "Memory Usage: " . $this->memoryUsage() . "<br />";
        
        parent::errorStackFlush();
        $this->view->debuginfo = $str;
    }
}

?>
