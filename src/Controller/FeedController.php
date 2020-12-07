<?php


namespace App\Controller;


use App\Entity\FeedEntity;
use App\Exception\DownloadFailedException;
use App\Form\FeedType;
use App\Repository\FeedRepository;
use App\Service\FeedService;
use App\Service\OfferService;
use App\Service\WebSocketService;
use App\Utils\Config;
use Knp\Component\Pager\PaginatorInterface;
use React\Socket\ConnectionInterface;
use React\Socket\Server;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @Route("/feed")
 */
class FeedController extends BaseController
{
	/**
	 * @var FeedService
	 */
	private $feedService;

	/**
	 * @var OfferService
	 */
	private $offerService;

	/**
	 * @var PaginatorInterface
	 */
	private $paginator;

	/**
	 * FeedController constructor.
	 * @param PaginatorInterface $paginator
	 * @param FeedService $feedService
	 * @param OfferService $offerService
	 */
	public function __construct(PaginatorInterface $paginator, FeedService $feedService, OfferService $offerService) {
		$this->paginator = $paginator;
		$this->feedService = $feedService;
		$this->offerService = $offerService;
	}

	/**
	 * @Route("/", name="feed_index", methods="GET")
	 * @param Request $request
	 * @param FeedRepository $feedRepository
	 * @return Response
	 */
	public function indexAction(Request $request, FeedRepository $feedRepository) {
		$data = [
			'title' => 'Feeds',
			'form' => [
				'page' => $request->query->getInt('page', 1),
			]
		];
		$feedQuery = $feedRepository->findAllFeedsQuery();
		$data['pagination'] = $this->paginator->paginate($feedQuery, $data['form']['page'], Config::PAGE_LIMIT);

		return $this->render('feed/index.html.twig', $data);
	}

	/**
	 * @Route("/new", name="feed_new", methods="GET|POST")
	 * @param Request $request
	 * @return Response
	 */
	public function newAction(Request $request) {
		$feed = new FeedEntity();
		$form = $this->createForm(FeedType::class, $feed);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$this->feedService->addFeed($feed);
			return $this->redirectToRoute('feed_download', ['id' => $feed->getId()]);
		}

		return $this->render('feed/new.html.twig', [
			'title' => 'Import Feed',
			'form' => $form->createView()
		]);
	}

	/**
	 * @Route("/download/{id}", requirements={"id"="\d+"}, name="feed_download", methods="GET")
	 * @param int $id
	 * @param FeedRepository $feedRepository
	 * @return Response
	 */
	public function downloadAction($id, FeedRepository $feedRepository) {
		try {
			$feed = $this->feedService->processFeed($id);
		} catch (DownloadFailedException $e) {
			$feed = $feedRepository->find($id);
		}

		return $this->render('feed/download.html.twig', [
			'title' => 'Confirm Feed',
			'feed' => $feed
		]);
	}

	/**
	 * @Route("/import/{id}", requirements={"id"="\d+"}, name="feed_import", methods="POST")
	 * @param int $id
	 * @return Response
	 * @throws \App\Exception\ImportOfferException
	 */
	public function importAction($id) {
		//check if feed is already downloaded
		//check file chunksum and valid
		$this->offerService->processOffers($id);
		return $this->redirectToRoute('offer_index', ['feedId' => $id]);
	}

	/**
	 * For testing purpose
	 * @Route("/delete", name="feed_delete", methods="GET")
	 * @return Response
	 */
	public function deleteAction() {
		if  ($_SERVER['APP_ENV'] != 'dev') {
			return new Response('Not Authorized', 401);
		}
		$this->getDoctrine()->getConnection()->exec('delete from offer');
		$this->getDoctrine()->getConnection()->exec('delete from feed');
		return $this->redirectToRoute('feed_index', []);
	}

}