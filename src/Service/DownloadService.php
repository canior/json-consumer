<?php


namespace App\Service;


use App\Exception\DownloadFailedException;
use App\Utils\Config;
use App\Utils\Url;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


class DownloadService
{

	/**
	 * @var string
	 */
	private $localStorageFolder;

	/**
	 * @var HttpClientInterface
	 */
	private $httpClient;

	/**
	 * DownloadService constructor.
	 * @param ParameterBagInterface $params
	 * @param HttpClientInterface $httpClient
	 */
	public function __construct(ParameterBagInterface $params, HttpClientInterface $httpClient) {
		$this->localStorageFolder = $params->get('file_directory');
		$this->httpClient = $httpClient;
	}

	/**
	 * @param $sourceUrl
	 * @return bool
	 * @throws DownloadFailedException
	 */
	public function isLargeFile($sourceUrl) {
		try {
			$response = $this->httpClient->request('GET', $sourceUrl);
			$headers = $response->getHeaders();
			return $headers['content-length'][0] > Config::REALTIME_DOWNLOAD_LIMIT;
		} catch (\Throwable $e) {
			throw new DownloadFailedException("Can't download url " . $sourceUrl);
		}
	}

	/**
	 * @param $sourceUrl
	 * @return string
	 * @throws TransportExceptionInterface
	 */
	public function downloadFile($sourceUrl) {
		$url = new Url($sourceUrl);
		$localFile = $this->localStorageFolder . $url->getFileName();
		$dirname = dirname($localFile);
		if (!is_dir($dirname)) {
			mkdir($dirname, 0755, true);
		}

		$response = $this->httpClient->request('GET', $sourceUrl);

		$fileHandler = fopen($localFile, 'w');
		foreach ($this->httpClient->stream($response) as $chunk) {
			fwrite($fileHandler, $chunk->getContent());
		}
		return $localFile;
	}

}