<?php


namespace App\Utils;


class Url
{
	private $url;
	private $parts;

	public function __construct($url) {
		$this->url = $url;
		$this->parts = parse_url($url);
	}

	public function getHost() {
		return $this->parts['host'];
	}

	/**
	 * handle subfolder limit 31998
	 * @return string
	 */
	public function getFileName() {
		$md5 = md5($this->url);
		$folder1 = substr($md5, 0, 8);
		$folder2 = substr($md5, 8, 8);
		$folder3 = substr($md5, 16, 8);
		$folder4 = substr($md5, 24, 8);
		return $folder1 .DIRECTORY_SEPARATOR . $folder2 . DIRECTORY_SEPARATOR
			. $folder3 . DIRECTORY_SEPARATOR .$folder4 . DIRECTORY_SEPARATOR . $md5 . '.json';
	}
}