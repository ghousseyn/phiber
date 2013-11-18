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
        return "id";
    }
    public function getPrimaryValue() 
    {
        return $this->id;
    }
    public function getRelations() 
    {
        return array('thumb_file_id'=>'filestore_file.id','original_file_id'=>'filestore_file.id',);
    }
}
