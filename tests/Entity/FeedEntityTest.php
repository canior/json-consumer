<?php


namespace App\Tests\Entity;


use App\Entity\FeedEntity;
use App\Tests\AbstractTestCase;

class FeedEntityTest extends AbstractTestCase
{
	public function testConstruct() {
		$feed = new FeedEntity();
		$this->assertNotNull($feed->getCreatedAt());
		$this->assertNotNull($feed->isSkipError());
		$this->assertNotNull($feed->isForceUpdate());
	}
}