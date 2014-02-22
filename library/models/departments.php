<?php
namespace models;
use Codup;
class departments extends model  
{
    public $dept_no;
    public $dept_name;
    public function getPrimary() 
	{
	    return array("dept_no");
	}
    public function getPrimaryValue($key=null)
	{
		if(null === $key){
			return array("dept_no" => $this->dept_no);
		}
		$pri = $this->getPrimary();
		if(in_array($key,$pri)){
			return $this->{$pri[$key]};
		}
	}
    public function getCompositeValue() 
	{
		return false;
	}
    public function getRelations() 
    {
        return array();
    }
    public function save() 
    {
        parent::save($this);
    }
    public function load() 
    {
        return parent::load($this);
    }
}
