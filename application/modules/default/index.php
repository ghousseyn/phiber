<?php

class index extends \Phiber\controller
{
  public $testing;
  function main()
  {

    $this->view->text = "Text from default controller: " . __file__;
  }

  function action()
  {


  }

}

