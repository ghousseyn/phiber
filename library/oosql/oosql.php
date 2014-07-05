<?php
namespace Phiber\oosql;

class oosql extends \PDO
{
/**
 * $oosql_result After a select this field will hold a copy of the result
 * @var collection
 * @access protected
 * @static
 */
  protected static $oosql_result = null;
  /**
   * $oosql_class The class name of the entity
   * @var string
   * @access protected
   */
  protected $oosql_class;
  /**
   * $oosql_table The table we are querying
   * @var string
   * @access protected
   */
  protected $oosql_table;

  /**
   * $oosql_model_obj An instance of the entity class
   * @var mixed
   * @access private
   */
  private $oosql_model_obj = null;
  /**
   * $oosql_limit
   * @var string Limit clause
   * @access private
   */
  private $oosql_limit = null;

  private $oosql_order = null;

  private $oosql_where = null;

  private $oosql_join = null;

  private $oosql_stmt;

  private $oosql_conValues = array();

  private $oosql_numargs;

  private $oosql_fromFlag = false;

  private $oosql_multiFlag = false;

  private $oosql_del_multiFlag = false;

  private $oosql_multi = array();

  private $oosql_del_numargs;

  private $oosql_sql;

  private $oosql_select;

  private $oosql_distinct = false;

  private $oosql_insert = false;

  /**
   * __construct()
   * @param string $oosql_table The table we are querying
   * @param string $oosql_class The class name (type of the object holding the results)
   * @throws \Exception
   */
  function __construct($oosql_table = null, $oosql_class = null,$config = null)
  {
    if($oosql_class === null || $oosql_table === null){
      throw new \Exception('Class or Table name not provided!',9801,null);
    }
    if(null === $config){
      $config = \config::getInstance();
    }
    $this->oosql_class = $oosql_class;
    $this->oosql_table = $oosql_table;

    parent::__construct($config->PHIBER_DB_DSN, $config->PHIBER_DB_USER, $config->PHIBER_DB_PASS);
    $this->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    $this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);

  }

  /**
   * Get a copy of the results of the previous select (if any) or null if not
   * @return collection or null
   */
  public static function getPrevious()
  {
    return self::$oosql_result;
  }

  /**
   * Get an instance of this class
   * @return oosql An oosql\oosql object
   * @static
   */
  public static function getInstance($oosql_table = null, $oosql_class = null, $config = null)
  {
    return new self($oosql_table, $oosql_class,$config);
  }

  /**
   * Returns an instance of the entity class used
   */
  public function getModelObject()
  {
    if(null != $this->oosql_model_obj){
      return $this->oosql_model_obj;
    }else{
      return new $this->oosql_class();

    }
  }

  public function select()
  {
    $this->oosql_sql = 'SELECT ';
    if($this->oosql_distinct ){
      $this->oosql_sql .= 'DISTINCT ';
    }
    $numargs = func_num_args();

    if($numargs > 0){
      $arg_list = func_get_args();
      for($i = 0; $i < $numargs; $i++){
        if($i != 0 && $numargs > 1){
          $this->oosql_sql .= ',';
        }
        $this->oosql_sql .= $arg_list[$i];
      }
    }else{
      $this->oosql_sql .= $this->oosql_table.'.* ';
    }

    $this->oosql_fromFlag = true;
    $this->oosql_select = $this;
    $this->oosql_where = null;
    return $this;
  }

  public function insert()
  {
    $this->oosql_sql = 'INSERT INTO '.$this->oosql_table;

    $arg_list = func_get_args();
    $numargs = func_num_args();

    if($numargs > 0){
      $this->oosql_numargs = $numargs;
      $this->oosql_sql .= ' (';

      $this->oosql_sql .= implode(',', $arg_list);

      $this->oosql_sql .= ')';
    }
    $this->oosql_insert = true;
    return $this;
  }

  public function update()
  {

    $this->oosql_sql = 'UPDATE';

    $numargs = func_num_args();

    if($numargs > 0){
      $arg_list = func_get_args();

      $this->oosql_multiFlag = true;

      $this->oosql_multi = $arg_list;

      for($i = 0; $i < $numargs; $i++){
        if($i != 0 && $numargs > $i){
          $this->oosql_sql .= ',';
        }
        $this->oosql_sql .= ' ' . $arg_list[$i];
      }
    }else{
      $this->oosql_sql .= " $this->oosql_table";
    }

    $this->oosql_sql .= ' SET ';
    $this->oosql_where = null;
    return $this;
  }

  public function delete()
  {

    $this->oosql_sql = 'DELETE';
    $this->oosql_where = null;
    $numargs = func_num_args();

    if($numargs > 0){
      if($numargs > 1){
        $this->oosql_del_multiFlag = true;
        $this->oosql_del_numargs = $numargs;
      }
      $arg_list = func_get_args();
      if(is_array($arg_list[0])){
        $this->oosql_sql .= ' FROM '.$this->oosql_table;
        $this->where($arg_list[0][0].' = ?', $arg_list[0][1]);
        return $this;
      }
      $this->oosql_sql .= ' FROM';
      for($i = 0; $i < $numargs; $i++){
        if($i != 0 && $numargs > 1){
          $this->oosql_sql .= ',';
        }
        $this->oosql_sql .= ' ' . $arg_list[$i];
      }

    }else{
      $this->oosql_fromFlag = true;
    }


    return $this;
  }

  /**
   * Sets the column, value pairs in update queries
   * @param array $data An array of the fields with their corresponding values in a key => value format
   */
  public function set(array $data)
  {
    foreach($data as $field => $value){

        $this->oosql_sql .= $field.' = ?,';
        $this->oosql_conValues[] = $value;


    }
    $this->oosql_sql = rtrim($this->oosql_sql, ',');
    return $this;
  }

  /**
   * Decides if this is an insert or an update and what fields have changed if appropriate
   * @param mixed $object If null this is an insert if not than it's an update
   * @throws \Exception
   */
  public function save($object = null)
  {
    $data = null;
    if(null === $object){
      if(null === $this->oosql_model_obj){
        $msg = 'Nothing to save! ' . $this->oosql_sql;
        throw new \Exception($msg,9806,null);
      }
      // This is a brand new record let's insert;
      $this->insert(implode(',', array_keys((array) $this->oosql_model_obj)))->values(implode(',', array_values((array) $this->oosql_model_obj)))->exe();
      return true;
    }
    if(isset(self::$oosql_result[get_class($object)])){
      // Updating after a select
      $primary = $object->getPrimaryValue();

      foreach(self::$oosql_result[get_class($object)] as $result_object){

        if($result_object->getPrimaryValue() === $primary){

          $old = $result_object;
        }
      }
      foreach(array_diff((array) $object, (array) $old) as $key => $value){

        $data[$key] = $value;
      }
      if(null === $data){
        $msg = 'Nothing to save! ' . $this->oosql_sql;
        throw new \Exception($msg,9806,null);
      }
      $this->update()->set($data)->createWhere($object->getPrimaryValue())->exe();
      return true;

    }
      // update a related table (no select on it)
      $primary = $object->getPrimary();

      foreach((array)$object as $k => $v){
        if($v === null || in_array($k, $primary)){
          continue;
        }
        $data[$k] = $v;
      }
      if(count($data) !== 0){
        $this->update()->set($data)->createWhere($object->getPrimaryValue())->exe();
        return true;
      }
      $msg = 'Nothing to save! ' . $this->oosql_sql;
      throw new \Exception($msg,9806,null);
  }

  /**
   * Creates where clause(s) from an array of conditions
   * @param array $conditions An array of conditions in the format:
   *              <code>array("column = ?", $value)</code>
   */
  public function createWhere(array $conditions)
  {
    $num = 0;

    foreach($conditions as $col => $value){
      if(empty($value)){
        continue;
      }
      if($num === 0){
        $this->where($col . ' =?', $value);
        $num++;
        continue;
      }
      $this->andWhere($col . ' =?', $value);

    }

    return $this;
  }

  /**
   * Assembles values part of an insert
   * @throws \Exception
   */
  public function values()
  {

    $arg_list = func_get_args();

    $numargs = func_num_args();

    if(($this->oosql_numargs !== 0 && $numargs !== $this->oosql_numargs) || $numargs === 0){
      $msg = 'Insert numargs: '.$this->oosql_numargs.' | values numargs = '.$numargs.', Columns and passed data do not match! ' . $this->oosql_sql;
      throw new \Exception($msg,9807,null);
    }

    $this->oosql_sql .= ' VALUES (';

    for($i = 0; $i < $numargs; $i++){
      if($i != 0 && $numargs > 1){
        $this->oosql_sql .= ',';
      }
      $this->oosql_sql .= ' ?';

    }
    $this->oosql_conValues = $arg_list;
    $this->oosql_sql .= ')';

    $this->oosql_fromFlag = false;
    return $this;
  }

  /**
   * Assembles the FROM part of the query
   * @throws \Exception
   */
  public function from()
  {

    $numargs = func_num_args();

    if($this->oosql_del_multiFlag){


      if($numargs < $this->oosql_del_numargs){
        $msg = 'Columns and passed data do not match! ' . $this->oosql_sql;
        throw new \PDOException($msg,9807,null);
      }


    }

    $this->oosql_sql .= ' FROM ';

    if($numargs > 0){
      $arg_list = func_get_args();
      for($i = 0; $i < $numargs; $i++){
        if($i !== 0 && $numargs > $i){
          $this->oosql_sql .= ', ';
        }
        $this->oosql_sql .= $arg_list[$i];
      }
    }else{
      $this->oosql_sql .= $this->oosql_table;
    }
    $this->oosql_fromFlag = false;
    return $this;
  }

  public function join($table, $criteria, $type = '')
  {

    $this->oosql_join .= " $type JOIN $table ON $criteria";
    return $this;
  }

  public function joinLeft($table, $criteria)
  {
    return $this->join($table, $criteria, $type = 'LEFT');
  }

  public function joinRight($table, $criteria)
  {
    return $this->join($table, $criteria, $type = 'RIGHT');
  }

  public function joinFull($table, $criteria)
  {
    return $this->join($table, $criteria, $type = 'FULL OUTER');
  }

  public function where($condition, $value=null, $type = null)
  {

    switch($type){
      case null:
        $clause = 'WHERE';
        break;
      case 'or':
        $clause = 'OR';
        break;
      case 'and':
        $clause = 'AND';
        break;
      default:
        $clause = 'WHERE';
    }

    $this->oosql_where .= " $clause $condition";
    $this->oosql_conValues[] = $value;

    return $this;
  }

  public function andWhere($condition, $value=null)
  {
    $this->where($condition, $value, 'and');
    return $this;
  }

  public function orWhere($condition, $value)
  {
    $this->where($condition, $value, 'or');
    return $this;
  }

  protected function valid_int($val)
  {
    return ctype_digit(strval($val));
  }

  public function exe($lastID = 'id')
  {
    if($this->oosql_fromFlag){
      $this->from();
    }
    if(null != $this->oosql_join){
      $this->oosql_sql .= $this->oosql_join;
    }
    if(null != $this->oosql_where){
      $this->oosql_sql .= $this->oosql_where;
    }
    if(null != $this->oosql_limit){
      $this->oosql_sql .= ' ' . $this->oosql_limit;
    }
    if(null != $this->oosql_order){
      $this->oosql_sql .= ' ' . $this->oosql_order;
    }

    if(count($this->oosql_conValues) !== 0){
      $this->oosql_stmt = $this->prepare(trim($this->oosql_sql));
      $ord = 1;
      foreach($this->oosql_conValues as $val){

        if($this->valid_int($val)){

          $this->oosql_stmt->bindValue($ord, $val, \PDO::PARAM_INT);

        }else{

          $this->oosql_stmt->bindValue($ord, $val, \PDO::PARAM_STR);
        }
        $ord++;
      }
      $this->oosql_conValues = array();

      $return = $this->oosql_stmt->execute();
      if($return === false){
        $msg = 'Execution failed! ' . $this->oosql_sql;
        throw new \Exception($msg,9812,null);
      }

    }else{

      $this->oosql_stmt = $this->query($this->oosql_sql);

    }
    if($this->oosql_insert){
      return $this->lastInsertId($lastID);
    }
    /*
     * $str = $this->oosql_sql." | "; $str .= implode(',
     * ',$this->oosql_conValues)."\r\n"; $f = fopen("g:\log.txt","a+");
     * fwrite($f, $str); fclose($f);
     */
    // echo $this->oosql_sql."</br></br>";
    $this->oosql_stmt->setFetchMode(\PDO::FETCH_CLASS | \PDO::FETCH_PROPS_LATE, $this->oosql_class);

    return $this->oosql_stmt;
  }


  /**
   *
   * @throws \InvalidArgumentException
   * @throws \Exception
   */
  public function fetch()
  {
    $numargs = func_num_args();
    if($numargs !== 0){
      $argumants = func_get_args();
      switch($numargs){
        case 1:
          $this->limit(0, $argumants[0]);
          break;
        case 2:
          $this->limit($argumants[0], $argumants[1]);
          break;
        default:
          throw new \InvalidArgumentException('Fetch expects zero, one or two arguments as a query result limit',9808,null);
      }
    }

    if($this->oosql_select instanceof oosql){
      $this->oosql_select->exe();
    }else{
      $msg = 'Query returned no results! You need to select first! ' . $this->oosql_sql;
      throw new \Exception($msg,9809,null);
    }


    if(! $this->oosql_stmt){

      $msg = 'Query returned no results! ' . $this->oosql_sql;
      throw new \Exception($msg,9810,null);
    }

    $result = $this->oosql_stmt->fetchAll();

    $collection = new collection();

    foreach($result as $res){
      $collection->add($res);

    }
    $collection->obj_name = $this->oosql_class;
    self::$oosql_result[$this->oosql_class] = clone $collection;
    return $collection;
  }

  public function with(array $related)
  {

    $relations = $this->getModelObject()->getRelations();
    foreach($relations as $fk => $target){
      $table = substr($target, 0, strpos($target, '.'));
      if(in_array($table, $related)){
        $this->oosql_sql .= " ,$table.*";
        $this->join($table, "$this->oosql_table.$fk = $target");
      }elseif(in_array($table, array_keys($related))){
        foreach($related[$table] as $field){
          $this->oosql_sql .= " ,$table.$field";
        }
        $this->join($table, "$this->oosql_table.$fk = $target");
      }
    }
    return $this;
  }

  public function limit($from, $to)
  {
    if(!$this->oosql_multiFlag){
      $this->oosql_limit = ' LIMIT ' . $from . ', ' . $to;
    }
    return $this;
  }
  public function orderBy($field){
    if(!$this->oosql_multiFlag){
      $this->oosql_order = ' ORDER BY ' . $field;
    }
    return $this;
  }

  public function findOne($arg, $operator = null, $fields = array('*'))
  {
    return $this->find($arg, $operator, $fields)->limit(0, 1);
  }

  public function findLimited($arg, $from, $to, $operator = null, $fields = array('*'))
  {
    return $this->find($arg, $operator, $fields)->limit($from, $to);
  }

  public function findAll()
  {
    $this->oosql_select = $this->select('*');
    return $this;
  }

  public function find($arg, $operator = '=', $fields = array('*'))
  {
    if($fields[0] == '*'){
      $this->oosql_select = $this->select("*");
    }else{
    $select_args = '';
    foreach ($fields as $key => $field){
      if(is_array($field) && is_string($key)){
         foreach ($field as $part){
           $select_args .= $key.'.'.$part.', ';
         }
      }else{
        $select_args .= $this->oosql_table.'.'.$field.', ';
      }
    }

    $this->oosql_select = $this->select(rtrim($select_args,','));
    }
    if(! is_array($arg)){
      $obj = new $this->oosql_class();
      $pri = $obj->getPrimary();
      $arg = array($pri[0] => $arg);
    }
    $i = 0;
    $flag = '';
    foreach($arg as $col => $val){
      if($i > 0){
        $flag = 'and';
      }
      $this->oosql_select->where("$this->oosql_table.$col $operator ?", $val, $flag);
      $i++;
    }

    return $this;
  }

  /**
   * @todo define these
   */

  public function groupBy(){
  }
  public function having(){
  }
  public function in(){
  }
  public function between(){
  }
  public function union(){
  }
  public function distinct(){
    $this->oosql_distinct = true;
    return $this;
  }
  public function __set($var, $val)
  {

    if(null != $this->oosql_model_obj){
      $this->oosql_model_obj->{$var} = $val;
    }else{
      $this->oosql_model_obj = new $this->oosql_class();
      $this->oosql_model_obj->{$var} = $val;
    }
    return $this->oosql_model_obj;
  }

}
?>
