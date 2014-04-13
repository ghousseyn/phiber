<?php

class index extends Phiber\controller
{
  public function main()
  {

    $this->setFlag(lib\myflags::MY_FLAG, true);
    $this->setFlag(lib\myflags::SIX, false);

    $this->setFlag(Phiber\Flag\flag::FOURTEEN, true);
    $this->setFlag(Phiber\Flag\flag::TEN, true);

    $test = new lib\myflags;
    $test2 = new lib\listenerExample;

    \Phiber\Session\session::attach($test,\Phiber\Session\session::EVENT_DESTR);
    \Phiber\Event\eventfull::attach($test,'main.test');

    \Phiber\Event\eventfull::notify(new \Phiber\Event\event('main.test', __METHOD__));
    \Phiber\Session\session::attach($test2);

   // \Phiber\Session\session::detach($test2,\Phiber\Session\session::EVENT_DESTR);
    $this->view->t = "there you go again";
   // echo dechex(1024);

    tools::wtf($_SESSION);

  }

}

?>