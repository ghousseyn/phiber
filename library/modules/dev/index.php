<?php

class index extends Phiber\controller
{

  function main()
  {

    $this->view->message = "This is dev module controller - welcome to Phiber";

  }

  function action()
  {

    $this->view->origin = __class__;
    $this->view->file = __file__;
    $this->view->text = "welcome to action";

    if($this->isAjax()){
      $this->contextSwitch('json');
      $this->sendJSON(array(0 => "one thing", 1 => "another"));

    }

  }
}
?>
