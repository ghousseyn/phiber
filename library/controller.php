<?php
namespace Phiber;
class controller extends wire
{

  protected function sendJSON($data, $options = 0 , $depth = 512)
  {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 16 Jul 1997 02:00:00 GMT');
    header('Content-type: application/json; charset=utf-8');
    echo json_encode($data);
    exit(0);
  }

  protected function disableLayout()
  {
    $this->register('layoutEnabled', false);
  }

}
