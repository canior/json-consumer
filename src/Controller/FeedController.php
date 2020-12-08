<?php


namespace App\Controller;


use App\Entity\FeedEntity;
use App\Exception\DownloadFailedException;
use App\Exception\ImportOfferException;
use App\Form\FeedType;
use App\Repository\FeedRepository;
use App\Service\FeedService;
use App\Service\OfferService;
use App\Utils\Config;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
	 * @var ParameterBagInterface
	 */
	private $parameterBag;

	/**
	 * FeedController constructor.
	 * @param PaginatorInterface $paginator
	 * @param FeedService $feedService
	 * @param OfferService $offerService
	 */
	public function __construct(PaginatorInterface $paginator, FeedService $feedService, OfferService $offerService, ParameterBagInterface $parameterBag) {
		$this->paginator = $paginator;
		$this->feedService = $feedService;
		$this->offerService = $offerService;
		$this->parameterBag = $parameterBag;
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
			$this->feedService->saveOrUpdateFeed($feed);
			return $this->redirectToRoute('feed_download', ['id' => $feed->getId()]);
		}

		return $this->render('feed/new.html.twig', [
			'title' => 'Import Feed',
			'form' => $form->createView(),
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
			'title' => 'Download Feed',
			'wsHost' => $this->parameterBag->get('ws_host'),
			'wsPort' => $this->parameterBag->get('ws_port'),
			'feed' => $feed
		]);
	}

	/**
	 * @Route("/import/{id}", requirements={"id"="\d+"}, name="feed_import", methods="POST")
	 * @param int $id
	 * @return Response
	 */
	public function importAction($id) {
		//check if feed is already downloaded
		//check file chunksum and valid
		try {
			$this->offerService->processOffers($id);
		} catch (ImportOfferException $e) {
			return $this->redirectToRoute('feed_download', ['id' => $id]);
		}

		return $this->redirectToRoute('offer_index', ['feedId' => $id]);
	}

	/**
	 * For testing purpose
	 * @Route("/delete", name="feed_delete", methods="GET")
	 * @return Response
	 */
	public function deleteAction() {
		$this->getDoctrine()->getConnection()->exec('delete from offer');
		$this->getDoctrine()->getConnection()->exec('delete from feed');
		return $this->redirectToRoute('feed_index', []);
	}

}