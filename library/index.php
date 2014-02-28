<?php

class index extends Codup\main
{

  function index()
  {
    
    $this->view->text = "Text from default controller: " . __file__;
  }

  function action()
  {
    $this->stack("default:" . __method__);
  
  }
}

