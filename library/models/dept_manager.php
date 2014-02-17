<?php
namespace models;
use Codup;
class dept_manager extends model  
{
    public $dept_no;
    public $emp_no;
    public $from_date;
    public $to_date;
    public function getPrimary() 
    {
        return "emp_no";
    }
    public function getPrimaryValue() 
    {
        return $this->emp_no;
    }
    public function getRelations() 
    {
        return array('dept_no'=>'departments.dept_no','emp_no'=>'employees.emp_no',);
    }
    public function save() 
    {
        parent::save($this);
    }
}
