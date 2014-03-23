<?php
namespace entity;
use Phiber;
class salaries extends entity
{
  public $emp_no;
  public $salary;
  public $from_date;
  public $to_date;
  public function getPrimary()
  {
    return array("emp_no","from_date");
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
            "from_date" => $this->from_date,
        );
  }
  public function getRelations()
  {
    return array('emp_no'=>'employees.emp_no',);
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
