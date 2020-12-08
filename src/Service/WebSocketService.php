<?php


namespace App\Service;


use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WebSocketService
{
	private $ws;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * DownloadService constructor.
	 * @param ParameterBagInterface $params
	 * @param LoggerInterface $logger
	 */
	public function __construct(ParameterBagInterface $params, LoggerInterface $logger) {
		$this->ws = $params->get('ws_host') . ':' . $params->get('ws_port') ;
		$this->logger = $logger;
	}

	/**
	 * @param $messageArray
	 */
	public function sendMessage($messageArray) {
		try {
			$this->logger->info('connect to ' . $this->ws);
			sleep(5); //give server to redirect page, temp solution, should do retry
			\Ratchet\Client\connect($this->ws)->then(function ($conn) use ($messageArray) {
				$conn->send(json_encode($messageArray));
				$conn->close();
			}, function (\Exception $e) {
				$this->logger->info('cannot connect to ' . $this->ws);
			});
		} catch (\Exception $e) {
			$this->logger->error('failed connect to ' . $this->ws);
		}
	}
}