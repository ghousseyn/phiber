<?php
namespace models;
use Codup;
class filestore_file extends model  
{
    public $id;
    public $filestore_type_id;
    public $filestore_volume_id;
    public $filename;
    public $original_filename;
    public $filesize;
    public $filenum;
    public $deleted;
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
        return array('filestore_volume_id'=>'filestore_volume.id','filestore_type_id'=>'filestore_type.id',);
    }
}
