<?php


namespace App\Service;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Exception\ImportOfferException;
use App\Message\ImportOffers;
use App\Repository\FeedRepository;
use App\Repository\OfferRepository;
use Doctrine\DBAL\LockMode;
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
	 * @return MessageBusInterface
	 */
	public function getMessageBus(): MessageBusInterface {
		return $this->messageBus;
	}

	/**
	 * @param MessageBusInterface $messageBus
	 */
	public function setMessageBus(MessageBusInterface $messageBus): void {
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
		$offers = $this->filterOutErrorOffers($json, $feed->isSkipError());

		/**
		 * Pessimistic write implemented to avoid concurrency
		 */
		$transactionalFunction = function () use ($feed, $offers) {
			foreach ($offers as $offer) {
				$existingOffer = $this->offerRepository->findByOfferId($offer->getOfferId());
				if ($existingOffer == null) {
					$existingOffer = new OfferEntity();
				} else {
					if (!$feed->isForceUpdate()) {
						continue;
					}
					$this->entityManager->lock($existingOffer, LockMode::PESSIMISTIC_WRITE);
					$existingOffer->setUpdatedAt();
				}

				$existingOffer->setOfferId($offer->getOfferId());
				$existingOffer->setName($offer->getName());
				$existingOffer->setCashBack($offer->getCashBack());
				$existingOffer->setImageUrl($offer->getImageUrl());
				$existingOffer->setUpdateFeed($feed);

				$this->entityManager->persist($existingOffer);
			}

			$feed->setProcessCompletedAt(time());
			$this->entityManager->persist($feed);
		};

		$this->transactionalService->transactional($transactionalFunction);
	}


	/**
	 * @deprecated
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
	 * @param $skipError bool
	 * @return OfferEntity[]
	 * @throws ImportOfferException
	 */
	public function filterOutErrorOffers($data, $skipError) {
		$dataArray = json_decode($data, true);
		$offers = $dataArray['offers'];
		$validOffers = [];
		foreach ($offers as $offer) {
			if (array_key_exists('offer_id', $offer)
				&& array_key_exists('cash_back', $offer)
				&& array_key_exists('name', $offer)
				&& $offer['cash_back'] > 0
				&& $offer['offer_id'] > 0
			) {
				$offerEntity = new OfferEntity();
				$offerEntity->setOfferId($offer['offer_id']);
				$offerEntity->setCashBack($offer['cash_back']);
				if (array_key_exists('image_url', $offer)) {
					$offerEntity->setImageUrl($offer['image_url']);
				}
				$offerEntity->setName($offer['name']);
				$validOffers[] = $offerEntity;
			} else {
				if (!$skipError) {
					throw new ImportOfferException();
				}
			}
		}
		return $validOffers;
	}

}