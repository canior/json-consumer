<?php


namespace App\Service\WebSocket;


use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class MessageHandler implements MessageComponentInterface
{

	protected $connections;

	public function __construct() {
		$this->connections = new \SplObjectStorage();
	}

	/**
	 * @inheritDoc
	 */
	function onOpen(ConnectionInterface $conn) {
		$this->connections->attach($conn);
	}

	/**
	 * @inheritDoc
	 */
	function onClose(ConnectionInterface $conn) {
		$this->connections->detach($conn);
	}

	/**
	 * @inheritDoc
	 */
	function onError(ConnectionInterface $conn, \Exception $e) {
		$this->connections->detach($conn);
		$conn->close();
	}

	/**
	 * @inheritDoc
	 */
	function onMessage(ConnectionInterface $from, $message) {
		foreach ($this->connections as $connection) {
			if ($connection === $from) {
				continue;
			}
			$connection->send($message);
		}
	}
}