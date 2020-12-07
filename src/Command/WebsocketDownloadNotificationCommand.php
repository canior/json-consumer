<?php


namespace App\Command;


use App\Repository\FeedRepository;
use App\Service\WebSocketService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WebsocketDownloadNotificationCommand extends Command
{
	protected static $defaultName = "websocket-download-notification";

	/**
	 * @var WebSocketService
	 */
	private $webSocketService;

	/**
	 * @var FeedRepository
	 */
	private $feedRepository;


	protected function configure() {
		parent::configure();
		$this->setDefinition(
			new InputDefinition([
				new InputOption('feedId', 'i', InputOption::VALUE_OPTIONAL),
			])
		);
	}

	/**
	 * WebsocketDownloadNotificationCommand constructor.
	 * @param WebSocketService $webSocketService
	 * @param FeedRepository $feedRepository
	 */
	public function __construct(WebSocketService $webSocketService, FeedRepository $feedRepository) {
		parent::__construct();
		$this->webSocketService = $webSocketService;
		$this->feedRepository = $feedRepository;
	}

	/**
	 * @param InputInterface $input
	 * @param OutputInterface $output
	 * @return int
	 */
	protected function execute(InputInterface $input, OutputInterface $output) {

		$feedId = $input->getOption('feedId');
		$feed = $this->feedRepository->find($feedId);

		$this->webSocketService->sendMessage([
			'feedId' => $feedId,
			'valid' => $feed->getValid(),
		]);
		return 0;
	}
}