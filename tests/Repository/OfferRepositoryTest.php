<?php


namespace App\Tests\Repository;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Tests\AbstractTestCase;

class OfferRepositoryTest extends AbstractTestCase
{
	public function testFindByOfferId() {
		$this->cleanData();
		$offers = $this->buildOffers();
		$offer1FromDB = $this->getOfferRepository()->findByOfferId(10);
		$this->assertEquals($offers['offer1'], $offer1FromDB);
	}

	public function testFindOfferQuery() {
		$this->cleanData();
		$offers = $this->buildOffers();

		$allOffers = $this->getOfferRepository()->findOfferQuery()->getQuery()->getResult();
		$this->assertEquals(count($offers), count($allOffers));

		$searchOffers = $this->getOfferRepository()->findOfferQuery(['name' => 'er1'])->getQuery()->getResult();
		$this->assertEquals(1, count($searchOffers));
		$this->assertEquals($offers['offer1'], $searchOffers[0]);

		$searchOffers = $this->getOfferRepository()->findOfferQuery(['name' => 'er1', 'sourceUrl' => 'test.com'])->getQuery()->getResult();
		$this->assertEquals(1, count($searchOffers));
		$this->assertEquals($offers['offer1'], $searchOffers[0]);

		$searchOffers = $this->getOfferRepository()->findOfferQuery(['feedId' => 100,'name' => 'er1', 'sourceUrl' => 'test.com'])->getQuery()->getResult();
		$this->assertEquals(0, count($searchOffers));

		$orderOffers = $this->getOfferRepository()->findOfferQuery([], ['field' => 'name', 'order' => 'desc'])->getQuery()->getResult();
		$this->assertEquals($offers['offer2'], $orderOffers[0]);
		$this->assertEquals($offers['offer1'], $orderOffers[1]);

		$orderOffers = $this->getOfferRepository()->findOfferQuery([], ['field' => 'cashBack', 'order' => 'desc'])->getQuery()->getResult();
		$this->assertEquals($offers['offer2'], $orderOffers[0]);
		$this->assertEquals($offers['offer1'], $orderOffers[1]);

		$orderOffers = $this->getOfferRepository()->findOfferQuery([], ['field' => 'cashBack', 'order' => 'asc'])->getQuery()->getResult();
		$this->assertEquals($offers['offer1'], $orderOffers[0]);
		$this->assertEquals($offers['offer2'], $orderOffers[1]);
	}

	private function buildOffers() {
		$offers = [];

		$feed = new FeedEntity();
		$feed->setSourceUrl("http://test.com");
		$feed->setValid(true);
		$feed->setLarge(false);
		$feed->setProcessStartedAt(time());
		$feed->setProcessCompletedAt(time());
		$this->getEntityManager()->persist($feed);
		$this->getEntityManager()->flush();

		$offer1 = new OfferEntity();
		$offer1->setUpdateFeed($feed);
		$offer1->setOfferId(10);
		$offer1->setCashBack(10);
		$offer1->setName('offer1');
		$this->getEntityManager()->persist($offer1);
		$this->getEntityManager()->flush();
		$offers['offer1'] = $offer1;

		$offer2 = new OfferEntity();
		$offer2->setUpdateFeed($feed);
		$offer2->setOfferId(20);
		$offer2->setCashBack(20);
		$offer2->setName('offer2');
		$this->getEntityManager()->persist($offer2);
		$this->getEntityManager()->flush();
		$offers['offer2'] = $offer2;

		return $offers;
	}
}