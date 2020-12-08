<?php


namespace App\Service;


use App\Entity\FeedEntity;
use App\Exception\DownloadFailedException;
use App\Message\DownloadFile;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class FeedService
{
	/**
	 * @var DownloadService
	 */
	private $downloadService;

	/**
	 * @var FeedRepository
	 */
	private $feedRepository;

	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;


	/**
	 * @var MessageBusInterface
	 */
	private $messageBus;

	/**
	 * @var LoggerInterface
	 */
	private $logger;


	public function __construct(EntityManagerInterface $entityManager,
	                            DownloadService $downloadService,
	                            FeedRepository $feedRepository,
	                            MessageBusInterface $messageBus, LoggerInterface $logger) {
		$this->entityManager = $entityManager;
		$this->downloadService = $downloadService;
		$this->feedRepository = $feedRepository;
		$this->messageBus = $messageBus;
		$this->logger = $logger;
	}

	/**
	 * @param int $id
	 * @return FeedEntity
	 * @throws DownloadFailedException
	 */
	public function processFeed(int $id) {
		$this->logger->info('process feed ' . $id);
		$feed = $this->feedRepository->find($id);
		if ($feed == null) {
			throw new NotFoundHttpException();
		}

		$sourceUrl = $feed->getSourceUrl();

		if (!$feed->isDownloading()) {
			$this->logger->warning('feed is processed, skip feed ' . $id);
			throw new DownloadFailedException('Duplicated download');
		}

		try {
			$isLarge = $this->downloadService->isLargeFile($sourceUrl);
			if ($isLarge) {
				$this->logger->info('large file, will download async');
				$this->messageBus->dispatch(new DownloadFile($feed->getId(), $sourceUrl));
			} else {
				$this->logger->info('small file, download right away');
				$url = $this->downloadService->downloadFile($sourceUrl);
				$this->completeDownload($feed, $url, true, $isLarge);
			}
		} catch (\Throwable $e) {
			$this->logger->warning('something wrong when downloading feed');
			$this->completeDownload($feed, null, false, null);
			throw new DownloadFailedException("Can't download file " . $sourceUrl);
		}

		return $feed;
	}

	/**
	 * @param FeedEntity $feed
	 * @param $url
	 * @param $isValid
	 * @param $isLarge
	 */
	public function completeDownload(FeedEntity $feed, $url, $isValid, $isLarge) {
		$feed->setUrl($url);
		$feed->setLarge($isLarge);
		$feed->setValid($isValid);
		$feed->setStatus($isValid ? FeedEntity::STATUS_DOWNLOADED : FeedEntity::STATUS_DOWNLOADED_ERROR);
		$this->entityManager->persist($feed);
		$this->entityManager->flush();
	}


	/**
	 * @param FeedEntity $feed
	 */
	public function saveOrUpdateFeed(FeedEntity $feed) {
		$this->entityManager->persist($feed);
		$this->entityManager->flush();
	}

}