<?php


namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\Traits\CreatedTrait;
use App\Entity\Traits\IdTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OfferRepository")
 * @ORM\Table(name="offer")
 */
class OfferEntity
{
	use IdTrait;
	use CreatedTrait;
	use UpdatedAtTrait;

	/**
	 * @var int
	 * @ORM\Column(type="integer", nullable=false)
	 */
	private $offerId;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $name;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $imageUrl;

	/**
	 * @var float
	 * @ORM\Column(type="float", scale=2, nullable=false)
	 */
	private $cashBack;

	/**
	 * @var FeedEntity
	 * @ORM\ManyToOne(targetEntity="FeedEntity", inversedBy="offers", cascade={"persist"})
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(name="update_feed_id", referencedColumnName="id")
	 * })
	 */
	private $updateFeed;


	public function  __construct() {
		$this->setCreatedAt();
		$this->setUpdatedAt();
	}

	/**
	 * @return int
	 */
	public function getOfferId(): int {
		return $this->offerId;
	}

	/**
	 * @param int $offerId
	 */
	public function setOfferId(int $offerId): void {
		$this->offerId = $offerId;
	}

	/**
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * @param string $name
	 */
	public function setName(string $name): void {
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getImageUrl(): ?string {
		return $this->imageUrl;
	}

	/**
	 * @param string|null $imageUrl
	 */
	public function setImageUrl(?string $imageUrl): void {
		$this->imageUrl = $imageUrl;
	}

	/**
	 * @return float
	 */
	public function getCashBack(): float {
		return $this->cashBack;
	}

	/**
	 * @param float $cashBack
	 */
	public function setCashBack(float $cashBack): void {
		$this->cashBack = $cashBack;
	}

	/**
	 * @return FeedEntity
	 */
	public function getUpdateFeed(): FeedEntity {
		return $this->updateFeed;
	}

	/**
	 * @param FeedEntity $updateFeed
	 */
	public function setUpdateFeed(FeedEntity $updateFeed): void {
		$this->updateFeed = $updateFeed;
	}
}