<?php


namespace App\Tests\Service;


use App\Entity\FeedEntity;
use App\Entity\OfferEntity;
use App\Exception\ImportOfferException;
use App\Message\ImportOffers;
use App\Tests\AbstractTestCase;
use Doctrine\ORM\NonUniqueResultException;
use Throwable;

class OfferServiceTest extends AbstractTestCase
{
	const OFFERS_JSON = '{
			  "batch_id": 0,
			  "offers": [
			    {
			      "offer_id": "40408",
			      "name": "Buy 2: Select TRISCUIT Crackers",
			      "image_url": "https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg",
			      "cash_back": 1.0
			    },
			    {
			      "offer_id": "39271",
			      "name": "Tide Liquid Detergent",
			      "image_url": "https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg",
			      "cash_back": 2.0
			    },
			    {
			      "offer_id": "-100",
			      "name": "error row",
			      "cash_back": -2.0
			    }
			]
		}';

	const OFFERS_UPDATE_JSON = '{
			  "batch_id": 0,
			  "offers": [
			    {
			      "offer_id": "40408",
			      "name": "Buy 2: Select TRISCUIT Crackers2",
			      "image_url": "https://d3bx4ud3idzsqf1.cloudfront.net/public/production/6840/67561_1535141624.jpg",
			      "cash_back": 2.0
			    },
			    {
			      "offer_id": "39271",
			      "name": "Tide Liquid Detergent 2",
			      "image_url": "https://2d3bx4ud3idzsqf2.cloudfront.net/public/production/4902/56910_1527084051.jpg",
			      "cash_back": 3.0
			    },
			    {
			      "offer_id": "1000",
			      "name": "new row 3",
			      "cash_back": 4.0
			    }
			]
		}';

	public function testDeserialize() {
		$offers = $this->getOfferService()->deserialize(self::OFFERS_JSON);
		$offer1 = $offers[0];

		$this->assertEquals(40408, $offer1->getOfferId());
		$this->assertEquals("Buy 2: Select TRISCUIT Crackers", $offer1->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg", $offer1->getImageUrl());
		$this->assertEquals(1.0, $offer1->getCashBack());

		$offer2 = $offers[1];

		$this->assertEquals(39271, $offer2->getOfferId());
		$this->assertEquals("Tide Liquid Detergent", $offer2->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg", $offer2->getImageUrl());
		$this->assertEquals(2.0, $offer2->getCashBack());
	}

	public function testFilterOutErrorOffersWithSkipErrorOn() {
		$offers = $this->getOfferService()->filterOutErrorOffers(self::OFFERS_JSON, true);

		$offer1 = $offers[0];
		$offer2 = $offers[1];

		$this->assertEquals(count($offers), 2);
		$this->assertEquals(40408, $offer1->getOfferId());
		$this->assertEquals("Buy 2: Select TRISCUIT Crackers", $offer1->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg", $offer1->getImageUrl());
		$this->assertEquals(1.0, $offer1->getCashBack());

		$this->assertEquals(39271, $offer2->getOfferId());
		$this->assertEquals("Tide Liquid Detergent", $offer2->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg", $offer2->getImageUrl());
		$this->assertEquals(2.0, $offer2->getCashBack());
	}

	public function testFilterOutErrorOffersWithSkipErrorOff() {
		try {
			$this->getOfferService()->filterOutErrorOffers(self::OFFERS_JSON, false);
		} catch (Throwable $e) {
			$this->assertTrue($e instanceof ImportOfferException);
		}
	}

	/**
	 * skipError = true, forceUpdate = true
	 * @throws NonUniqueResultException
	 */
	public function testImportOffersWithForceUpdateOn() {
		$this->cleanData();

		//create feed, first import
		$url1 = '/tmp/' . uniqid();
		file_put_contents($url1, self::OFFERS_JSON);
		$feed1 = new FeedEntity();
		$feed1->setStatus(FeedEntity::STATUS_IMPORTING);
		$feed1->setSourceUrl("http://createFeed.com");
		$feed1->setSkipError(true);
		$feed1->setForceUpdate(true);
		$feed1->setUrl($url1);
		$this->getEntityManager()->persist($feed1);
		$this->getEntityManager()->flush();


		$this->getOfferService()->importOffers($feed1);

		/**
		 * @var OfferEntity $offer1
		 */
		$offer1 = $this->getOfferRepository()->findByOfferId('40408');
		$this->assertEquals(40408, $offer1->getOfferId());
		$this->assertEquals("Buy 2: Select TRISCUIT Crackers", $offer1->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg", $offer1->getImageUrl());
		$this->assertEquals(1.0, $offer1->getCashBack());
		$this->assertEquals(FeedEntity::STATUS_IMPORTED, $feed1->getStatus());

		/**
		 * @var OfferEntity $offer2
		 */
		$offer2 = $this->getOfferRepository()->findByOfferId('39271');
		$this->assertEquals(39271, $offer2->getOfferId());
		$this->assertEquals("Tide Liquid Detergent", $offer2->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg", $offer2->getImageUrl());
		$this->assertEquals(2.0, $offer2->getCashBack());


		//update feed, update import
		$url2 = '/tmp/' . uniqid();
		file_put_contents($url2, self::OFFERS_UPDATE_JSON);
		$feed2 = new FeedEntity();
		$feed2->setStatus(FeedEntity::STATUS_IMPORTING);
		$feed2->setSourceUrl("http://updatetFeed.com");
		$feed2->setSkipError(true);
		$feed2->setForceUpdate(true);
		$feed2->setUrl($url2);
		$this->getEntityManager()->persist($feed2);
		$this->getEntityManager()->flush();

		sleep(2);

		$this->getOfferService()->importOffers($feed2);

		/**
		 * @var OfferEntity $offer1
		 */
		$offer1 = $this->getOfferRepository()->findOneBy(['offerId' => 40408]);
		$this->getEntityManager()->refresh($offer1);
		$this->assertEquals("Buy 2: Select TRISCUIT Crackers2", $offer1->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf1.cloudfront.net/public/production/6840/67561_1535141624.jpg", $offer1->getImageUrl());
		$this->assertEquals(2.0, $offer1->getCashBack());
		$this->assertNotEquals($offer1->getUpdatedAt(), $offer1->getCreatedAt());

		/**
		 * @var OfferEntity $offer2
		 */
		$offer2 = $this->getOfferRepository()->findOneBy(['offerId' => 39271]);
		$this->assertEquals("Tide Liquid Detergent 2", $offer2->getName());
		$this->assertEquals("https://2d3bx4ud3idzsqf2.cloudfront.net/public/production/4902/56910_1527084051.jpg", $offer2->getImageUrl());
		$this->assertEquals(3.0, $offer2->getCashBack());
		$this->assertNotEquals($offer2->getUpdatedAt(), $offer2->getCreatedAt());


		/**
		 * @var OfferEntity $offer3
		 */
		$offer3 = $this->getOfferRepository()->findOneBy(['offerId' => 1000]);
		$this->assertEquals($offer3->getUpdatedAt(), $offer3->getCreatedAt());

		$this->assertEquals(3, count($this->getOfferRepository()->findAll()));
	}


	public function testImportOffersWithForceUpdateOff() {
		$this->cleanData();

		//create feed, first import
		$url1 = '/tmp/' . uniqid();
		file_put_contents($url1, self::OFFERS_JSON);
		$feed1 = new FeedEntity();
		$feed1->setStatus(FeedEntity::STATUS_IMPORTING);
		$feed1->setSourceUrl("http://createFeed.com");
		$feed1->setSkipError(true);
		$feed1->setForceUpdate(true);
		$feed1->setUrl($url1);
		$this->getEntityManager()->persist($feed1);
		$this->getEntityManager()->flush();


		$this->getOfferService()->importOffers($feed1);

		/**
		 * @var OfferEntity $offer1
		 */
		$offer1 = $this->getOfferRepository()->findByOfferId('40408');
		$this->assertEquals(40408, $offer1->getOfferId());
		$this->assertEquals("Buy 2: Select TRISCUIT Crackers", $offer1->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg", $offer1->getImageUrl());
		$this->assertEquals(1.0, $offer1->getCashBack());

		/**
		 * @var OfferEntity $offer2
		 */
		$offer2 = $this->getOfferRepository()->findByOfferId('39271');
		$this->assertEquals(39271, $offer2->getOfferId());
		$this->assertEquals("Tide Liquid Detergent", $offer2->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg", $offer2->getImageUrl());
		$this->assertEquals(2.0, $offer2->getCashBack());


		//update feed, update import
		$url2 = '/tmp/' . uniqid();
		file_put_contents($url2, self::OFFERS_UPDATE_JSON);
		$feed2 = new FeedEntity();
		$feed2->setStatus(FeedEntity::STATUS_IMPORTING);
		$feed2->setSourceUrl("http://updatetFeed.com");
		$feed2->setSkipError(true);
		$feed2->setForceUpdate(false);
		$feed2->setUrl($url2);
		$this->getEntityManager()->persist($feed2);
		$this->getEntityManager()->flush();

		$this->getOfferService()->importOffers($feed2);

		/**
		 * @var OfferEntity $offer1
		 */
		$offer1 = $this->getOfferRepository()->findOneBy(['offerId' => 40408]);
		$this->assertEquals(40408, $offer1->getOfferId());
		$this->assertEquals("Buy 2: Select TRISCUIT Crackers", $offer1->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/6840/67561_1535141624.jpg", $offer1->getImageUrl());
		$this->assertEquals(1.0, $offer1->getCashBack());

		/**
		 * @var OfferEntity $offer2
		 */
		$offer2 = $this->getOfferRepository()->findOneBy(['offerId' => 39271]);
		$this->assertEquals(39271, $offer2->getOfferId());
		$this->assertEquals("Tide Liquid Detergent", $offer2->getName());
		$this->assertEquals("https://d3bx4ud3idzsqf.cloudfront.net/public/production/4902/56910_1527084051.jpg", $offer2->getImageUrl());
		$this->assertEquals(2.0, $offer2->getCashBack());

		$this->assertEquals(3, count($this->getOfferRepository()->findAll()));
	}


	public function testProcessOffersWithSmallFeed() {
		$this->cleanData();

		//create feed, first import
		$url = '/tmp/' . uniqid();
		file_put_contents($url, self::OFFERS_JSON);
		$feed = new FeedEntity();
		$feed->setSourceUrl("http://createFeed.com");
		$feed->setSkipError(true);
		$feed->setForceUpdate(true);
		$feed->setUrl($url);
		$feed->setLarge(false);
		$this->getEntityManager()->persist($feed);
		$this->getEntityManager()->flush();

		$this->getOfferService()->processOffers($feed->getId());

		$this->assertNotNull($feed->getProcessStartedAt());
		$this->assertNotNull($feed->getProcessCompletedAt());

		$this->assertEquals(2, count($this->getOfferRepository()->findAll()));
	}

	public function testProcessOffersWithLargeFeed() {
		$this->cleanData();

		//create feed, first import
		$url = '/tmp/' . uniqid();
		file_put_contents($url, self::OFFERS_JSON);
		$feed = new FeedEntity();
		$feed->setSourceUrl("http://createFeed.com");
		$feed->setSkipError(true);
		$feed->setForceUpdate(true);
		$feed->setUrl($url);
		$feed->setLarge(true);
		$this->getEntityManager()->persist($feed);
		$this->getEntityManager()->flush();

		$this->getOfferService()->setMessageBus($this->getMockMessageBus());
		$this->getOfferService()->processOffers($feed->getId());

		$this->assertEquals(0, count($this->getOfferRepository()->findAll()));

		$this->assertNotNull($feed->getProcessStartedAt());
		$this->assertNull($feed->getProcessCompletedAt());

		$message = new ImportOffers($feed->getId());
		$this->assertEquals($message, $this->getMockMessageBus()->getMemory()[0]);
		$this->getMockMessageBus()->resetMemory();
	}
}