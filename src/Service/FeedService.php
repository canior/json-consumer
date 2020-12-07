<?php


namespace App\Service;


use App\Entity\FeedEntity;
use App\Exception\DownloadFailedException;
use App\Message\DownloadFile;
use App\Repository\FeedRepository;
use Doctrine\ORM\EntityManagerInterface;
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


	public function __construct(EntityManagerInterface $entityManager,
	                            DownloadService $downloadService,
	                            FeedRepository $feedRepository,
	                            MessageBusInterface $messageBus) {
		$this->entityManager = $entityManager;
		$this->downloadService = $downloadService;
		$this->feedRepository = $feedRepository;
		$this->messageBus = $messageBus;
	}

	/**
	 * @param int $id
	 * @return FeedEntity
	 * @throws DownloadFailedException
	 */
	public function processFeed(int $id) {

		$feed = $this->feedRepository->find($id);
		$sourceUrl = $feed->getSourceUrl();

		try {
			$isLarge = $this->downloadService->isLargeFile($sourceUrl);
			if ($isLarge) {
				$this->messageBus->dispatch(new DownloadFile($feed->getId(), $sourceUrl));
			} else {
				$url = $this->downloadService->downloadFile($sourceUrl);
				$this->completeDownload($feed, $url, true, $isLarge);
			}
		} catch (\Throwable $e) {
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