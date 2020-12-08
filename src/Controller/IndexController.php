<?php


namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IndexController extends BaseController
{
	/**
	 * @Route("/", name="index", methods="GET")
	 * @return Response
	 */
	public function indexAction() {
		return $this->render('index.html.twig');
	}
}