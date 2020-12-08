<?php


namespace App\Controller;

use App\Repository\FeedRepository;
use App\Repository\OfferRepository;
use App\Service\OfferService;
use App\Utils\Config;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
	 * @var ParameterBagInterface
	 */
	private $parameterBag;

	/**
	 * OfferController constructor.
	 * @param PaginatorInterface $paginator
	 * @param ParameterBagInterface $parameterBag
	 * @param OfferService $offerService
	 */
	public function __construct(PaginatorInterface $paginator, ParameterBagInterface $parameterBag, OfferService $offerService) {
		$this->paginator = $paginator;
		$this->offerService = $offerService;
		$this->parameterBag = $parameterBag;
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
			'wsHost' => $this->parameterBag->get('ws_host'),
			'wsPort' => $this->parameterBag->get('ws_port'),
			'form' => [
				'search' => [
					'sourceUrl' => $request->query->get('sourceUrl', ''),
					'name' => $request->query->get('offerName', ''),
					'feedId' => $request->query->get('feedId', ''),
				],
				'orderBy' => [
					'field' => $request->get('orderByField', 'id'),
					'order' => $request->get('orderByOrder', 'desc'),
				],
				'page' => $request->query->getInt('page', 1),
			]
		];

		$feedQuery = $offerRepository->findOfferQuery($data['form']['search'], $data['form']['orderBy']);
		$data['pagination'] = $this->paginator->paginate($feedQuery, $data['form']['page'], Config::PAGE_LIMIT);
		return $this->render('offer/index.html.twig', $data);
	}
}