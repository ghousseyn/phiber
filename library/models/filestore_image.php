<?php
namespace models;
use Codup;
class filestore_image extends model  
{
    public $id;
    public $name;
    public $original_file_id;
    public $thumb_file_id;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>