<?php


namespace App\Message;

use App\Exception\ImportOfferException;
use App\Repository\FeedRepository;
use App\Service\OfferService;
use App\Service\WebSocket\MessagePusher;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;


class ImportOffersHandler implements MessageHandlerInterface
{
	/**
	 * @var OfferService
	 */
	private $offerService;

	/**
	 * @var FeedRepository
	 */
	private $feedRepository;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var MessagePusher
	 */
	private $messagePusher;

	/**
	 * DownloadFileHandler constructor.
	 * @param OfferService $offerService
	 * @param FeedRepository $feedRepository
	 * @param MessagePusher $messagePusher
	 * @param LoggerInterface $logger
	 */
	public function __construct(OfferService $offerService, FeedRepository $feedRepository, MessagePusher $messagePusher, LoggerInterface $logger) {
		$this->offerService = $offerService;
		$this->feedRepository = $feedRepository;
		$this->logger = $logger;
		$this->messagePusher = $messagePusher;
	}

	/**
	 * @param ImportOffers $importOffers
	 * @throws ImportOfferException
	 */
	public function __invoke(ImportOffers $importOffers)
	{
		$feedId = $importOffers->getFeedId();
		$feed = $this->feedRepository->find($feedId);

		if ($feed == null) {
			$this->logger->warning('empty feed found of id ' . $feedId . ', skip queue');
			return;
		}

		try {
			$this->offerService->importOffers($feed);
			$this->messagePusher->pushFeedNotification($feed->getId());
		} catch (\Exception $e) {
			$this->logger->warning('can not import feed ' . $feed->getId());
			$this->messagePusher->pushFeedNotification($feed->getId());
			throw new ImportOfferException('Failed to import offers from feed ' . $feedId);
		}
	}
}