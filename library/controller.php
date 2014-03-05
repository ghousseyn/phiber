<?php
namespace Phiber;
class controller extends main
{

  protected function sendJSON($arr, $options = 0 , $depth = 512)
  {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 16 Jul 1997 02:00:00 GMT');
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($arr);
    return true;
  }

  protected function disableLayout()
  {
    $this->register('layoutEnabled', false);
  }

}
