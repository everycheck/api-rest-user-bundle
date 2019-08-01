<?php

namespace EveryCheck\UserApiRestBundle\Service;

use GuzzleHttp\Client;

class Api{

	public function __construct($url,$login,$password,$debug)
	{
		$this->login    = $login;
		$this->password = $password;
		$this->debug    = $debug;
		$this->token    = '';

		$this->guzzleClient = new Client([
			"base_uri" => $url,
			"timeout" => 180
		]);
	}

	public function setGuzzleClient(Client $client)
	{
		$this->guzzleClient = $guzzleClient;
	}

	public function getUserToken($isBasic = false)
	{
		$credential = [
			"login" => $this->login,
			"password" => $this->password
		];
		$response = $this->postToken($credential,$isBasic?"basic" : "jwt");
		
		return json_decode($response->getBody()->getContents());
	}

	public function setUpToken(string $token = '')
	{
		if(empty($token))
		{
			$token = $this->getUserToken()->jwt;
		}
		$this->token = $token;
	}

	public function postToken(array $credential,string $mode='jwt')
	{
		$response = $this->guzzleClient->request("POST","/tokens/$mode", $this->getOption($credential));
		
		if($response->getStatusCode() !== 201)
		{
			$this->handleError($response);
		}

		return $response;
	}

	public function getCurrentUser(): \stdClass
	{
		$response = $this->guzzleClient->request("GET","/users/current", $this->getOption());
		
		if($response->getStatusCode() !== 200)
		{
			$this->handleError($response);
		}

		return json_decode($response->getBody());
	}

	public function postUser(array $client): \stdClass
	{
		$response = $this->guzzleClient->request("POST","/user", $this->getOption($client));
		
		if($response->getStatusCode() !== 201)
		{
			$this->handleError($response);
		}

		return json_decode($response->getBody());
	}

	public function postRole(string $clientUuid, array $role): \stdClass
	{
		$response = $this->guzzleClient->request("POST","/users/$clientUuid/role", $this->getOption($role));
		
		if($response->getStatusCode() !== 201)
		{
			$this->handleError($response);
		}

		return json_decode($response->getBody());
	}


	public function handleError($response)
	{
		if($response->hasHeader("X-Debug-Token-Link"))
		{
			throw new \Exception("Error happened. See more at " . $response->getHeader("X-Debug-Token-Link"));
		}
		throw new \Exception("Error happened : " . $response->getStatusCode() . "\n Enable debug to see more.");
	}

	private function getOption($body = '', $json = true,$filename = "")
	{
		return [
			"body" => $json?json_encode($body):$body,
			"debug" => $this->debug?fopen($this->debug,'w'):false,
			"headers" => [
				'Content-Type' => $json  ? 'application/json' : 'application/octet-stream',
				"X-Auth-Token" => $this->token,
				"X-File-Name" => $filename
			]			
		];
	}
}