<?php
namespace entity;
use Codup;
class dept_manager extends entity  
{
  public $dept_no;
  public $emp_no;
  public $from_date;
  public $to_date;
  public function getPrimary()
  {
    return array("dept_no","emp_no");
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
            "dept_no" => $this->dept_no,
            "emp_no" => $this->emp_no,
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
