<?php
namespace models;
use Codup;
class employees extends model  
{
    public $emp_no;
    public $birth_date;
    public $first_name;
    public $last_name;
    public $gender;
    public $hire_date;
    public function getPrimary() 
	{
	    return array("emp_no");
	}
    public function getPrimaryValue($key=null)
	{
		if(null === $key){
			return array("emp_no" => $this->emp_no);
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
