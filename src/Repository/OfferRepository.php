<?php


namespace App\Repository;


use App\Entity\OfferEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\LockMode;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OfferEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method OfferEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method OfferEntity[]    findAll()
 * @method OfferEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OfferRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, OfferEntity::class);
	}


	/**
	 * @param $offerId
	 * @return OfferEntity
	 * @throws NonUniqueResultException
	 */
	public function findByOfferId($offerId) {
		return $this->getEntityManager()
			->createQuery('SELECT o FROM App\Entity\OfferEntity o WHERE o.offerId = :offerId')
			->setParameter('offerId', $offerId)
			->getOneOrNullResult();
	}

	/**
	 * @param array $search
	 * @param array $orderBy
	 * @return QueryBuilder
	 */
	public function findOfferQuery($search = [], $orderBy = []) {
		$query = $this->createQueryBuilder('o');

		if (array_key_exists('name', $search)) {
			$query->andWhere('o.name like :name')
				->setParameter('name', '%' . $search['name'] . '%');
		}

		if (array_key_exists('sourceUrl', $search)) {
			$query->join('o.updateFeed', 'f')
				->andWhere('f.sourceUrl like :sourceUrl')
				->setParameter('sourceUrl', '%'. $search['sourceUrl'] . '%');
		}

		if (array_key_exists('feedId', $search)) {
			if (!empty($search['feedId'])) {
				$query->join('o.updateFeed', 'f2')
					->andWhere('f2.id = :feedId')
					->setParameter('feedId', $search['feedId']);
			}
		}

		$fields = $this->getEntityManager()->getClassMetadata(OfferEntity::class)->getFieldNames();

		if (array_key_exists('field', $orderBy) && array_key_exists('order', $orderBy)) {
			if (in_array($orderBy['field'], $fields) && in_array($orderBy['order'], ['asc', 'desc'])) {
				$query->orderBy('o.' . $orderBy['field'], $orderBy['order']);
			}
		}


		return $query;
	}
}