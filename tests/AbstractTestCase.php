<?php


namespace App\Tests;


use App\Service\DownloadService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


class AbstractTestCase extends KernelTestCase
{

	protected function setUp() {
		self::bootKernel();
	}

	/**
	 * @return DownloadService
	 */
	protected function getDownloadService() {
		return self::$container->get('App\Service\DownloadService');
	}
}