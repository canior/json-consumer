<?php


namespace App\Message;

use App\Exception\ImportOfferException;
use App\Repository\FeedRepository;
use App\Service\OfferService;
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
	 * DownloadFileHandler constructor.
	 * @param OfferService $offerService
	 * @param FeedRepository $feedRepository
	 */
	public function __construct(OfferService $offerService, FeedRepository $feedRepository) {
		$this->offerService = $offerService;
		$this->feedRepository = $feedRepository;
	}

	/**
	 * @param ImportOffers $importOffers
	 * @throws ImportOfferException
	 */
	public function __invoke(ImportOffers $importOffers)
	{
		$feedId = $importOffers->getFeedId();
		$feed = $this->feedRepository->find($feedId);
		try {
			$this->offerService->importOffers($feed);
		} catch (\Exception $e) {
			throw new ImportOfferException('Failed to import offers from feed ' . $feedId);
		}
	}
}