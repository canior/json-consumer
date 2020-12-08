<?php


namespace App\Service\WebSocket;


use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;

class MessagePusher
{
	/**
	 * @var Application
	 */
	private $application;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * MessagePusher constructor.
	 * @param KernelInterface $kernel
	 * @param LoggerInterface $logger
	 */
	public function __construct(KernelInterface $kernel, LoggerInterface $logger) {
		$this->application = new Application($kernel);
		$this->logger = $logger;
	}

	/**
	 * @param int $feedId
	 */
	public function pushFeedNotification(int $feedId) {
		try {
			$this->application->run(new ArrayInput([
				'command' => 'websocket-notification',
				'-i' => $feedId,
				'--feedId' => $feedId,
			]), new BufferedOutput());
		} catch (\Exception $e) {
			$this->logger->warning('websocket error');
		}
	}
}