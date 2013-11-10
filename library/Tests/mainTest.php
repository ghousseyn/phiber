<?php

require_once '../main.class.php';

require_once 'PHPUnit\Framework\TestCase.php';

/**
 * main test case.
 */
class mainTest extends PHPUnit_Framework_TestCase {
	
	/**
	 *
	 * @var main
	 */
	private $main;
	
	/**
	 * Prepares the environment before running a test.
	 */
	
	
	/**
	 * Cleans up the environment after running a test.
	 */
	protected function tearDown() {
		// TODO Auto-generated mainTest::tearDown()
		
		$this->main = null;
		
		parent::tearDown ();
	}
	
	/**
	 * Constructs the test case.
	 */
	public function __construct() {
		// TODO Auto-generated constructor
	}
	
	/**
	 * Tests main::getInstance()
	 */
	public function testGetInstance() {
		// TODO Auto-generated mainTest::testGetInstance()
		//$this->markTestIncomplete ( "getInstance test not implemented" );
		
		$this->assertInstanceOf('main', main::getInstance());
	
	}
	
// 	/**
// 	 * Tests main->run()
// 	 */
// 	public function testRun() {
// 		// TODO Auto-generated mainTest->testRun()
// 		$this->markTestIncomplete ( "run test not implemented" );
		
// 		$this->main->run(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->getView()
// 	 */
// 	public function testGetView() {
// 		// TODO Auto-generated mainTest->testGetView()
// 		$this->markTestIncomplete ( "getView test not implemented" );
		
// 		$this->main->getView(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->_view()
// 	 */
// 	public function test_view() {
// 		// TODO Auto-generated mainTest->test_view()
// 		$this->markTestIncomplete ( "_view test not implemented" );
		
// 		$this->main->_view(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->renderLayout()
// 	 */
// 	public function testRenderLayout() {
// 		// TODO Auto-generated mainTest->testRenderLayout()
// 		$this->markTestIncomplete ( "renderLayout test not implemented" );
		
// 		$this->main->renderLayout(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->router()
// 	 */
// 	public function testRouter() {
// 		// TODO Auto-generated mainTest->testRouter()
// 		$this->markTestIncomplete ( "router test not implemented" );
		
// 		$this->main->router(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->isValidURI()
// 	 */
// 	public function testIsValidURI() {
// 		// TODO Auto-generated mainTest->testIsValidURI()
// 		$this->markTestIncomplete ( "isValidURI test not implemented" );
		
// 		$this->main->isValidURI(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->isPost()
// 	 */
// 	public function testIsPost() {
// 		// TODO Auto-generated mainTest->testIsPost()
// 		$this->markTestIncomplete ( "isPost test not implemented" );
		
// 		$this->main->isPost(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->isGet()
// 	 */
// 	public function testIsGet() {
// 		// TODO Auto-generated mainTest->testIsGet()
// 		$this->markTestIncomplete ( "isGet test not implemented" );
		
// 		$this->main->isGet(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->getRoute()
// 	 */
// 	public function testGetRoute() {
// 		// TODO Auto-generated mainTest->testGetRoute()
// 		$this->markTestIncomplete ( "getRoute test not implemented" );
		
// 		$this->main->getRoute(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->dispatch()
// 	 */
// 	public function testDispatch() {
// 		// TODO Auto-generated mainTest->testDispatch()
// 		$this->markTestIncomplete ( "dispatch test not implemented" );
		
// 		$this->main->dispatch(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->_request()
// 	 */
// 	public function test_request() {
// 		// TODO Auto-generated mainTest->test_request()
// 		$this->markTestIncomplete ( "_request test not implemented" );
		
// 		$this->main->_request(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->setVars()
// 	 */
// 	public function testSetVars() {
// 		// TODO Auto-generated mainTest->testSetVars()
// 		$this->markTestIncomplete ( "setVars test not implemented" );
		
// 		$this->main->setVars(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->register()
// 	 */
// 	public function testRegister() {
// 		// TODO Auto-generated mainTest->testRegister()
// 		$this->markTestIncomplete ( "register test not implemented" );
		
// 		$this->main->register(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->get()
// 	 */
// 	public function testGet() {
// 		// TODO Auto-generated mainTest->testGet()
// 		$this->markTestIncomplete ( "get test not implemented" );
		
// 		$this->main->get(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->load()
// 	 */
// 	public function testLoad() {
// 		// TODO Auto-generated mainTest->testLoad()
// 		$this->markTestIncomplete ( "load test not implemented" );
		
// 		$this->main->load(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->__set()
// 	 */
// 	public function test__set() {
// 		// TODO Auto-generated mainTest->test__set()
// 		$this->markTestIncomplete ( "__set test not implemented" );
		
// 		$this->main->__set(/* parameters */);
	
// 	}
	
// 	/**
// 	 * Tests main->__get()
// 	 */
// 	public function test__get() {
// 		// TODO Auto-generated mainTest->test__get()
// 		$this->markTestIncomplete ( "__get test not implemented" );
		
// 		$this->main->__get(/* parameters */);
	
// 	}

}

