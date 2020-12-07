<?php


namespace App\Controller;

use App\Repository\FeedRepository;
use App\Repository\OfferRepository;
use App\Service\OfferService;
use App\Utils\Config;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/offer")
 */
class OfferController extends BaseController
{
	/**
	 * @var OfferService
	 */
	private $offerService;

	/**
	 * @var PaginatorInterface
	 */
	private $paginator;

	/**
	 * OfferController constructor.
	 * @param PaginatorInterface $paginator
	 * @param OfferService $offerService
	 */
	public function __construct(PaginatorInterface $paginator, OfferService $offerService) {
		$this->paginator = $paginator;
		$this->offerService = $offerService;
	}

	/**
	 * @Route("/", name="offer_index", methods="GET")
	 * @param Request $request
	 * @param OfferRepository $offerRepository
	 * @return Response
	 */
	public function indexAction(Request $request, OfferRepository $offerRepository) {
		$data = [
			'title' => 'Offers',
			'form' => [
				'search' => [
					'sourceUrl' => $request->query->get('sourceUrl', ''),
					'name' => $request->query->get('offerName', ''),
					'feedId' => $request->query->get('feedId', ''),
				],
				'orderBy' => [],
				'page' => $request->query->getInt('page', 1),
			]
		];

		$data['form']['orderBy']['id'] = $request->get('orderById', '');
		$data['form']['orderBy']['name'] = $request->get('orderByName', '');
		$data['form']['orderBy']['cashBack'] = $request->get('orderByCashBack', '');

		$feedQuery = $offerRepository->findOfferQuery($data['form']['search'], $data['form']['orderBy']);
		$data['pagination'] = $this->paginator->paginate($feedQuery, $data['form']['page'], Config::PAGE_LIMIT);
		return $this->render('offer/index.html.twig', $data);
	}
}