<?php


namespace App\Tests;


use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class MockHttpClient implements HttpClientInterface
{
	/**
	 * @inheritDoc
	 */
	public function request(string $method, string $url, array $options = []): ResponseInterface {
		// TODO: Implement request() method.
		echo "Cao";exit;
	}

	/**
	 * @inheritDoc
	 */
	public function stream($responses, float $timeout = null): ResponseStreamInterface {
		// TODO: Implement stream() method.
	}
}