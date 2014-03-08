<?php

class index extends Phiber\controller
{

  public function main()
  {

    $this->view->message = "This is dev module controller - welcome to Phiber";

    //$log2 = $this->getLog('file',array('second','g:\\logfile2.txt'),'secondlog');
    //$this->get('log')->debug('hello from index!:'.__line__);

   // $log2->error('hello from index!:'.__line__);


   // $this->get('log')->debug('second hello from index!:'.__line__);

    //$this->get('secondlog')->debug('second hello from index!:'.__line__);
  }

  public function action()
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
