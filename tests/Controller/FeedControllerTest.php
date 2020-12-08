<?php


namespace App\Tests\Controller;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Tests\AbstractWebTestCase;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

class FeedControllerTest extends AbstractWebTestCase
{
	public function testEmptyDataIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));

		$client->request('GET', '/feed/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();


		$defaultTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});

		$this->assertEquals('ID Source URL Skip Error Force Update Created Status Offers', $defaultTableContent[0]);
		$this->assertEquals('No records found', $defaultTableContent[1]);

	}

	public function testFeedDownloadingDataIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));

		/**
		 * @var EntityManager $entityManager
		 */
		$entityManager = self::$container->get('doctrine.orm.entity_manager');

		$feed = new FeedEntity();
		$feed->setSourceUrl("http://test.com");
		$feed->setSkipError(false);
		$feed->setForceUpdate(true);
		$entityManager->persist($feed);
		$entityManager->flush();

		$client->request('GET', '/feed/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();

		$feedTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});

		$this->assertEquals('ID Source URL Skip Error Force Update Created Status Offers', $feedTableContent[0]);
		$this->assertEquals($feed->getId() . ' http://test.com No Yes ' . $feed->getCreatedAtFormatted() . ' Downloading (View Download) 0', $feedTableContent[1]);
	}

	public function testFeedDownloadedDataIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));

		/**
		 * @var EntityManager $entityManager
		 */
		$entityManager = self::$container->get('doctrine.orm.entity_manager');

		$feed = new FeedEntity();
		$feed->setSourceUrl("http://test.com");
		$feed->setSkipError(false);
		$feed->setForceUpdate(true);
		$feed->setUrl('/tmp/');
		$feed->setLarge(true);
		$feed->setStatus(FeedEntity::STATUS_DOWNLOADED);
		$entityManager->persist($feed);
		$entityManager->flush();

		$client->request('GET', '/feed/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();

		$feedTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});

		$this->assertEquals('ID Source URL Skip Error Force Update Created Status Offers', $feedTableContent[0]);
		$this->assertEquals($feed->getId() . ' http://test.com No Yes ' . $feed->getCreatedAtFormatted() . ' Downloaded (Import Offers) 0', $feedTableContent[1]);
	}

	public function testFeedWithOffersIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));

		/**
		 * @var EntityManager $entityManager
		 */
		$entityManager = self::$container->get('doctrine.orm.entity_manager');

		$feed = new FeedEntity();
		$feed->setSourceUrl("http://test.com");
		$feed->setSkipError(false);
		$feed->setForceUpdate(true);
		$feed->setUrl('/tmp/');
		$feed->setLarge(true);
		$feed->setValid(true);
		$feed->setStatus(FeedEntity::STATUS_IMPORTED);
		$feed->setProcessStartedAt(time());
		$feed->setProcessCompletedAt(time());
		$entityManager->persist($feed);
		$entityManager->flush();


		$offer = new OfferEntity();
		$offer->setName('test offer');
		$offer->setCashBack(1.0);
		$offer->setUpdateFeed($feed);
		$offer->setOfferId(123456);
		$entityManager->persist($offer);
		$entityManager->flush();
		$entityManager->refresh($feed);

		$client->request('GET', '/feed/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();

		$feedTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});

		$this->assertEquals('ID Source URL Skip Error Force Update Created Status Offers', $feedTableContent[0]);
		$this->assertEquals($feed->getId() . ' http://test.com No Yes ' . $feed->getCreatedAtFormatted() . ' Imported 1', $feedTableContent[1]);
	}
}