<?php


namespace App\Tests\Entity;


use App\Entity\OfferEntity;
use App\Tests\AbstractTestCase;

class OfferEntityTest extends AbstractTestCase
{
	public function testConstruct() {
		$offer = new OfferEntity();
		$this->assertNotNull($offer->getCreatedAt());
		$this->assertNotNull($offer->getUpdatedAt());
	}
}