<?php


namespace App\Service;


use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class WebSocketService
{
	private $ws;

	/**
	 * DownloadService constructor.
	 * @param ParameterBagInterface $params
	 */
	public function __construct(ParameterBagInterface $params) {
		$this->ws = $params->get('ws_host') . ':' . $params->get('ws_port') ;
	}

	/**
	 * @param $messageArray
	 */
	public function sendMessage($messageArray) {
		try {
			\Ratchet\Client\connect('ws://localhost:3001')->then(function ($conn) use ($messageArray) {
				$conn->send(json_encode($messageArray));
				$conn->close();
			}, function (\Exception $e) {
				var_dump($e);exit;
			});
		} catch (\Exception $e) {
			var_dump($e);exit;
		}
	}
}