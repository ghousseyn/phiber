<?php

class index extends Codup\controller
{
public static $test = 2;

    function main ()
    {
	echo self::$test;
	self::$test++;
	$this->view->message = "welcome nbn";
		
    }

    function action ()
    {
  
	$this->view->origin = __class__;
        $this->view->file = __file__;
	$this->view->text = "welcome to action";
$this->tools->wtf($_SESSION);
	if($this->isAjax()){
        	$this->sendJSON(array(0=>"this thing",1=>"another"));
		$this->contextSwitch('html');
	}
        
    }
}
?>
