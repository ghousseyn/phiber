<?php
namespace Phiber\Flag;
class flag
{

  const ONE = 1;
  const TWO = 2;
  const THREE = 4;
  const FOUR = 8;
  const FIVE = 16;
  const SIX = 32;
  const SEVEN = 64;
  const TEN = 128;
  const ELEVEN = 256;
  const THIRTEEN = 512;
  const FOURTEEN = 1024;
  const FIFTEEN = 2048;
  const SIXTEEN = 4096;
  const SEVENTEEN = 8192;
  const EIGHTEEN = 16384;
  const NINETEEN = 32768;
  const TWENTY = 65536;

  public static function _isset($flag, $flags)
  {
    return (($flags & $flag) == $flag);
  }
  public static function _set($flag, $value, &$flags)
  {

    if($value){
      $flags |= $flag;
    }else{
      $flags &= ~$flag;
    }

  }
}

?>