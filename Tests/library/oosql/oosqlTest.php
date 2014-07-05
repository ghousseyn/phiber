<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/oosql/oosql.php';
require_once 'library/config.php';

class oosqlTest extends PhiberTests
{

  private $oosql = null;

  public function setUp()
  {
    $config = \config::getInstance();
    $config->PHIBER_DB_DSN = 'sqlite:./test.db';
    $this->oosql = \Phiber\oosql\oosql::getInstance('table','class',$config);
  }
  public function testSelect()
  {
    $return = $this->invokeMethod($this->oosql, 'select');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $select = $this->getProperty($this->oosql, 'oosql_select');
    $this->assertEquals('SELECT table.* ', $sql);
    $this->assertTrue($flag);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $select);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }

  public function testSelectColumnsSpecified()
  {
    $return = $this->invokeMethod($this->oosql, 'select',array('column1','column2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $select = $this->getProperty($this->oosql, 'oosql_select');
    $this->assertEquals('SELECT column1,column2', $sql);
    $this->assertTrue($flag);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $select);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testInsert()
  {
    $return = $this->invokeMethod($this->oosql, 'insert');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $this->assertEquals('INSERT INTO table', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testInsertColumnsSpecified()
  {
    $return = $this->invokeMethod($this->oosql, 'insert',array('column1','column2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $numargs = $this->getProperty($this->oosql, 'oosql_numargs');
    $this->assertEquals(2, $numargs);
    $this->assertEquals('INSERT INTO table (column1,column2)', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testUpdate()
  {
    $return = $this->invokeMethod($this->oosql, 'update');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_multiFlag');
    $this->assertFalse($flag);
    $this->assertEquals('UPDATE table SET ', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testUpdateMultiple()
  {
    $return = $this->invokeMethod($this->oosql, 'update',array('table1', 'table2'));
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_multiFlag');
    $this->assertTrue($flag);
    $this->assertEquals('UPDATE table1, table2 SET ', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testDelete()
  {
    $return = $this->invokeMethod($this->oosql, 'delete');
    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $this->assertTrue($flag);
    $this->assertEquals('DELETE', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
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
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
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
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
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
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  /**
   * @expectedException Exception
   * @expectedExceptionCode 9806
   */
  public function testSave()
  {
    $this->invokeMethod($this->oosql, 'save');
  }
  public function testCreateWhere()
  {
    $array = array('field1' => 11);
    $return = $this->invokeMethod($this->oosql, 'createWhere',array($array));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' WHERE field1 =?', $sql);
    $this->assertContains(11, $values);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testCreateAndWhere()
  {
    $array = array('field1' => 11, 'field2' => 'value', 'field3' => 1.54);
    $return = $this->invokeMethod($this->oosql, 'createWhere',array($array));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' WHERE field1 =? AND field2 =? AND field3 =?', $sql);
    $this->assertContains(11, $values);
    $this->assertContains(1.54, $values);
    $this->assertContains('value', $values);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  /**
   * @expectedException Exception
   * @expectedExceptionCode 9807
   */
  public function testValuesNoArgs()
  {
    $this->invokeMethod($this->oosql, 'values');
  }

  /**
   * @expectedException Exception
   * @expectedExceptionCode 9807
   */
  public function testValuesArgsNotMatching()
  {
    $this->invokeMethod($this->oosql,'insert', array('field1','field2'));
    $this->invokeMethod($this->oosql, 'values',array('value1'));
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
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  /**
   * @expectedException Exception
   * @expectedExceptionCode 9807
   */
  public function testFromArgsNotMatching()
  {
    $this->invokeMethod($this->oosql,'delete', array('field1','field2'));
    $this->invokeMethod($this->oosql, 'from',array('table1'));
  }
  public function testFromWithArgs()
  {
    $return = $this->invokeMethod($this->oosql, 'from',array('table1','table2'));

    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $this->assertEquals(' FROM table1, table2', $sql);
    $this->assertFalse($flag);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testFromNoArgs()
  {
    $return = $this->invokeMethod($this->oosql, 'from');

    $sql = $this->getProperty($this->oosql, 'oosql_sql');
    $flag = $this->getProperty($this->oosql, 'oosql_fromFlag');
    $this->assertEquals(' FROM table', $sql);
    $this->assertFalse($flag);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testJoinDefault()
  {
    $params = array('table1','table.fk_id = table1.id');
    $return = $this->invokeMethod($this->oosql, 'join',$params);

    $sql = $this->getProperty($this->oosql, 'oosql_join');
    $this->assertEquals('  JOIN table1 ON table.fk_id = table1.id', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testJoinLeft()
  {
    $params = array('table1','table.fk_id = table1.id');
    $return = $this->invokeMethod($this->oosql, 'joinLeft',$params);

    $sql = $this->getProperty($this->oosql, 'oosql_join');
    $this->assertEquals(' LEFT JOIN table1 ON table.fk_id = table1.id', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testJoinRight()
  {
    $params = array('table1','table.fk_id = table1.id');
    $return = $this->invokeMethod($this->oosql, 'joinRight',$params);

    $sql = $this->getProperty($this->oosql, 'oosql_join');
    $this->assertEquals(' RIGHT JOIN table1 ON table.fk_id = table1.id', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testJoinFull()
  {
    $params = array('table1','table.fk_id = table1.id');
    $return = $this->invokeMethod($this->oosql, 'joinFull',$params);

    $sql = $this->getProperty($this->oosql, 'oosql_join');
    $this->assertEquals(' FULL OUTER JOIN table1 ON table.fk_id = table1.id', $sql);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testWhere()
  {
    $return = $this->invokeMethod($this->oosql, 'where',array('field1 = ?', 'value'));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' WHERE field1 = ?', $sql);
    $this->assertContains('value', $values);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testAndWhere()
  {
    $return = $this->invokeMethod($this->oosql, 'andWhere',array('field1 = ?', 'value'));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' AND field1 = ?', $sql);
    $this->assertContains('value', $values);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testOrWhere()
  {
    $return = $this->invokeMethod($this->oosql, 'orWhere',array('field1 = ?', 'value'));
    $sql = $this->getProperty($this->oosql, 'oosql_where');
    $values = $this->getProperty($this->oosql, 'oosql_conValues');

    $this->assertEquals(' OR field1 = ?', $sql);
    $this->assertContains('value', $values);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testValidInteger()
  {
    $return1 = $this->invokeMethod($this->oosql, 'valid_int',array(1.2304e6));
    $return2 = $this->invokeMethod($this->oosql, 'valid_int',array(16));
    $return3 = $this->invokeMethod($this->oosql, 'valid_int',array('16'));
    $return4 = $this->invokeMethod($this->oosql, 'valid_int',array('1.2304e6'));
    $return5 = $this->invokeMethod($this->oosql, 'valid_int',array(16.2));

    $this->assertTrue($return1);
    $this->assertTrue($return2);
    $this->assertTrue($return3);
    $this->assertFalse($return4);
    $this->assertFalse($return5);
  }
  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionCode 9808
   */
  public function testFetchWrongArgs()
  {
    $this->invokeMethod($this->oosql, 'fetch',array(0,1,2));
  }
  /**
   * @expectedException Exception
   * @expectedExceptionCode 9809
   */
  public function testFetchNoResults()
  {
    $this->invokeMethod($this->oosql, 'fetch');
  }
  public function testLimit()
  {
    $return = $this->invokeMethod($this->oosql, 'limit',array(0,10));
    $limit = $this->getProperty($this->oosql, 'oosql_limit');

    $this->assertEquals(' LIMIT 0, 10', $limit);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testLimitMulti()
  {
    $this->setProperty($this->oosql, 'oosql_multiFlag',true);
    $return = $this->invokeMethod($this->oosql, 'limit',array(0,10));
    $limit = $this->getProperty($this->oosql, 'oosql_limit');

    $this->assertEmpty($limit);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testOrderBy()
  {
    $return = $this->invokeMethod($this->oosql, 'orderBy',array('column'));
    $order = $this->getProperty($this->oosql, 'oosql_order');

    $this->assertEquals(' ORDER BY column', $order);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
  public function testOrderByMulti()
  {
    $this->setProperty($this->oosql, 'oosql_multiFlag',true);
    $return = $this->invokeMethod($this->oosql, 'orderBy',array('column'));
    $order = $this->getProperty($this->oosql, 'oosql_order');

    $this->assertEmpty($order);
    $this->assertInstanceOf('Phiber\\oosql\\oosql', $return);
  }
}

?>