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
    public function id($id=null)
    {
        if(null != $id){
            $this->id = $id;
        }else{
            return $this->id;
        }
        
    }
    public function name($name=null)
    {
        if(null != $name){
        	$this->name = $name;
        }else{
        	return $this->name;
        }
    }
    public function cms_componenttype_id($cms_componenttype_id=null)
    {
        if(null != $cms_componenttype_id){
        	$this->cms_componenttype_id = $cms_componenttype_id;
        }else{
        	return $this->cms_componenttype_id;
        }
    }
    public function config($config=null)
    {
        if(null != $config){
        	$this->config = $config;
        }else{
        	return $this->config;
        }
    }
    public function is_enabled($is_enabled = null)
    {
        if(null != $is_enabled){
        	$this->is_enabled = $is_enabled;
        }else{
        	return $this->is_enabled;
        }
    }
  
}
