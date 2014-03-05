<?php

require_once 'Tests/CodupTests.php';
require_once 'library/oosql/oosql.php';
require_once 'library/config.php';

class oosqlTest extends CodupTests
{

  private $oosql = null;

  public function setUp()
  {
    $config = config::getInstance();
    $config->_dsn = 'sqlite:./test.db';
    $this->oosql = oosql\oosql::getInstance('table','class',$config);
  }
  public function testSelect()
  {
    $return = $this->invokeMethod($this->oosql, 'select');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $select = $this->getProperty($this->oosql, 'oosql_select');
    $this->assertEquals('SELECT table.* ', $sql);
    $this->assertTrue($flag);
    $this->assertInstanceOf('oosql\\oosql', $select);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }

  public function testSelectColumnsSpecified()
  {
    $return = $this->invokeMethod($this->oosql, 'select',array('column1','column2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $select = $this->getProperty($this->oosql, 'oosql_select');
    $this->assertEquals('SELECT table.column1, table.column2', $sql);
    $this->assertTrue($flag);
    $this->assertInstanceOf('oosql\\oosql', $select);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testInsert()
  {
    $return = $this->invokeMethod($this->oosql, 'insert');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $this->assertEquals('INSERT INTO table', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testInsertColumnsSpecified()
  {
    $return = $this->invokeMethod($this->oosql, 'insert',array('column1','column2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $numargs = $this->getProperty($this->oosql, 'oosql_numargs');
    $this->assertEquals(2, $numargs);
    $this->assertEquals('INSERT INTO table (column1,column2)', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testUpdate()
  {
    $return = $this->invokeMethod($this->oosql, 'update');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_multiFlag');
    $this->assertFalse($flag);
    $this->assertEquals('UPDATE table SET ', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testUpdateMultiple()
  {
    $return = $this->invokeMethod($this->oosql, 'update',array('table1', 'table2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_multiFlag');
    $this->assertTrue($flag);
    $this->assertEquals('UPDATE table1, table2 SET ', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testDelete()
  {
    $return = $this->invokeMethod($this->oosql, 'delete');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $this->assertTrue($flag);
    $this->assertEquals('DELETE', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testDeleteMulti()
  {
    $return = $this->invokeMethod($this->oosql, 'delete',array('table1','table2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flagFrom = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $flagMulti = $this->getProperty($this->oosql, 'oosql_del_multiFlag');
    $flagArgs = $this->getProperty($this->oosql, 'oosql_del_numargs');
    $this->assertFalse($flagFrom);
    $this->assertTrue($flagMulti);
    $this->assertEquals(2,$flagArgs);
    $this->assertEquals('DELETE FROM table1, table2', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testDeleteShortCut()
  {
    $return = $this->invokeMethod($this->oosql, 'delete',array(array('id',1)));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $value = $this->getProperty($this->oosql, 'oosql_conValues');
    $this->assertFalse($flag);
    $this->assertContains(1, $value);
    $this->assertEquals('DELETE FROM table', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testSet()
  {
    $data = array('field1'=>'value','field2'=>12);
    $this->invokeMethod($this->oosql, 'update');
    $return = $this->invokeMethod($this->oosql, 'set', array($data));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals('UPDATE table SET field1 = ?,field2 = ?', $sql);
    $this->assertContains('value',$values);
    $this->assertContains(12,$values);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  /**
   * @expectedException PDOException
   * @expectedExceptionCode 9902
   */
  public function testSave()
  {
    $this->invokeMethod($this->oosql, 'save');
  }
  public function testCreatWhere()
  {
    $array = array('field1' => 11);
    $return = $this->invokeMethod($this->oosql, 'createWhere',array($array));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' WHERE field1 =?', $sql);
    $this->assertContains(11, $values);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testCreatAndWhere()
  {
    $array = array('field1' => 11, 'field2' => 'value', 'field3' => 1.54);
    $return = $this->invokeMethod($this->oosql, 'createWhere',array($array));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' WHERE field1 =? AND field2 =? AND field3 =?', $sql);
    $this->assertContains(11, $values);
    $this->assertContains(1.54, $values);
    $this->assertContains('value', $values);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  /**
   * @expectedException PDOException
   * @expectedExceptionCode 9905
   */
  public function testValuesNoArgs()
  {
    $return = $this->invokeMethod($this->oosql, 'values');
  }

  /**
   * @expectedException PDOException
   * @expectedExceptionCode 9905
   */
  public function testValuesArgsNotMatching()
  {
    $this->invokeMethod($this->oosql,'insert', array('field1','field2'));
    $return = $this->invokeMethod($this->oosql, 'values',array('value1'));
  }
  public function testValues()
  {
    $this->invokeMethod($this->oosql,'insert', array('field1','field2'));
    $return = $this->invokeMethod($this->oosql, 'values',array('value1',3));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');

    $this->assertEquals('INSERT INTO table (field1,field2) VALUES ( ?, ?)', $sql);
    $this->assertFalse($flag);
    $this->assertcontains('value1',$values);
    $this->assertcontains(3,$values);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  /**
   * @expectedException PDOException
   * @expectedExceptionCode 9906
   */
  public function testFromArgsNotMatching()
  {
    $this->invokeMethod($this->oosql,'delete', array('field1','field2'));
    $return = $this->invokeMethod($this->oosql, 'from',array('table1'));
  }
  public function testFromWithArgs()
  {
    $return = $this->invokeMethod($this->oosql, 'from',array('table1','table2'));

    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $this->assertEquals(' FROM table1, table2', $sql);
    $this->assertFalse($flag);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testFromNoArgs()
  {
    $return = $this->invokeMethod($this->oosql, 'from');

    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $this->assertEquals(' FROM table', $sql);
    $this->assertFalse($flag);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
  public function testJoinDefault()
  {
    $params = array('table1','table.fk_id = table1.id');
    $return = $this->invokeMethod($this->oosql, 'join',$params);

    $sql = $this->getProperty($this->oosql, 'oosql_join');
    $this->assertEquals(' JOIN table1 ON table.fk_id = table1.id', $sql);
    $this->assertInstanceOf('oosql\\oosql', $return);
  }
}

?>