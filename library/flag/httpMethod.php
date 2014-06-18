<?php
namespace Phiber\flag;

use Phiber\Flag\flag;

class httpMethod extends flag
{
  const OPTIONS = self::ONE;
  const GET = self::TWO;
  const HEAD = self::THREE;
  const POST = self::FOUR;
  const PUT = self::FIVE;
  const DELETE = self::SIX;
  const TRACE = self::SEVEN;

  public static function setMethod(&$flags)
  {
    $method = $_SERVER['REQUEST_METHOD'];
    self::_set(constant('self::'.$method), true, $flags);
    return $method;
  }
}

?>