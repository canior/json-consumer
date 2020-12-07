<?php


namespace App\Tests\Repository;


use App\Entity\FeedEntity;
use App\Tests\AbstractTestCase;

class FeedRepositoryTest extends AbstractTestCase
{
	public function testFindAllFeedsQuery() {
		$this->cleanData();

		$totalFeeds = random_int(1, 100);
		for($i = 0; $i < $totalFeeds; $i++) {
			$feed = new FeedEntity();
			$feed->setSourceUrl('http://url' . $i);
			$this->getEntityManager()->persist($feed);
		}
		$this->getEntityManager()->flush();

		$query = $this->getFeedRepository()->findAllFeedsQuery();
		$feeds = $query->getQuery()->getResult();

		$this->assertEquals($totalFeeds, count($feeds));
	}
}