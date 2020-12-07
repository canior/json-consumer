<?php


namespace App\Repository;


use App\Entity\FeedEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FeedEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method FeedEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method FeedEntity[]    findAll()
 * @method FeedEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FeedRepository extends ServiceEntityRepository
{
	public function __construct(ManagerRegistry $registry) {
		parent::__construct($registry, FeedEntity::class);
	}

	/**
	 * @return QueryBuilder
	 */
	public function findAllFeedsQuery() {
		return $this->createQueryBuilder('f');
	}
}