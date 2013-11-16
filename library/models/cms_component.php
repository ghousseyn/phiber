<?php
namespace models;
use Codup;
class cms_component extends model  
{
    public $id;
    public $name;
    public $cms_componenttype_id;
    public $config;
    public $is_enabled;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>