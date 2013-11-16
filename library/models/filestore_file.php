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
        return $this->id;
    }
}
?>