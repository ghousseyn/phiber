<?php
namespace models;
use Codup;
class blog_post extends model  
{
    public $id;
    public $title;
    public $body;
    public $date;
    public function getPrimary() 
    {
        return $this->id;
    }
}
?>