<?php

namespace Async;

use \Http\IRequest;

interface IAsyncClient {
	
	public function getRequest( $name );
	public function addRequest($name, IRequest $request );
	public function getResponse( $name );
	public function addHeaderJwt ( IRequest $request, $key, $data );
	public function setDebug( $debug );
	public function hasError( );
	public function run();
	public static function createRequest($url, $method);
}
