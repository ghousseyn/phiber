<?php

/**
 * https://code.google.com/p/php-class-html-generator/
 */

namespace Phiber\Ui;

class html
{

    private static $instance = null;

    private $top = null;

    private $tag = null;
    private $attributes = array();
    private $class = null;
    private $text = '';

    private $content = null;

    private $autoclosed = true;
    private $textFirst = false;

    public function __construct($tag, $top = null){
        $this->tag = $tag;
        $this->top =& $top;
    }

    public static function createElement($tag = ''){
        self::$instance = new html($tag);
        return self::$instance;
    }

    public function addElement($tag){
        $htmlTag = null;
        if(is_null($this->content)){
            $this->content = array();
            $this->autoclosed = false;
        }
        if(is_object($tag) && get_class($tag) == get_class($this)){
            $htmlTag = $tag;
            $htmlTag->top = $this->top;
            $this->content[] = $htmlTag;
        }
        else{
            $htmlTag = new html($tag, (is_null($this->top) ? $this : $this->top ));
            $this->content[] = $htmlTag;
        }
        return $htmlTag;
    }

    public function set($name,$value){
        if(is_null($this->attributes)) $this->attributes = array();
        $this->attributes[$name] = $value;
        return $this;
    }

    public function id($value){
        return $this->set('id',$value);
    }

    public function addClass($value){
        if(is_null($this->class))
            $this->class = array();
        $this->class[] = $value;
        return $this;
    }

    public function removeClass($class){
        if(!is_null($this->class)){
            unset($this->class[array_search($class, $this->class)]);
            // foreach($this->class as $key=>$value){
            // if($class == $value)
            // $this->class[$key] = '';
            // }
        }
        return $this;
    }

    public function setText($value){
        $this->text = $value;
        return $this;
    }

    public function showTextBeforeContent($bool){
        $this->textFirst = $bool;
    }

    public function __toString(){
        return (is_null($this->top) ? $this->toString() : $this->top->toString() );
    }

    public function toString(){
        $string = '';
        if(!empty($this->tag)){
            $string .=  '<' . $this->tag;
            $string .= $this->attributesToString();
            if($this->autoclosed && empty($this->text)) $string .= '/>' . CHR(13) . CHR(10) . CHR(9);
            else $string .= '>' . ($this->textFirst ?  $this->text.$this->contentToString() : $this->contentToString().$this->text ). '</' . $this->tag . '>';
        }
        else{
            $string .= $this->contentToString();
        }
        return $string;
    }

    private function attributesToString(){
        $string = '';
        if(!is_null($this->attributes)){
            foreach($this->attributes as $key => $value){
                if(!empty($value))
                    $string .= ' ' . $key . '="' . $value . '"';
            }
        }
        if(!is_null($this->class) && count($this->class) > 0 ){
            $string .= ' class="' . implode(' ',$this->class) . '"';
        }
        return $string;
    }

    private function contentToString(){
        $string = '';
        if(!is_null($this->content)){
            foreach($this->content as $c){
                $string .= CHR(13) . CHR(10) . CHR(9) . $c->toString();
            }
        }
        return $string;
    }
}