<?php


class debug extends Codup\main{

    public $stack;

    private $timestart;

    protected $queries = array();

    protected $mem = null;
    
    protected static $dbg = null;

    protected function __construct ()
    {
       //xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        $this->mem = memory_get_usage();
        $this->timestart = microtime(true);
    }

    function start ()
    {
       
    }
    public static function getInstance(){
        return new self;
    }

    function execTime ()
    {
        return number_format((microtime(true) - $this->timestart), 4);
    }

    function memoryUsage ()
    {
        return $this->load('tools')->convertSize(memory_get_usage() - $this->mem);
    }

    function stackPush ($msg)
    {
        array_unshift($_SESSION['stack'], $msg);
    }

    function output ()
    {
	$errors = "";
	$steps = "";
	
	$error_label = "Errors";
	$steps_label = "Steps";

        if (isset($_SESSION['error']) && count($_SESSION['error'])) {
            $error_label = "<b class='active'>$error_label</b>(".count($_SESSION['error']).")";
            foreach ($_SESSION['error'] as $k => $en) {
                
                $errors .= "[$k] : $en <br />";
            }
        } elseif (isset($_SESSION['stack']) && count($_SESSION['stack'])) {
            
	    $tmp1 = $_SESSION['stack'];
	 
	    foreach($tmp1 as $key => $tmpel){
		
		    $tmp [$tmpel] = $key;
		
	    }

	    $tmp = array_keys($tmp);
	    $steps_label = "<b class='active'>$steps_label</b>(".count($tmp).")";
            foreach ($tmp as $key => $entry) {
                
                $steps .= "[$key] : $entry <br />";
            }
        }
        
       // unset($_SESSION['stack']);
	//$XHPROF_ROOT = "/home/hussein/Documents/www/bdd/codup/library/xhprof_lib/";
	//include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
	//include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";

	//$xhprof_runs = new XHProfRuns_Default("/tmp");
	//$xhprof_data = xhprof_disable();
	//$run_id = $xhprof_runs->save_run($xhprof_data, "xhprof_testing");
	
        $str = "<div class='bottom_bar'>";
        $str .= "<div class='bbtn' >$error_label<span ttip=''>" . $errors . "</span></div>|";
	$str .= "<div class='bbtn' >$steps_label<span ttip=''>" . $steps . "</span></div>|";
        $str .= "<div>Execution Time: " . $this->execTime() . "</div>|";
        $str .= "<div>Memory Usage: " . $this->memoryUsage() . "</div>";
	//$str .= "<a href='/xhprof_html/index.php?run={$run_id}&source=xhprof_testing\n'>profile this run</a>";
        $str .= "</div>";
        //parent::errorStackFlush();
        $this->view->debuginfo = $str;

    }
}

?>
