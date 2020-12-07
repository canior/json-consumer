<?php


namespace App\Service;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Exception\ImportOfferException;
use App\Message\ImportOffers;
use App\Repository\FeedRepository;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

class OfferService
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var FeedRepository
	 */
	private $feedRepository;

	/**
	 * @var OfferRepository
	 */
	private $offerRepository;

	/**
	 * @var DownloadService
	 */
	private $downloadService;

	/**
	 * @var TransactionalService
	 */
	private $transactionalService;

	/**
	 * @var MessageBusInterface
	 */
	private $messageBus;

	/**
	 * OfferService constructor.
	 * @param EntityManagerInterface $entityManager
	 * @param TransactionalService $transactionalService
	 * @param FeedRepository $feedRepository
	 * @param OfferRepository $offerRepository
	 * @param DownloadService $downloadService
	 * @param MessageBusInterface $messageBus
	 */
	public function __construct(EntityManagerInterface $entityManager, TransactionalService $transactionalService, FeedRepository $feedRepository, OfferRepository $offerRepository, DownloadService $downloadService, MessageBusInterface $messageBus) {
		$this->entityManager = $entityManager;
		$this->transactionalService = $transactionalService;
		$this->feedRepository = $feedRepository;
		$this->downloadService = $downloadService;
		$this->offerRepository = $offerRepository;
		$this->messageBus = $messageBus;
	}

	/**
	 * @param int $feedId
	 * @throws ImportOfferException
	 */
	public function processOffers(int $feedId) {
		$feed = $this->feedRepository->find($feedId);
		try {
			if ($feed->getLarge() === true) {
				$feed->setProcessStartedAt(time());
				$this->entityManager->persist($feed);
				$this->entityManager->flush();
				$this->messageBus->dispatch(new ImportOffers($feedId));
			} else {
				$feed->setProcessStartedAt(time());
				$this->importOffers($feed);
			}
		} catch (\Exception $e) {
			throw new ImportOfferException('Failed to import offers from feed ' . $feedId);
		}
	}

	/**
	 * @param FeedEntity $feed
	 * @throws \Exception
	 */
	public function importOffers(FeedEntity $feed) {
		$json = file_get_contents($feed->getUrl());

		if ($feed->isSkipError()) {
			$offers = $this->filterOutErrorOffers($json);
		} else {
			$offers = $this->deserialize($json);
		}

		/**
		 * Pessimistic write implemented to avoid concurrency
		 */
		$transactionalFunction = function () use ($feed, $offers) {
			foreach ($offers as $offer) {
				$offerEntity = $this->offerRepository->findByOfferId($offer->getOfferId());
				if ($offerEntity != null && !$feed->isForceUpdate()) {
					continue;
				}
				$offer->setUpdateFeed($feed);
				$feed->setProcessCompletedAt(time());
				$this->entityManager->persist($feed);
				$this->entityManager->persist($offer);
			}
		};

		$this->transactionalService->transactional($transactionalFunction);
	}


	/**
	 * @param $data string
	 * @return OfferEntity[]
	 */
	public function deserialize($data) {
		$dataArray = json_decode($data, true);
		$offersJson = json_encode($dataArray['offers']);
		$encoders = [new JsonEncoder()];
		$normalizers = [new GetSetMethodNormalizer(null, new CamelCaseToSnakeCaseNameConverter()), new ArrayDenormalizer()];
		$serializer = new Serializer($normalizers, $encoders);
		return $serializer->deserialize($offersJson, 'App\Entity\OfferEntity[]', 'json', [AbstractNormalizer::IGNORED_ATTRIBUTES => ['updateFeed']]);
	}

	/**
	 * @param $data string
	 * @return OfferEntity[]
	 */
	public function filterOutErrorOffers($data) {
		$dataArray = json_decode($data, true);
		$offers = $dataArray['offers'];
		$validOffers = [];
		foreach ($offers as $offer) {
			if ($offer['offer_id'] > 0 && $offer['cash_back'] > 0) {
				$offerEntity = new OfferEntity();
				$offerEntity->setOfferId($offer['offer_id']);
				$offerEntity->setCashBack($offer['cash_back']);
				$offerEntity->setImageUrl($offer['image_url']);
				$offerEntity->setName($offer['name']);
				$validOffers[] = $offerEntity;
			}
		}
		return $validOffers;
	}

}