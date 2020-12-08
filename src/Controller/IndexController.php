<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IndexController
{
	/**
	 * @Route("/", name="index", methods="GET")
	 * @return Response
	 */
	public function indexAction() {
		return new Response("thank you");
	}
}