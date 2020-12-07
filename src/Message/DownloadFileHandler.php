<?php


namespace App\Message;


use App\Exception\DownloadFailedException;
use App\Repository\FeedRepository;
use App\Service\DownloadService;
use App\Service\FeedService;
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
	 * @var Application
	 */
	private $application;

	/**
	 * DownloadFileHandler constructor.
	 * @param FeedService $feedService
	 * @param FeedRepository $feedRepository
	 * @param DownloadService $downloadService
	 * @param KernelInterface $kernel
	 */
	public function __construct(FeedService $feedService, FeedRepository $feedRepository, DownloadService $downloadService, KernelInterface $kernel) {
		$this->feedService = $feedService;
		$this->feedRepository = $feedRepository;
		$this->downloadService = $downloadService;
		$this->application = new Application($kernel);
	}

	/**
	 * @param DownloadFile $downloadFile
	 * @throws DownloadFailedException
	 * @throws \Exception
	 */
	public function __invoke(DownloadFile $downloadFile) {
		$sourceUrl = $downloadFile->getUrl();
		$feed = $this->feedRepository->find($downloadFile->getFeedId());
		try {
			$url = $this->downloadService->downloadFile($sourceUrl);
			$this->feedService->completeDownload($feed, $url, true, true);
			$this->pushNotification($feed->getId());
		} catch (\Throwable $e) {
			$this->feedService->completeDownload($feed, null, false, true);
			$this->pushNotification($feed->getId());
			throw new DownloadFailedException("Can't download file " . $sourceUrl);
		}
	}

	/**
	 * @param $feedId
	 * @throws \Exception
	 */
	public function pushNotification($feedId) {
		try {
			$this->application->run(new ArrayInput([
				'command' => 'websocket-download-notification',
				'-i' => $feedId,
				'--feedId' => $feedId,
			]), new BufferedOutput());
		} catch (\Exception $e) {

		}

	}
}