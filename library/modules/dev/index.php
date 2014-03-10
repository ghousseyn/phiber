<?php

class index extends Phiber\controller
{

  public function main()
  {
    $variableOne = 'test';

    $variableTwo = 'var';

    $variableThree = 32156;

    echo $undefined;

    $this->view->message = "This is dev module controller - welcome to Phiber";

    $this->get('log')->notice('hello from index!:' . __line__);

    $vars = 'scope test';

    $log2 = $this->setLog('file', 'second', 'secondlog');

    $this->get('log')->notice('hello from index!:' . __line__, array('message' => $this->view->message));

    $log2->info('hello from index!:' . __line__);

    $this->logger()->debug('second hello from index!:' . __line__);

    $this->get('secondlog')->debug('second hello from index!:' . __line__);

    trigger_error(' A triggered E_USER_WARNING', E_USER_WARNING);
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
