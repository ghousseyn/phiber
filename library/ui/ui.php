<?php


namespace Phiber\Ui;


use Phiber\phiber;

class ui extends phiber
{

    public $html;

    public function __construct()
    {
        $this->html = html::createElement();
    }
}