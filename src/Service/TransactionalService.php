<?php


namespace App\Service;


use App\Utils\Config;
use Doctrine\DBAL\Exception\DeadlockException;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

class TransactionalService
{
	/**
	 * @var EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @var ManagerRegistry
	 */
	private $managerRegistry;

	public function __construct(EntityManagerInterface $entityManager, ManagerRegistry $managerRegistry) {
		$this->entityManager = $entityManager;
		$this->managerRegistry = $managerRegistry;
	}

	/**
	 * @param callable $callback
	 * @throws \Exception
	 */
	public function transactional(callable $callback) {
		$retries = 0;
		do {
			$this->entityManager->beginTransaction();
			try {
				$callback();
				$this->entityManager->flush();
				$this->entityManager->commit();
				return;
			} catch (RetryableException $e) {
				$this->entityManager->rollback();
				$this->entityManager->close();;
				$this->managerRegistry->resetManager();
				$retries++;
			}
		} while ($retries < Config::TRANSACTION_RETRIED_LIMIT);

	}
}