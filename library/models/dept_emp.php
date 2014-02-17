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
        return "dept_no";
    }
    public function getPrimaryValue() 
    {
        return $this->dept_no;
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
