<?php


namespace App\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AbstractWebTestCase extends WebTestCase
{
	protected function cleanData($doctrine) {
		$doctrine->getConnection()->exec('set foreign_key_checks=0');
		$doctrine->getConnection()->exec('delete from feed');
		$doctrine->getConnection()->exec('delete from offer');
		$doctrine->getConnection()->exec('set foreign_key_checks=1');
	}
}