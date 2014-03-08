<?php

require_once 'Tests/PhiberTests.php';
require_once 'library/oosql/collection.php';

class collectionTest extends PhiberTests
{
  private $collection = null;

  public function setUp()
  {
    $this->collection = new oosql\collection();
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testAdd($object)
  {
    $this->assertEquals(0,count($this->collection));
    $this->collection->add($object);
    $this->assertEquals(1,count($this->collection));
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testObjectWhere($object)
  {
    $this->collection->add($object);
    $obj = $this->collection->objectWhere('integer',144);
    $this->assertEquals($object, $obj);
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testObjectsWhere($object)
  {
    $this->collection->add($object);
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $collection2 = $this->collection->objectsWhere('integer',144);

    $this->assertInstanceOf('oosql\\collection',$collection2);
    $this->assertEquals(3,count($this->collection));
    $this->assertEquals(2,count($collection2));
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testKeyWhere($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $collection2 = $this->collection->keyWhere('integer',144);

    $this->assertTrue(is_array($collection2));
    $this->assertEquals(3,count($this->collection));
    $this->assertEquals(2,count($collection2));
    $this->assertContains(0,$collection2);
    $this->assertContains(2,$collection2);
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testCountWhere($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $count = $this->collection->countWhere('integer',144);

    $this->assertEquals(2,$count);
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testRemoveWhere($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $this->assertEquals(3,count($this->collection));

    $this->collection->removeWhere('integer',144);

    $this->assertEquals(1,count($this->collection));
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testRestoreWhere($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $this->assertEquals(3,count($this->collection));

    $this->collection->removeWhere('integer',144);

    $this->assertEquals(1,count($this->collection));

    $this->collection->restoreWhere('integer',144);

    $this->assertEquals(3,count($this->collection));
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testGetLast($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $last = $this->collection->getLast();

    $this->assertEquals($object, $last);
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testPop($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $last = $this->collection->pop();

    $this->assertEquals($object, $last);
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testDestroy($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $this->collection->add($object);

    $this->assertEquals(3, count($this->collection));

    $this->collection->destroy();

    $this->assertEquals(0, count($this->collection));
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testIsEmpty($object)
  {
    $this->collection->add($object);

    $this->assertEquals(1, count($this->collection));

    $empty = $this->collection->isEmpty();

    $this->assertFalse($empty);

    $this->collection->destroy();

    $this->assertEquals(0, count($this->collection));

    $empty = $this->collection->isEmpty();

    $this->assertTrue($empty);
  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testExists($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $exists = $this->collection->exists($object);

    $this->assertTrue($exists);

    $exists = $this->collection->exists($object1);

    $this->assertFalse($exists);

  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testObject($object)
  {
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $obj = $this->collection->object(1);

    $obj2 = $this->collection->object();

    $this->assertEquals($object1, $obj);

    $this->assertEquals($object, $obj2);

  }
  /**
   *
   * @dataProvider providerObj
   */
  public function testCount($object)
  {
    $this->collection->add($object);
    $this->collection->add($object);
    $this->collection->add($object);
    $this->collection->add($object);
    $this->collection->add($object);
    $this->collection->add($object);

    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 255;//different value
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    $this->collection->add($object1);

    $count = $this->collection->count();

    $this->assertEquals($count, count($this->collection));

  }
  public function providerObj()
  {
    $object1 = new stdClass();
    $object1->string = 'string';
    $object1->integer = 144;
    $object1->float = 1.45;
    $object1->array = array('val1','key1'=>'val2','key2' => 3);

    return array(array($object1));
  }
}

?>