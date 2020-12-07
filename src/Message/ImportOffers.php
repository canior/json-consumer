<?php


namespace App\Message;


class ImportOffers
{
	/**
	 * @var int
	 */
	private $feedId;

	public function __construct($feedId) {
		$this->feedId = $feedId;
	}

	/**
	 * @return int
	 */
	public function getFeedId(): int {
		return $this->feedId;
	}

}