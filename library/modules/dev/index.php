<?php

class index extends Codup\main
{

    function index ()
    {
		$this->view->message = "Hello world";
		
    }

    function action ()
    {
        $this->stack("dev:action");
        //$this->db->select(array('translation', array('*'), ''));
        
        $this->view->text = "text from the dev module controller";
        $this->view->origin = __class__;
        $this->view->file = __file__;
    }
}
?>
