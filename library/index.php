<?php

class index extends Codup\main
{

    function index ()
    {
        
        $this->view->text = "here's some text!";
    }

    function action ()
    {
        $this->stack("default:" . __method__);
    
    }
}

