<?php

/**
 * Tools class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */

class tools
{

    public static function getInstance(){
	return new self();
    }

    function convertTime ($size)
    {
        if (0 == $size) {
            return;
        }
        $s = array('s', 'min', 'H');
        $e = floor(log($size) / log(60));
        
        return sprintf('%d ' . $s[$e], ($size / pow(60, floor($e))));
    }

    function convertSize ($size)
    {
        if (0 == $size) {
            return;
        }
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' .$unit[$i];
    }

    function orDefault ($value, $default)
    {
        if (null != $value) {
            return $value;
        }
        return $default;
  	}
  	/*
  	 * Thanks to Aaron Fisher
  	 * 
  	 * http://www.aaron-fisher.com/articles/web/php/wtf/
  	 * 
  	 */
  	function wtf($var, $arrayOfObjectsToHide=array(), $fontSize=12)
  	{
  		$text = print_r($var, true);
  		$text = str_replace('<', '&lt;', $text);
  		$text = str_replace('>', '&gt;', $text);
  		foreach ($arrayOfObjectsToHide as $objectName) {
  			$searchPattern = '#(\W'.$objectName.' Object\n(\s+)\().*?\n\2\)\n#s';
  			$replace = "$1<span style=\"color: #FF9900;\">";
  			$replace .= "--&gt; HIDDEN - courtesy of wtf() &lt;--</span>)";
  			$text = preg_replace($searchPattern, $replace, $text);
  		}
  		// color code objects
  		$text = preg_replace(
  				'#(\w+)(\s+Object\s+\()#s',
  				'<span style="color: #079700;">$1</span>$2',
  				$text
  		);
  		// color code object properties
  		$pattern = '#\[(\w+)\:(public|private|protected)\]#';
  		$replace = '[<span style="color: #000099;">$1</span>:';
  		$replace .= '<span style="color: #009999;">$2</span>]';
  		$text = preg_replace($pattern, $replace, $text);
  		echo '<pre style="
  		font-size: '.$fontSize.'px;
  		line-height: '.$fontSize.'px;
  		background-color: #fff; padding: 10px;
  		">'.$text.'</pre>
  		';
  	}
}
