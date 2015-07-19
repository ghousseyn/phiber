<?php

/**
 * https://code.google.com/p/php-class-html-generator/
 */

namespace Phiber\Ui;

class html
{

    private static $instance;

    private $top;

    private $tag;
    private $attributes = array();
    private $class;
    private $text = '';

    private $content;

    private $autoclosed = true;
    private $textFirst = false;

    public function __construct($tag, $top = null)
    {
        $this->tag = $tag;
        $this->top =& $top;
    }

    public static function createElement($tag = '')
    {
        self::$instance = new html($tag);
        return self::$instance;
    }

    public function addElement($tag)
    {

        if (null === $this->content) {
            $this->content = array();
            $this->autoclosed = false;
        }
        if ($tag instanceof html) {
            $htmlTag = $tag;
            $htmlTag->top = $this->top;
            $this->content[] = $htmlTag;
        } else {
            $htmlTag = new html($tag, (null === $this->top ? $this : $this->top));
            $this->content[] = $htmlTag;
        }
        return $htmlTag;
    }

    public function set($name, $value)
    {
        if (null === $this->attributes) $this->attributes = array();
        $this->attributes[$name] = $value;
        return $this;
    }

    public function id($value)
    {
        return $this->set('id', $value);
    }

    public function addClass($value)
    {
        if (null === $this->class)
            $this->class = array();
        $this->class[] = $value;
        return $this;
    }

    public function removeClass($class)
    {
        if (null !== $this->class) {
            unset($this->class[array_search($class, $this->class)]);
        }
        return $this;
    }

    public function setText($value)
    {
        $this->text = $value;
        return $this;
    }

    public function showTextBeforeContent($bool)
    {
        $this->textFirst = $bool;
    }

    public function __toString()
    {
        return (null === $this->top ? $this->toString() : $this->top->toString());
    }

    public function toString()
    {
        $string = '';
        if (!empty($this->tag)) {
            $string .= '<' . $this->tag;
            $string .= $this->attributesToString();
            if ($this->autoclosed && $this->text === '') $string .= '/>' . CHR(13) . CHR(10) . CHR(9);
            else $string .= '>' . ($this->textFirst ? $this->text . $this->contentToString() : $this->contentToString() . $this->text) . '</' . $this->tag . '>';
        } else {
            $string .= $this->contentToString();
        }
        return $string;
    }

    private function attributesToString()
    {
        $string = '';
        if (null !== $this->attributes) {
            foreach ($this->attributes as $key => $value) {
                if (!empty($value))
                    $string .= ' ' . $key . '="' . $value . '"';
            }
        }
        if (null !== $this->class && count($this->class) > 0) {
            $string .= ' class="' . implode(' ', $this->class) . '"';
        }
        return $string;
    }

    private function contentToString()
    {
        $string = '';
        if (null !== $this->content) {
            foreach ($this->content as $c) {
                $string .= CHR(13) . CHR(10) . CHR(9) . $c->toString();
            }
        }
        return $string;
    }
    public function getTag()
    {
        return $this->tag;
    }
}