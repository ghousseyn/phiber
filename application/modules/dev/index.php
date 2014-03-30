<?php

class index extends Phiber\controller
{

  private $active = Phiber\Flag\flag::ONE;
  private $member = Phiber\Flag\flag::TWO;
  private $online = Phiber\Flag\flag::THREE;
  private $author = Phiber\Flag\flag::FOUR;
  private $editor = Phiber\Flag\flag::FIVE;

  public function main()
  {
    $this->setLocalFlag($this->active, true);
    $this->setLocalFlag($this->member, true);
    $this->setGlobalFlag($this->online, true);
    $this->setGlobalFlag($this->author, true);
    $this->setLocalFlag($this->editor, true);

    $authorized = false;
    $row = array('description'=> 'test data', 'status'=> 'active');
    $this->view->message = "<li>".$row['description']. "(" . ($authorized ? $row['status'] : "N/A"). ")</li>";

    $variableOne = 'test';

    $variableTwo = 'var';

    $variableThree = 32156;

    echo $undefined;



    $this->get('log')->notice('hello from index!:' . __line__);

    $vars = 'scope test';

    $log2 = $this->setLog('file', 'second', 'secondlog');

    $this->get('log')->notice('hello from index!:' . __line__, array('message' => $this->view->message));

    $log2->info('hello from second log index!:' . __line__);

    $this->logger()->debug('second hello from index!:' . __line__);

    $this->get('secondlog')->debug('second hello from index!:' . __line__);

    trigger_error(' A triggered E_USER_WARNING', E_USER_WARNING);
  }

  public function action()
  {
    $this->setGlobalFlag($this->active, true);

    test::getInstance()->db();
    if($this->isLocalFlagSet($this->active)){
      echo 'User is Active','<br>';
    }else{
      echo 'User is not Active','<br>';
    }
    $this->register('t','guettaf');
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
