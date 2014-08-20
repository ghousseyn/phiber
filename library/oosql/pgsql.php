<?php
namespace Phiber\oosql;
require 'oogen.php';

class pgsql extends oogen
{

  protected $queries = array('tables' => "SELECT table_name FROM information_schema.tables where table_schema = 'public'",
                             'columns' => "SELECT column_name AS Field
FROM information_schema.columns where table_schema = 'public' and table_name='<table>'",
                             'schema' => "SELECT distinct table_schema FROM information_schema.tables where table_schema not in ('pg_catalog','information_schema')",
      'constraint'=> "SELECT
				pc.conname,
				pg_catalog.pg_get_constraintdef(pc.oid, true) AS consrc,
				pc.contype

			FROM
				pg_catalog.pg_constraint pc
			WHERE
				pc.conrelid = (SELECT oid FROM pg_catalog.pg_class WHERE relname='<table>'
					AND relnamespace = (SELECT oid FROM pg_catalog.pg_namespace
					WHERE nspname='public'))
			ORDER BY
				1");

  protected $seq = array();


  protected function createProps($fields,$tname,$cols)
  {
    $count = 0;
    foreach($cols as $col){

      if(isset($col['constraints'])){

        $cnt = count($fields[$tname]);
        for($i = 0; $i < $cnt; $i++){
          foreach($fields[$tname][$i] as $key => $val){

            if(! empty($val) && is_string($val) && isset($col['constraints'][$val])){
              $this->foreign[$val] = $col['constraints'][$val];
            }
          }
        }
      }


      if(! isset($col['Field']) || strlen($col['Field']) == 0){
        continue;
      }else{
        $seq = $tname.'_'.$col['Field'].'_seq';

        if(in_array($seq,$this->seq)){
          $this->ai = $col['Field'];
        }
        $this->text .= '  public $' . $col['Field'] . ';' . PHP_EOL;
      }
      $count++;
    }
  }

  protected function analyze($tbls)
  {
    foreach($tbls as &$tbl){

      $table = array_pop($tbl);

      $tables[] = array('schemaname'=>'public','tablename'=>$table);

      print "Analyzing $table physical columns ..." . PHP_EOL;

      $query = str_replace('<table>',$table,$this->queries['columns']);

      $collection = $this->getCollection($query);

      if(!$collection){

      foreach($this->errors as $error){
      print implode('|', $error) . PHP_EOL;
      }
      return false;
      }
      foreach($collection as $columns){
      $fields[$table][]['Field'] =  $columns['field'];
      }

        print "Analyzing $table DDL ..." . PHP_EOL;

        $constraints = $this->getCollection(str_replace('<table>',$table,$this->queries['constraint']));

//var_dump($constraints);

        $ks = array();
        if($constraints){
          foreach($constraints as $ex){

            $string = $ex['consrc'];
            $parts = explode('REFERENCES',$string);

            $local = explode(' ',$parts[0]);

            $fkey = str_replace(' ','_',trim($local[2],'()'));
            if($local[0] != 'FOREIGN'){

              if(trim($local[0]) == 'PRIMARY'){

                $this->primary[$table][] = $fkey;
              }
              continue;
            }
            $ref = explode('(',$parts[1]);
            $reftable = str_replace(' ','_',trim($ref[0]));
            $reffield = str_replace(' ','_',rtrim($ref[1],')'));
            print 'Found constraint '.$ex['conname'].' ...'.PHP_EOL;
            $fields[$table][]['constraints'][$fkey] = array($reffield, $reftable);
          }
        }



        }

        var_dump($this->getLinkingKeys($tables));
        return $fields;
  }
  public function getLinkingKeys($tables) {
    if (!is_array($tables)) return -1;
    $arr = array();
    $tables_list = "'{$tables[0]['tablename']}'";
    $schema_list = "'{$tables[0]['schemaname']}'";
    $schema_tables_list = "'{$tables[0]['schemaname']}.{$tables[0]['tablename']}'";

    for ($i = 1; $i < sizeof($tables); $i++) {

      $tables_list .= ", '{$tables[$i]['tablename']}'";
      $schema_list .= ", '{$tables[$i]['schemaname']}'";
      $schema_tables_list .= ", '{$tables[$i]['schemaname']}.{$tables[$i]['tablename']}'";
    }

    $maxDimension = 1;

    $sql = "
    SELECT DISTINCT
    array_dims(pc.conkey) AS arr_dim,
    pgc1.relname AS p_table
    FROM
    pg_catalog.pg_constraint AS pc,
    pg_catalog.pg_class AS pgc1
    WHERE
    pc.contype = 'f'
    AND (pc.conrelid = pgc1.relfilenode OR pc.confrelid = pgc1.relfilenode)
    AND pgc1.relname IN ($tables_list)
    ";

    //parse our output to find the highest dimension of foreign keys since pc.conkey is stored in an array
    $rs = $this->getCollection($sql);
    foreach ($rs as $line) {
      $arr[$line['p_table']]=$line['arr_dim'];
      $arrData = explode(':', $line['arr_dim']);
      $tmpDimension = intval(substr($arrData[1], 0, strlen($arrData[1] - 1)));
      $maxDimension = $tmpDimension > $maxDimension ? $tmpDimension : $maxDimension;
    }

    //we know the highest index for foreign keys that conkey goes up to, expand for us in an IN query
    $cons_str = '( (pfield.attnum = conkey[1] AND cfield.attnum = confkey[1]) ';
    for ($i = 2; $i <= $maxDimension; $i++) {
      $cons_str .= "OR (pfield.attnum = conkey[{$i}] AND cfield.attnum = confkey[{$i}]) ";
    }
    $cons_str .= ') ';

    $sql = "
    SELECT
    pgc1.relname AS p_table,
    pgc2.relname AS f_table,
    pfield.attname AS p_field,
    cfield.attname AS f_field,
    pgns1.nspname AS p_schema,
    pgns2.nspname AS f_schema
    FROM
    pg_catalog.pg_constraint AS pc,
    pg_catalog.pg_class AS pgc1,
    pg_catalog.pg_class AS pgc2,
    pg_catalog.pg_attribute AS pfield,
    pg_catalog.pg_attribute AS cfield,
    (SELECT oid AS ns_id, nspname FROM pg_catalog.pg_namespace WHERE nspname IN ($schema_list) ) AS pgns1,
    (SELECT oid AS ns_id, nspname FROM pg_catalog.pg_namespace WHERE nspname IN ($schema_list) ) AS pgns2
    WHERE
    pc.contype = 'f'
    AND pgc1.relnamespace = pgns1.ns_id
    AND pgc2.relnamespace = pgns2.ns_id
    AND pc.conrelid = pgc1.relfilenode
    AND pc.confrelid = pgc2.relfilenode
    AND pfield.attrelid = pc.conrelid
    AND cfield.attrelid = pc.confrelid
    AND $cons_str
    AND pgns1.nspname || '.' || pgc1.relname IN ($schema_tables_list)
    AND pgns2.nspname || '.' || pgc2.relname IN ($schema_tables_list)
    ";

    foreach($this->getCollection($sql) as $key => $res){
      $res['rel'] = $arr[$res['p_table']];
      $newArr[$res['p_table']] = $res;

    }
    return  $newArr;
  }
}
