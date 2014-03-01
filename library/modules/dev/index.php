<?php

class index extends Codup\controller
{

  function main()
  {

    $this->view->message = "welcome nbn";

  }

  function action()
  {

    $this->view->origin = __class__;
    $this->view->file = __file__;
    $this->view->text = "welcome to action";

    if($this->isAjax()){
      $this->sendJSON(array(0 => "this thing", 1 => "another"));
      $this->contextSwitch('html');
    }

  }
}
?>
