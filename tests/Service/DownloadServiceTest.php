<?php


namespace App\Tests\Service;


use App\Tests\AbstractTestCase;

class DownloadServiceTest extends AbstractTestCase
{
	public function testIsLargeFile() {
		$sourceUrl = "test.com";
		$this->getDownloadService()->isLargeFile();
	}
}