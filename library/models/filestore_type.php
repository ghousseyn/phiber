<?php
namespace models;
use Codup;
class filestore_type extends model  
{
    public $id;
    public $name;
    public $mime_type;
    public $extension;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>