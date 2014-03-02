<?php

class index extends Codup\controller
{

  function main()
  {

    $this->view->text = "Text from default controller: " . __file__;
  }

  function action()
  {


  }
}

