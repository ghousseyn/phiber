<?php

class context extends Codup\main
{
    

    function run ()
    {
        echo "<pre>";
        
         //$cosql = new cosql\basemodel();
         $test = models\cms_component::getInstance();
         $res = $test->find(array('id'=>12))->fetch();
         $res->name = "hussein";
         //$res->cms_componenttype_id = 6;
         //$res->is_enabled = 'N';        
         $res->save();
         
         //var_dump($res);
        /*
         $q = "SHOW TABLES";
         $tables = $cosql->getCollection($q);
         while($table = $tables->iterate()){
             $tbls[] = (array) $table;
         }
         foreach($tbls as $table){
             
      
         $table = array_pop($table);
         $query = 'SHOW COLUMNS FROM '.$table.'';
         
         $collection = $cosql->getCollection($query);
          while($example = $collection->iterate()) {
             $fields[$table][]= (array) $example;
         }
      
         $col = $cosql->getCollection('show create table `'.$table.'`');
         $ks = array();
         while($ex = $col->iterate()) {
         	$ks[]= (array) $ex;
         }
         
         $create = $ks[0]['Create Table'];
        
         $keys = explode(',',$create);
      
         //echo trim($keys[count($keys)-1])." $table<br>";
         while(substr(trim($keys[count($keys)-1]),0,10) == 'CONSTRAINT' ){
         	 
         	 
         	$key = array_pop($keys);
         	$parts = explode(' ',trim($key));
         	$constraint = trim($parts[1],'`');
         	$fkey = trim($parts[4],'(,),`');
         	$reftable = trim($parts[6],'`');
         	$reffield = trim($parts[7],'(,),`');
         	$fields[$table][]['constraints'][$fkey] = array($reffield,$reftable); 
         	
         }
          }
        
          //$test = array_pop($fields);
          foreach($fields as $tname => $cols){
              $text .= '<?php'.PHP_EOL.'namespace models;'.PHP_EOL.'use Codup;';
              $text .= PHP_EOL."class $tname extends model  ".PHP_EOL."{".PHP_EOL;
              $count = 0;
              $foreign = array();
              foreach($cols as $col){
               // var_dump($fields);
                  
                  if(isset($col['constraints'])){
                      //print_r($fields[$tname]);
                 for($i =0;$i < count($fields[$tname]);$i++){
                     foreach($fields[$tname][$i] as $key => $val){
                         //print_r($col['constraints'][$val]);
                         if(isset($col['constraints'][$val])){   
                             $foreign[$val] = $col['constraints'][$val];
                         }
                     }
                 }
                  }
                
                  if($col['Key'] == 'PRI'){
                      $primary = $col['Field'];
                  }
                  if($col['Field'] == ''){
                      continue;
                  }
                  $text .= '    public $'.$col['Field'].';'.PHP_EOL;
                  $count++;
              }
              if($primary != ""){
                  $text .= '    public function getPrimary() '.PHP_EOL.'    {'.PHP_EOL.'        return "'.$primary.'";'.PHP_EOL.'    }'.PHP_EOL;
                  $text .= '    public function getPrimaryValue() '.PHP_EOL.'    {'.PHP_EOL.'        return $this->'.$primary.';'.PHP_EOL.'    }'.PHP_EOL;
                  unset($primary);
              }else{
                  $text .= '    public function getPrimary() '.PHP_EOL.'    {'.PHP_EOL.'        return false;'.PHP_EOL.'    }'.PHP_EOL;
                  $text .= '    public function getPrimaryValue() '.PHP_EOL.'    {'.PHP_EOL.'        return false;'.PHP_EOL.'    }'.PHP_EOL;                  
              }
              
              if(count($foreign)){
                  $text .= '    public function getRelations() '
                  .PHP_EOL.
                  '    {'
                  .PHP_EOL.
                  '        return array(';
                                   
                  foreach ($foreign as $member => $content){
                      $text .= "'".$member."'=>'".$content[1].".".str_replace('`)','',$content[0])."',";
                  }
                  $text .= ');'
                  .PHP_EOL.
                  '    }'
                  .PHP_EOL;
                  
                 
              }
              $text .= '}'.PHP_EOL;
              $filename = "G:\\www\\codup\\codup\\library\\models/".$tname.".php";
            
              $f = fopen($filename, "w+");
              $r = fwrite($f, $text);
              fclose($f);
              unset($text);
          }
 
           */
        if ($this->isAjax()) {
            $this->register('context', 'html');
            echo "AJax";
        }
    }
}