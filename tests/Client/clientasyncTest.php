<?php
// use Client\ClientAsync;
// use Client\Api\IClientApi;
// use Client\Api\ClientApi;
// use Client\Api\ClientDb;

// class ClientAsyncTest extends PHPUnit_Framework_TestCase {
// 	private $request;
// 	public function setUp() {
// 		$this->client = new ClientAsync ();
// 	}
// 	public function testConstructor() {
// 		$db = $this->client->create ( 'db' );
// 		// Interface
// 		$inst = $db instanceof IClientApi;
// 		$this->assertEquals ( $inst, true );
// 		// Real Class
// 		$inst = $db instanceof ClientDb;
// 		$this->assertEquals ( $inst, true );
// 		// Abstract
// 		$inst = $db instanceof ClientApi;
// 		$this->assertEquals ( $inst, true );
// 	}
// 	public function testConstructorException() {
// 		try {
// 			$db = $this->client->create ( 'db_error' );
// 			$error = true;
// 		} catch ( \Exception $e ) {
// 			$error = false;
// 		}
// 		$this->assertFalse ( $error );
// 	}
// }