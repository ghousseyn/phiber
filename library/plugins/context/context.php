<?php

class context extends Codup\main
{
    

    function run ()
    {
        echo "<pre>";
        
        $test = new models\translation;
        $res = $test->find(array('id'=> 16));
        echo $test->sql;
         var_dump($res->stmt->fetchAll());
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
         $query = 'SHOW KEYS FROM '.$table.'';
          
         $collection = $cosql->getCollection($query);
         while($example = $collection->iterate()) {
         	$fields[$table]['indexs'][]= (array) $example;
         }
          }
          //$test = array_pop($fields);
          foreach($fields as $tname => $cols){
              $text .= '<?php'.PHP_EOL.'namespace models;'.PHP_EOL.'use Codup;';
              $text .= PHP_EOL."class $tname extends model  ".PHP_EOL."{".PHP_EOL;
              foreach($cols as $col){
                  if($col['Key'] == 'PRI'){
                      $primary = $col['Field'];
                  }
                  if($col['Field'] == ''){
                      continue;
                  }
                  $text .= '    public $'.$col['Field'].';'.PHP_EOL;
              }
              $text .= '    public function getPrimary() '.PHP_EOL.'    {'.PHP_EOL.'        return $this->'.$primary.';'.PHP_EOL.'    }'.PHP_EOL;
              unset($primary);
              $text .= '}'.PHP_EOL.'?>';
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