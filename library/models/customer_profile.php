<?php
namespace models;
use Codup;
class customer_profile extends model  
{
    public $id;
    public $lid;
    public $gid;
    public $fname;
    public $lname;
    public $email2;
    public $address1;
    public $address2;
    public $zip;
    public $commune;
    public $daira;
    public $wilaya;
    public $owner;
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
        return array('gid'=>'customer_group.id',);
    }
}
