<?php


namespace App\Tests\Controller;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Tests\AbstractWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class OfferControllerTest extends AbstractWebTestCase
{
	public function testEmptyDataIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));

		$client->request('GET', '/offer/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();


		$defaultTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});

		$this->assertEquals('Image ID Offer ID Name Cash Back Updated Created Update Feed', $defaultTableContent[0]);
		$this->assertEquals('No records found', $defaultTableContent[1]);
	}

	public function testOfferDataIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));
		$entityManager = self::$container->get('doctrine.orm.entity_manager');

		$offers = $this->buildOffers($entityManager);

		$client->request('GET', '/offer/');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();

		$feedTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});

		$this->assertEquals($offers['offer2']->getId() . ' 20 offer2 20 ' . $offers['offer2']->getUpdatedAtFormatted() . ' ' . $offers['offer2']->getCreatedAtFormatted()  . ' ' . $offers['offer2']->getUpdateFeed()->getId(), $feedTableContent[1]);
		$this->assertEquals($offers['offer1']->getId() . ' 10 offer1 10 ' . $offers['offer1']->getUpdatedAtFormatted() . ' ' . $offers['offer1']->getCreatedAtFormatted()  . ' ' . $offers['offer1']->getUpdateFeed()->getId(), $feedTableContent[2]);
	}

	public function testSearchIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));
		$entityManager = self::$container->get('doctrine.orm.entity_manager');

		$offers = $this->buildOffers($entityManager);

		$client->request('GET', '/offer/?offerName=' . 'er1');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();

		$feedTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});
		$this->assertEquals(2, count($feedTableContent));
		$this->assertEquals($offers['offer1']->getId() . ' 10 offer1 10 ' . $offers['offer1']->getUpdatedAtFormatted() . ' ' . $offers['offer1']->getCreatedAtFormatted()  . ' ' . $offers['offer1']->getUpdateFeed()->getId(), $feedTableContent[1]);
	}


	public function testOrderByIndexAction() {
		$client = static::createClient();
		$this->cleanData(self::$container->get('doctrine'));
		$entityManager = self::$container->get('doctrine.orm.entity_manager');

		$offers = $this->buildOffers($entityManager);

		$client->request('GET', '/offer/?orderByName=' . 'asc');
		$this->assertEquals(200, $client->getResponse()->getStatusCode());
		$crawler = $client->getCrawler();

		$feedTableContent = $crawler->filter('tr')->each(function (Crawler $node, $i) {
			return $node->text();
		});
		$this->assertEquals(3, count($feedTableContent));
		$this->assertEquals($offers['offer1']->getId() . ' 10 offer1 10 ' . $offers['offer1']->getUpdatedAtFormatted() . ' ' . $offers['offer1']->getCreatedAtFormatted()  . ' ' . $offers['offer1']->getUpdateFeed()->getId(), $feedTableContent[2]);
		$this->assertEquals($offers['offer2']->getId() . ' 20 offer2 20 ' . $offers['offer2']->getUpdatedAtFormatted() . ' ' . $offers['offer2']->getCreatedAtFormatted()  . ' ' . $offers['offer2']->getUpdateFeed()->getId(), $feedTableContent[1]);

	}

	/**
	 * @param $entityManager
	 * @return OfferEntity[]
	 */
	private function buildOffers($entityManager) {
		$offers = [];

		$feed = new FeedEntity();
		$feed->setSourceUrl("http://test.com");
		$feed->setValid(true);
		$feed->setLarge(false);
		$feed->setProcessStartedAt(time());
		$feed->setProcessCompletedAt(time());
		$entityManager->persist($feed);
		$entityManager->flush();

		$offer1 = new OfferEntity();
		$offer1->setUpdateFeed($feed);
		$offer1->setOfferId(10);
		$offer1->setCashBack(10);
		$offer1->setName('offer1');
		$entityManager->persist($offer1);
		$entityManager->flush();
		$offers['offer1'] = $offer1;

		$offer2 = new OfferEntity();
		$offer2->setUpdateFeed($feed);
		$offer2->setOfferId(20);
		$offer2->setCashBack(20);
		$offer2->setName('offer2');
		$entityManager->persist($offer2);
		$entityManager->flush();
		$offers['offer2'] = $offer2;

		return $offers;
	}
}