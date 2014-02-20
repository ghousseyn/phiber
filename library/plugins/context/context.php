<?php

class context extends Codup\main
{
    
   
    function run ($method)
    {
      

         $dept_manager = models\dept_manager::getInstance();

         //SELECT * FROM employees WHERE last_name like "Mal%"
         //$test = models\employees::getInstance();
         //$res = $test->find(array('last_name' => "Mal%"),'like')->fetch();
         // or
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
								->with(array('departments' => array("dept_name")))
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
		$deps = models\departments::getInstance();/* 
         try {
		 $deps->dept_no = "d013";
		 $deps->dept_name = "new dep 4";
		 $deps->save();
		 
         }catch (Exception $e){
         	
         	echo $e->errorInfo[2];
         	
         }
         $deps->delete(array("dept_no = ?", "d012"));
         */
		  $res = $dept_manager->find(10022)//or $dept_manager->findAll()
							  ->with(array('departments'=>array("dept_name"),'employees'))
							  ->fetch();
         $this->tools->wtf($deps);
         //var_dump($test->errors);

        // var_dump($res);
       // var_dump($res);

       // var_dump($res);
         
      //echo count($res);
         
       // foreach( $res as $record){
        	//foreach($record as $key => $rec){
        	//	echo $record->last_name."</br>";
        	//}
      //  }
         /*
         $dept = $res->Object()->departments();
         $dept->dept_name = "Finance again";
         //won't work (trying to change a primary key here) try using update() instead BAD idea though!
         //$dept->dept_no = "d010";
         $dept->save();
         var_dump($dept);
     
         
       //  echo self::$t;
         if(!$res){
             var_dump($test->geterrors);
             return;
         }
         while ( $r = $res->iterate()){
         //$test2 = (array) $res; 
         //echo $r->last_name." ".$r->first_name."<br/>";
         
        // $employees = $r->employees();
        // if($employees->gender == "M"){
         //	$employees->gender = "F";
        // }
        // $employees->save();
         echo $r->dept_name." ".$r->last_name." ".$r->first_name." ".$r->gender."</br>";
         //$r->cms_componenttype_id = 7;
         //$r->is_enabled = 'Y';    
         //$r->dept_name = "Made up dept";
         //$r->save();

         }*/
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
