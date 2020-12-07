<?php


namespace App\Tests;


use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class MockMessageBus implements MessageBusInterface
{
	private $memory;

	/**
	 * MockMessageBus constructor.
	 */
	public function __construct() {
		$this->memory = [];
	}

	public function resetMemory() {
		$this->memory = [];
	}

	public function getMemory() {
		return $this->memory;
	}

	/**
	 * @inheritDoc
	 */
	public function dispatch($message, array $stamps = []): Envelope {
		$this->memory[] = $message;
		return new Envelope($message);
	}
}