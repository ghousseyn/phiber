<?php

require_once 'Tests/CodupTests.php';
require_once 'library/oosql/oosql.php';

class oosqlTest extends CodupTests
{

  private $oosql = null;

  public function setUp()
  {
    $this->oosql = oosql\oosql::getInstance('table','class');
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

}

?>