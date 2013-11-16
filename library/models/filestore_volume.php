<?php
namespace models;
use Codup;
class filestore_volume extends model  
{
    public $id;
    public $name;
    public $dirname;
    public $total_space;
    public $used_space;
    public $stored_files_cnt;
    public $enabled;
    public $last_filenum;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>