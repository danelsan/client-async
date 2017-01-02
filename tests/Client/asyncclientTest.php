<?php

use Async\AsyncClient;
use Async\IAsyncClient;

class AsyncClientTest extends PHPUnit_Framework_TestCase {
	private $client;
	private $request;
	public function setUp() {
		$this->client = new AsyncClient();
	}
	public function testConstructor() {
		// Interface
		$inst = $this->client instanceof IAsyncClient;
		$this->assertEquals ( $inst, true );
	}
	public function testCreateRequest_get() {
		try {
			$request = $this->client->createRequest( 'http://www.example.com' );
			$error = true;
		} catch ( \Exception $e ) {

			$error = false;
		}
		$this->assertTrue ( $error );
		$this->assertEquals( $request instanceof \Http\IRequest, true);
	}
	
	public function testCreateRequest_post() {
		try {
			$request = $this->client->createRequest( 'http://www.example.com','POST' );
			$error = true;
		} catch ( \Exception $e ) {
			$error = false;
		}
		$this->assertTrue( $error );
		$this->assertEquals( $request instanceof \Http\IRequest, true);
	}
	public function testCreateRequest_error() {
		try {
			$request = $this->client->createRequest( 'http://www.example.com','POS' );
			$request->send();
			$error = true;
		} catch ( \Exception $e ) {
			$error = false;
		}
		$this->assertFalse( $error );
		$this->assertEquals( isset($request), false );
	}
	
	public function testRunAsync() {
		$request1 = $this->client->createRequest( 'http://www.example.com','POST' );
		$request2 = $this->client->createRequest( 'http://www.example.com','GET' );
		$this->client->addRequest('n1', $request1);
		$this->client->addRequest('n2', $request2);
		
		try {
			$this->client->run();
			$error = true;
		} catch ( \Exception $e ) {
			$error = false;
		}
		
		$this->assertTrue( $error );
		
		try {
			$responses = $this->client->getResponse('n');
			$this->assertTrue( false );
		} catch ( \Exception $e ) {
			$this->assertTrue( true );
		}
		
		try {
			$response = $this->client->getResponse('n1');
			$this->assertTrue( true );
		} catch ( \Exception $e ) {
			$this->assertTrue( false );
		}
		
		$this->assertEquals( true, $response instanceof \Http\IResponse );
	}
}
