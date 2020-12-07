<?php


namespace App\Tests;


use App\Utils\Config;
use Symfony\Contracts\HttpClient\ChunkInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\ResponseStreamInterface;

class MockHttpClient implements HttpClientInterface
{
	const LARGE_FILE_URL = "http://largeFile";
	const SMALL_FILE_URL = "http://small";
	const INVALID_URL = "http://invalid";
	const SHORT_FILE_CONTENT = '{
			  "batch_id": 0,
			  "offers": [
			    {
			      "offer_id": "40408",
			      "name": "Buy 2: Select TRISCUIT Crackers",
			      "image_url": "https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg",
			      "cash_back": 1.0
			    }
			]
		}';
	const LARGE_FILE_CONTENT = '{
			  "batch_id": 0,
			  "offers": [
			    {
			      "offer_id": "40408",
			      "name": "Buy 2: Select TRISCUIT Crackers",
			      "image_url": "https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg",
			      "cash_back": 1.0
			    },
			    {
			      "offer_id": "39271",
			      "name": "Tide Liquid Detergent",
			      "image_url": "https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg",
			      "cash_back": 1.0
			    }
			]
		}';

	private $responseStream;

	/**
	 * @inheritDoc
	 */
	public function request(string $method, string $url, array $options = []): ResponseInterface {

		$mockResponse = new MockTestResponse();
		if ($url == self::LARGE_FILE_URL) {
			$headers = ['content-length' => [0 => Config::REALTIME_DOWNLOAD_LIMIT + 1]];
			$mockResponse->setHeaders($headers);
			$this->responseStream = new MockLargeFileResponseSteam();
			return $mockResponse;
		} else {
			if ($url == self::SMALL_FILE_URL) {
				$headers = ['content-length' => [0 => Config::REALTIME_DOWNLOAD_LIMIT - 1]];
				$mockResponse->setHeaders($headers);
				$this->responseStream = new MockShortFileResponseSteam();
				return $mockResponse;
			}
		}
		throw new \Exception("url is not supported");

	}

	/**
	 * @inheritDoc
	 */
	public function stream($responses, float $timeout = null): ResponseStreamInterface {
		return $this->responseStream;
	}
}

class MockTestResponse implements ResponseInterface
{

	private $headers;

	public function setHeaders($headers) {
		$this->headers = $headers;
	}

	/**
	 * @inheritDoc
	 */
	public function getStatusCode(): int {
		// TODO: Implement getStatusCode() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getHeaders(bool $throw = true): array {
		return $this->headers;
	}

	/**
	 * @inheritDoc
	 */
	public function getContent(bool $throw = true): string {
		// TODO: Implement getContent() method.
	}

	/**
	 * @inheritDoc
	 */
	public function toArray(bool $throw = true): array {
		// TODO: Implement toArray() method.
	}

	/**
	 * @inheritDoc
	 */
	public function cancel(): void {
		// TODO: Implement cancel() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getInfo(string $type = null) {
		// TODO: Implement getInfo() method.
	}
}

class MockShortFileResponseSteam implements ResponseStreamInterface
{
	/**
	 * @inheritDoc
	 */
	public function next() {
		// TODO: Implement next() method.
	}

	/**
	 * @inheritDoc
	 */
	public function valid() {
		// TODO: Implement valid() method.
	}

	/**
	 * @inheritDoc
	 */
	public function rewind() {
		// TODO: Implement rewind() method.
	}

	public function key(): ResponseInterface {
		// TODO: Implement key() method.
	}

	public function current(): ChunkInterface {
		return new MockShortFileChunk();
	}
}

class MockShortFileChunk implements ChunkInterface
{

	/**
	 * @inheritDoc
	 */
	public function isTimeout(): bool {
		// TODO: Implement isTimeout() method.
	}

	/**
	 * @inheritDoc
	 */
	public function isFirst(): bool {
		// TODO: Implement isFirst() method.
	}

	/**
	 * @inheritDoc
	 */
	public function isLast(): bool {
		// TODO: Implement isLast() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getInformationalStatus(): ?array {
		// TODO: Implement getInformationalStatus() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getContent(): string {
		return MockHttpClient::SHORT_FILE_CONTENT;
	}

	/**
	 * @inheritDoc
	 */
	public function getOffset(): int {
		// TODO: Implement getOffset() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getError(): ?string {
		// TODO: Implement getError() method.
	}
}

class MockLargeFileResponseSteam implements ResponseStreamInterface
{
	/**
	 * @inheritDoc
	 */
	public function next() {
		// TODO: Implement next() method.
	}

	/**
	 * @inheritDoc
	 */
	public function valid() {
		// TODO: Implement valid() method.
	}

	/**
	 * @inheritDoc
	 */
	public function rewind() {
		// TODO: Implement rewind() method.
	}

	public function key(): ResponseInterface {
		// TODO: Implement key() method.
	}

	public function current(): ChunkInterface {
		return new MockLargeFileChunk();
	}
}

class MockLargeFileChunk implements ChunkInterface
{

	/**
	 * @inheritDoc
	 */
	public function isTimeout(): bool {
		// TODO: Implement isTimeout() method.
	}

	/**
	 * @inheritDoc
	 */
	public function isFirst(): bool {
		// TODO: Implement isFirst() method.
	}

	/**
	 * @inheritDoc
	 */
	public function isLast(): bool {
		// TODO: Implement isLast() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getInformationalStatus(): ?array {
		// TODO: Implement getInformationalStatus() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getContent(): string {
		return MockHttpClient::LARGE_FILE_CONTENT;
	}

	/**
	 * @inheritDoc
	 */
	public function getOffset(): int {
		// TODO: Implement getOffset() method.
	}

	/**
	 * @inheritDoc
	 */
	public function getError(): ?string {
		// TODO: Implement getError() method.
	}
}