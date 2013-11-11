<?php

class context extends main
{
    /*
     * Implement getInstance from app interface
     */
    static function getInstance ()
    {
        return new self();
    }
    /*
     * @override this to execute your code
     */
    function run ()
    {
        
        if ($this->isAjax()) {
            $this->register('context', 'html');
            echo "AJax";
        }
    }
}