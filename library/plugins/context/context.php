<?php

class context extends Codup\main
{
    
   
    function run ($method)
    {
      

         $dept_manager = models\dept_manager::getInstance();

         //SELECT * FROM employees WHERE last_name like "Mal%"
         //$res = $test->find(array('last_name' => "Mal%"),'like')->fetch();
	     //$res = $test->select()->where('last_name like ?',"Mal%")->fetch();
	  
         /*
          * 
          * SELECT 	dept_manager.dept_no, 
          * 		dept_manager.emp_no, 
          * 		dept_manager.from_date, 
          * 		dept_manager.to_date ,
          * 		departments.dept_name ,
          * 		employees.last_name 
          * FROM 	dept_manager 
          *     JOIN 	departments 
          *       ON 		dept_manager.dept_no = departments.dept_no 
          *     JOIN 	employees 
          *       ON 		dept_manager.emp_no = employees.emp_no 
          * WHERE dept_manager.emp_no = 110039
          * 
          * $res = $dept_manager->find(110039)
					->with(array('departments'=>array("dept_name")))
					->with(array('employees' => array("last_name")))
					->fetch();
          * 
          * 
          * ============================================================================
          * 
          * 
          * SELECT 	dept_manager.*,
          * 		departments.dept_name ,
          * 		employees.last_name 
          * FROM 	dept_manager 
          *     JOIN 	departments 
          *       ON 		dept_manager.dept_no = departments.dept_no 
          *     JOIN 	employees 
          *       ON 		dept_manager.emp_no = employees.emp_no 
          * LIMIT 	0,20
          */
		$res = $dept_manager->select()//or $test->findAll()
					->with(array('departments'=>array("dept_name"),'employees' => array("last_name")))
					->limit(0,20)
					->fetch();
         
         //var_dump($test->errors);

        // var_dump($res);
       // var_dump($res);

       // var_dump($res);
         /*
         $res->getObject()->name = "guettaf5";
         $res->getObject()->save();
        */
         
       //  echo self::$t;
         if(!$res){
             var_dump($test->geterrors);
             return;
         }
         while ( $r = $res->iterate()){
         //$test2 = (array) $res; 
         //echo $r->last_name." ".$r->first_name."<br/>";
         echo $r->dept_name." ".$r->last_name."</br>";
         //$r->cms_componenttype_id = 7;
         //$r->is_enabled = 'Y';    
         //$r->save();

         }
         //var_dump($res);
        
        // ;
        /*
         

           */
        if ($this->isAjax()) {
            $this->register('context', 'html');
            echo "AJax";
        }
    }
}
