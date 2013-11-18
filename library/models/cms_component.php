<?php
namespace models;
use Codup;
class cms_component extends model  
{    
  
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
        return array('cms_componenttype_id'=>'cms_componenttype.id',);
    }
    public function save()
    {     
    	parent::save($this);
    }
    
}
