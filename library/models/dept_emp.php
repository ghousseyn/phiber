<?php
namespace models;
use Codup;
class dept_emp extends model  
{
    public $emp_no;
    public $dept_no;
    public $from_date;
    public $to_date;
    public function getPrimary() 
	{
	    return array("emp_no","dept_no");
	}
    public function getPrimaryValue($key=null)
	{
		if(null === $key){
			return $this->getCompositeValue();
		}
		$pri = $this->getPrimary();
		if(in_array($key,$pri)){
			return $this->{$pri[$key]};
		}
	}
    public function getCompositeValue() 
	{
		return array(
				"emp_no" => $this->emp_no,
				"dept_no" => $this->dept_no,
				);
	}
    public function getRelations() 
    {
        return array('dept_no'=>'departments.dept_no','emp_no'=>'employees.emp_no',);
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
