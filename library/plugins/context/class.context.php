<?php

class context extends Codup\main
{

    function run ()
    {
         
        if ($this->isAjax()) {
            $this->register('context', 'html');
            echo "AJax";
        }
    }
}