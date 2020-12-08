<?php


namespace App\Tests;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Repository\FeedRepository;
use App\Repository\OfferRepository;
use App\Service\DownloadService;
use App\Service\FeedService;
use App\Service\OfferService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Messenger\MessageBusInterface;


class AbstractTestCase extends KernelTestCase
{
	private $mockMessageBus;

	protected function setUp() {
		self::bootKernel();
		$this->mockMessageBus = new MockMessageBus();
	}

	/**
	 * @return ManagerRegistry
	 */
	protected function getDoctrine() {
		return self::$container->get('doctrine');
	}

	/**
	 * @return EntityManagerInterface
	 */
	protected function getEntityManager() {
		return self::$container->get('doctrine.orm.entity_manager');
	}

	/**
	 * @return MessageBusInterface
	 */
	protected function getAsyncMessageBus() {
		return self::$container->get('messenger.default_bus');
	}


	protected function getMockMessageBus() {
		return $this->mockMessageBus;
	}

	protected function getMockLogger() {
		return new MockLogger();
	}

	/**
	 * @return FeedService
	 */
	protected function getFeedService() {
		return self::$container->get(FeedService::class);
	}

	protected function getMockFeedService() {
		return new FeedService($this->getEntityManager(), $this->getMockDownloadService(), $this->getFeedRepository(), $this->getMockMessageBus(), $this->getMockLogger());
	}

	/**
	 * @return OfferService
	 */
	protected function getOfferService() {
		return self::$container->get(OfferService::class);
	}

	/**
	 * @return DownloadService
	 */
	protected function getDownloadService() {
		return self::$container->get(DownloadService::class);
	}

	/**
	 * @return FeedRepository
	 */
	protected function getFeedRepository() : FeedRepository {
		return $this->getEntityManager()->getRepository(FeedEntity::class);
	}

	/**
	 * @return OfferRepository
	 */
	protected function getOfferRepository() : OfferRepository {
		return $this->getEntityManager()->getRepository(OfferEntity::class);
	}

	/**
	 * @return DownloadService
	 */
	protected function getMockDownloadService() {
		$httpClient = new MockHttpClient();
		$parameterBag = new MockParameterBag();
		return new DownloadService($parameterBag, $httpClient);
	}

	protected function cleanData() {
		$this->getDoctrine()->getConnection()->exec('set foreign_key_checks=0');
		$this->getDoctrine()->getConnection()->exec('delete from feed');
		$this->getDoctrine()->getConnection()->exec('delete from offer');
		$this->getDoctrine()->getConnection()->exec('set foreign_key_checks=1');
	}
}