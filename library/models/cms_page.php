<?php
namespace models;
use Codup;
class cms_page extends model  
{
    public $id;
    public $name;
    public $api_layout;
    public $page_layout;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>