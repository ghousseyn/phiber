<?php

/**
 * Configuration class.
 * @version 	1.0
 * @author 	Hussein Guettaf <ghussein@coda-dz.com>
 * @package 	codup
 */
class view extends main
{

    protected $vars = array();

    static function getInstance ()
    {
        
        return new self();
    }

    function showTime ()
    {
        
        if ($this->get('layoutEnabled')) {
            $this->renderLayout();
        } else {
            include $this->viewPath;
        }
    
    }

}
?>
