<?php

namespace Async;

use \Http\IRequest;
use \Http\Request;
use \Http\Client;
use \Http\Response;

class AsyncClient implements IAsyncClient {
	
	private $requests;
	private $responses;
	private $terminated;
	private $runned;
	private $debug;
	private $error;
	
	public function __construct() {
		$this->requests = array();
		$this->responses = array();
		$this->terminated = false;
		$this->runned = false;
		$this->debug = false;
		$this->error = false;
	}
	
	public function getRequest($name) {
		if ( !isset( $this->requests[$name]) )
			throw new \Exception("Async Request '$name' not found");
		
		return $this->requests[$name];
	}
	
	public function addRequest( $name, IRequest $request ) {
		$this->requests[$name]	= $request;
	}
	
	/**
	 * 
	 * @param string $name
	 * @return IResponse
	 */
	public function getResponse( $name ) {
		if ( ! $this->runned )
			throw new \Exception("Async not runned ");
		
		if ( ! $this->terminated )
			throw new \Exception("Async not finished");
		
		if ( !isset( $this->responses[$name]) )
			throw new \Exception("Async Response '$name' not found");
		
		return $this->responses[$name];
	}
	
	public function setDebug( $debug ) {
		$this->debug = 	boolval($debug);	
	}
	
	
 	public function addHeaderJwt ( IRequest $request, $key, $data = array() ) {
 		$url = $request->getUrl();
		$token = array (
				"iss" => $url,
				"aud" => $url,
				"time" => microtime ( true ),
				"iat" => time () - 10,
				"nbf" => time () - 10,
				"data" => $data
		);
		$jwt = \JWT::encode ( $token, $key );
		$request->addHeader ( 'Authorization', 'Bearer ' . $jwt );
		return $request;
 	}
	
	public function run() {
		$this->runned = true;
		
		$start = microtime ( TRUE );
		$r = array ();
		
		if ( version_compare ( phpversion (), '5.5.0', '<' ) ) {
			$handler = new \GuzzleHttp\Handler\StreamHandler ();
			$stack = \GuzzleHttp\HandlerStack::create ( $handler ); // Wrap w/ middleware
			$client = new \GuzzleHttp\Client ( array (
					'handler' => $stack
			) );
		} else {
			$client = new \GuzzleHttp\Client ();
		}
		
		foreach ( $this->requests as $k => $request ) {
			$r [$k] = $client->requestAsync ( $request->getMethod (), $request->getUrl(), $this->getOptionsGuzzle ( $request ) );
			$r [$k]->then ( function ($response) {
			}, function (\Exception $e) {
				
			} );
		}
		
		// Wait for all response
		$result = \GuzzleHttp\Promise\unwrap ( $r );
		$stop = microtime ( TRUE );
		$this->bench_time = $stop - $start;
		foreach ( $result as $k => $v ) {
			if ($v->getStatusCode () !== 200)
				$this->errors = true;
		}

		// return $result;
		$this->setResponses( $result );
		
		$this->terminated = true;
	}
	
	public function hasError() {
		return $this->error;
	}
	
	public static function createRequest($url, $method = 'GET') {
		return Request::Http( $url, $method );
	}
	
	private function setResponses ( $result ) {
		if ( ! $this->runned )
			throw new \Exception("Async not runned ");
		
		$responses = array();
		foreach ( $result as $k => $v ) {
			$body = $v->getBody()->getContents();
			$status = $v->getStatusCode();
			$headers = $v->getHeaders();
			$responses[$k] = Response::Http($body, $status , $headers );
		}
		$this->responses = $responses;
	}
	
	private function getOptionsGuzzle( IRequest $request) {
		$options = array ();
		$post = $request->getPost ();
		if (! empty ( $post ) ) {
			$options ['form_params'] = $post;
		}
	
		$headers = $this->getHeaders ( $request->getHeaders () );

		if (! empty ( $headers ))
			$options ['headers'] = $headers;
		
		$options ['http_errors'] = false;
		$options ['debug'] = $this->debug;
		$options ['allow_redirects'] = true;
		$options ['stream'] = false;
		$options ['track_redirects'] = true;
		return $options;
	}
	
	/**
	 * Return headers with key like name
	 */
	private function getHeaders( array $headers ) {
		if ( empty( $headers ) ) 
			return $headers;
		
		$result = array();
		foreach  ( $headers as $header ) {
			$split = explode(': ', $header);
			if ( count( $split ) == 2 ) {
				$key = $split[0];
				$value = $split[1];
				$result[$key] = $value;
			}
		}
		return $result;
		
	}
}
