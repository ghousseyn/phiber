<?php

class context extends Codup\main
{
    
   
    function run ($method)
    {
      
       // echo "<pre>";
      
         //$cosql = new cosql\basemodel();

         $test = models\departments::getInstance();

        
	  $res = $test->findAll()->fetch();

         
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
             var_dump($test->errors);
             return;
         }
         while ( $r = $res->iterate()){
         //$test2 = (array) $res; 
         echo $r->dept_no." ".$r->dept_name."<br/>";
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
