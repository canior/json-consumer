<?php


namespace App\Tests\Service;


use App\Exception\DownloadFailedException;
use App\Tests\AbstractTestCase;
use App\Tests\MockHttpClient;
use Symfony\Component\HttpClient\HttpClient;

class DownloadServiceTest extends AbstractTestCase
{
	public function testIsLargeFile() {
		$this->assertTrue($this->getMockDownloadService()->isLargeFile(MockHttpClient::LARGE_FILE_URL));
		$this->assertFalse($this->getMockDownloadService()->isLargeFile(MockHttpClient::SMALL_FILE_URL));
		try {
			$this->getMockDownloadService()->isLargeFile(MockHttpClient::INVALID_URL);
		} catch (\Throwable $e) {
			$this->assertTrue($e instanceof DownloadFailedException);
			return;
		}
		$this->assertTrue(false);
	}

	public function testDownloadFile() {
		$sourceUrl = 'https://www.checkout51.com/';
		$localPath = $this->getDownloadService()->downloadFile($sourceUrl);
		$httpClient = HttpClient::create();
		$response = $httpClient->request('GET', $sourceUrl);
		$this->assertEquals($response->getContent(), file_get_contents($localPath));
	}

}