<?php

require_once 'PHPUnit\Framework\TestSuite.php';

require_once 'PHPUnit\Extensions\OutputTestCase.php';

require_once 'PHPUnit\Framework\TestSuite\DataProvider.php';

require_once 'PHPUnit\Extensions\PhptTestSuite.php';

/**
 * Static test suite.
 */
class codupSuite extends PHPUnit_Framework_TestSuite {
	
	/**
	 * Constructs the test suite handler.
	 */
	public function __construct() {
		$this->setName ( 'codupSuite' );
		
		$this->addTestSuite ( 'PHPUnit_Extensions_OutputTestCase' );
		
		$this->addTestSuite ( 'PHPUnit_Framework_TestSuite_DataProvider' );
		
		$this->addTestSuite ( 'PHPUnit_Extensions_PhptTestSuite' );
	
	}
	
	/**
	 * Creates the suite.
	 */
	public static function suite() {
		return new self ();
	}
}

