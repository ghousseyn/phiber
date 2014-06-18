<?php
namespace Phiber;
class controller extends wire
{

  protected function disableLayout()
  {
    $this->register('layoutEnabled', false);
  }

}
