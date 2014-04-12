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

    $this->setFlag($this->active, true);
    $this->setFlag($this->member, true);
    $this->setFlag($this->online, true);
    $this->setFlag($this->author, true);
    $this->setFlag($this->editor, true);

    $this->view->message = "Error/Log test";

    $variableOne = 'test';

    $variableTwo = 'var';

    $variableThree = 32156;

  echo $undefined;





    $this->logger()->notice('hello from index!:' . __line__);

    $vars = 'scope test';

    $log2 = $this->setLog('file', 'second', 'secondlog');

    $this->logger()->notice('hello from index!:' . __line__, array('message' => $this->view->message));

    $log2->info('hello from second log index!:' . __line__);

    $this->logger()->debug('second hello from index!:' . __line__);

    $this->logger('secondlog')->debug('second log hello from index!:' . __line__);
    try{

      trigger_error(' A triggered E_USER_WARNING', E_USER_WARNING);

    }catch(Exception $e){
      echo "catched an error";
    }
  }

  public function action()
  {

    if($this->isAjax()){
      $this->contextSwitch('json');
      $this->sendJSON("or another");

    }

    test::getInstance()->db();
    if($this->isFlagSet($this->active)){
      echo 'User is Active','<br>';
    }else{
      echo 'User is not Active','<br>';
    }
    $this->register('t','guettaf');
    $this->view->origin = __class__;
    $this->view->file = __file__;
    $this->view->text = "welcome to action";


  }

}
?>
