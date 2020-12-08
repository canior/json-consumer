<?php


namespace App\Tests\Service;


use App\Entity\FeedEntity;
use App\Exception\DownloadFailedException;
use App\Message\DownloadFile;
use App\Tests\AbstractTestCase;
use App\Tests\MockHttpClient;

class FeedServiceTest extends AbstractTestCase
{
	public function testProcessLargeFeed() {
		$sourceUrl = MockHttpClient::LARGE_FILE_URL;

		$feed = new FeedEntity();
		$feed->setSourceUrl($sourceUrl);
		$this->getEntityManager()->persist($feed);
		$this->getEntityManager()->flush();

		$feedId = $feed->getId();
		$feedService = $this->getMockFeedService();
		$feedService->processFeed($feedId);

		$feedFromDB = $this->getFeedRepository()->find($feedId);
		$this->assertNull($feedFromDB->getValid());

		$message = new DownloadFile($feedId, $sourceUrl);
		$this->assertEquals($message, $this->getMockMessageBus()->getMemory()[0]);
		$this->getMockMessageBus()->resetMemory();
	}

	public function testProcessSimpleFeed() {
		$sourceUrl = MockHttpClient::SMALL_FILE_URL;

		$feed = new FeedEntity();
		$feed->setSourceUrl($sourceUrl);
		$this->getEntityManager()->persist($feed);
		$this->getEntityManager()->flush();

		$feedId = $feed->getId();
		$feedService = $this->getMockFeedService();
		$feedService->processFeed($feedId);

		$feedFromDB = $this->getFeedRepository()->find($feedId);
		$this->assertTrue($feedFromDB->getValid());
	}

	public function testProcessInvalidFeed() {
		$sourceUrl = MockHttpClient::INVALID_URL;

		$feed = new FeedEntity();
		$feed->setSourceUrl($sourceUrl);
		$this->getEntityManager()->persist($feed);
		$this->getEntityManager()->flush();
		$feedId = $feed->getId();

		try {
			$feedService = $this->getMockFeedService();
			$feedService->processFeed($feedId);
		} catch (\Throwable $e) {
			$this->assertTrue($e instanceof DownloadFailedException);

			$feedFromDB = $this->getFeedRepository()->find($feedId);
			$this->getEntityManager()->refresh($feedFromDB);
			$this->assertFalse($feedFromDB->getValid());
			return;
		}

		$this->assertTrue(false);
	}

	public function testCompleteDownload() {
		$sourceUrl = "http://test.com";
		$url = "/tmp";

		$feed = new FeedEntity();
		$feed->setSourceUrl($sourceUrl);
		$this->assertNull($feed->getValid());
		$this->assertTrue($feed->isSkipError());

		$this->getFeedService()->completeDownload($feed, $url, true, false);

		/**
		 * @var FeedEntity $feedFromDB
		 */
		$feedFromDB = $this->getFeedRepository()->find($feed->getId());

		$this->assertEquals($feed, $feedFromDB);
		$this->assertTrue($feedFromDB->isSkipError());
		$this->assertTrue($feedFromDB->getValid());
		$this->assertEquals($url, $feedFromDB->getUrl());

	}

	public function testSaveOrUpdateFeed() {
		$sourceUrl = "http://test.com";
		$feed = new FeedEntity();
		$feed->setSourceUrl($sourceUrl);
		$this->getFeedService()->saveOrUpdateFeed($feed);

		$feedFromDB = $this->getFeedRepository()->find($feed->getId());

		$this->assertEquals($feed, $feedFromDB);
	}
}