<?php


namespace App\Message;


use App\Exception\DownloadFailedException;
use App\Repository\FeedRepository;
use App\Service\DownloadService;
use App\Service\FeedService;
use App\Service\WebSocket\MessagePusher;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DownloadFileHandler implements MessageHandlerInterface
{
	/**
	 * @var FeedService
	 */
	private $feedService;

	/**
	 * @var FeedRepository
	 */
	private $feedRepository;

	/**
	 * @var DownloadService
	 */
	private $downloadService;


	/**
	 * @var MessagePusher
	 */
	private $messagePusher;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * DownloadFileHandler constructor.
	 * @param FeedService $feedService
	 * @param FeedRepository $feedRepository
	 * @param DownloadService $downloadService
	 * @param MessagePusher $messagePusher
	 * @param LoggerInterface $logger
	 */
	public function __construct(FeedService $feedService, FeedRepository $feedRepository, DownloadService $downloadService, MessagePusher $messagePusher, LoggerInterface $logger) {
		$this->feedService = $feedService;
		$this->feedRepository = $feedRepository;
		$this->downloadService = $downloadService;
		$this->messagePusher = $messagePusher;
		$this->logger = $logger;
	}

	/**
	 * @param DownloadFile $downloadFile
	 * @throws DownloadFailedException
	 * @throws \Exception
	 */
	public function __invoke(DownloadFile $downloadFile) {
		$sourceUrl = $downloadFile->getUrl();
		$feed = $this->feedRepository->find($downloadFile->getFeedId());
		if ($feed == null) {
			$this->logger->info('feed id not exist ' . $downloadFile->getFeedId() . ', skip queue');
			return;
		}

		try {
			$url = $this->downloadService->downloadFile($sourceUrl);
			$this->feedService->completeDownload($feed, $url, true, true);
			$this->messagePusher->pushFeedNotification($feed->getId());
		} catch (\Throwable $e) {
			$this->feedService->completeDownload($feed, null, false, true);
			$this->messagePusher->pushFeedNotification($feed->getId());
			throw new DownloadFailedException("Can't download file " . $sourceUrl);
		}
	}
}