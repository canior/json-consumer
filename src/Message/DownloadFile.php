<?php


namespace App\Message;


class DownloadFile
{
	/**
	 * @var int
	 */
	private $feedId;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * DownloadFile constructor.
	 * @param $feedId
	 * @param $url
	 */
	public function __construct($feedId, $url) {
		$this->feedId = $feedId;
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string {
		return $this->url;
	}

	/**
	 * @return int
	 */
	public function getFeedId(): int {
		return $this->feedId;
	}

}