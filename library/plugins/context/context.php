<?php

class context extends Codup\main
{


    function run ()
    {

    	$managers = models\dept_emp::getInstance();
    	$results = $managers->select()->fetch(200);
    	/*foreach($results as $result){
    		$result->from_date = "1983-03-26";
    		$result->save();
    	}
    	*/
    	echo $results->count();
	$col = $results->findAll('dept_no','d009');
	$col->sortbyproperty('from_date');
    	$this->tools->wtf($col);
	//$this->tools->wtf($results->findAll('dept_no','d007'));
    	/*$depts = models\titles::getInstance();;

    	try {

    		$titles = $depts->select()->fetch(2);
    		$title = $titles(0);
    		$title->to_date = "1986-06-28";
    		$title->save();

    	}catch (Exception $e){

    		$this->tools->wtf($e);

    	}
    	$this->tools->wtf($titles);
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
    	/*
		$deps = models\dept_emp::getInstance();

         //$deps->delete(array("dept_no = ?", "d012"));

		  $res = $dept_manager->findAll()//or $dept_manager->findAll()
							  ->with(array('departments'=>array("dept_name"),'employees'))
							  ->fetch(20);

		  $this->tools->wtf($res);
		  $res2 = $deps->findAll()->fetch(30);

		  $res2->removeWhere('dept_no','d004');
		  $res2->removeWhere('dept_no','d005');
		  $res2->restoreWhere('dept_no','d005');
		  $res2->sortByProperty('from_date');

		  $keys = $res2->objectWhere('dept_no','d005');
		  $this->tools->wtf($res2);
		  $dept2 = $res2(0)->departments()->load();
		  //$this->tools->wtf($dept2);
		  $dept2->dept_name = "Quality Management testing";
		 $dept2->save();
		  $res2(10)->departments()->load();
		  $this->tools->wtf($dept2);
		  echo $res;
		  echo $res2;

         //($deps);
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

        $dept = $res(1)->employees();

        $this->tools->wtf($dept);
        $dept->first_name = "hocine";
        $dept->last_name = "guettaf";
        $dept->gender = 'M';
        $res(1)->dept_no = 'd004';

        $dept->save();
        $res(1)->save();
        //$this->tools->wtf($dept);
        //$dept->dept_no = "d018";
         //won't work (trying to change a primary key here) try using update() instead BAD idea though!
         //$dept->dept_no = "d010";

		  //$this->tools->wtf($res2);
     /*

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

        }
    }
}
