<?php


namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Traits\CreatedTrait;
use App\Entity\Traits\IdTrait;


/**
 * @ORM\Entity(repositoryClass="App\Repository\FeedRepository")
 * @ORM\Table(name="feed")
 */
class FeedEntity
{
	use IdTrait;
	use CreatedTrait;

	/**
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $sourceUrl;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $url;

	/**
	 * @var bool
	 * @ORM\Column(name="skip_error", type="boolean", nullable=false)
	 */
	private $skipError;

	/**
	 * @var bool
	 * @ORM\Column(name="force_update", type="boolean", nullable=false)
	 */
	private $forceUpdate;

	/**
	 * @var bool|null
	 * @ORM\Column(name="valid", type="boolean", nullable=true)
	 */
	private $valid;

	/**
	 * @var bool|null
	 * @ORM\Column(name="large", type="boolean", nullable=true)
	 */
	private $large;

	/**
	 * @var string|null
	 * @ORM\Column(type="string", nullable=true)
	 */
	private $chunkSum;

	/**
	 * @var int|null
	 * @ORM\Column(name="process_started_at", type="integer", nullable=true)
	 */
	private $processStartedAt;

	/**
	 * @var int|null
	 * @ORM\Column(name="process_completed_at", type="integer", nullable=true)
	 */
	private $processCompletedAt;

	/**
	 * @var OfferEntity[]
	 * @ORM\OneToMany(targetEntity="OfferEntity", mappedBy="updateFeed", fetch="EXTRA_LAZY")
	 */
	private $offers;



	public function __construct() {
		$this->setCreatedAt();
		$this->offers = new ArrayCollection();
	}

	/**
	 * @return string
	 */
	public function getSourceUrl(): string {
		return $this->sourceUrl;
	}

	/**
	 * @param string $sourceUrl
	 */
	public function setSourceUrl(string $sourceUrl): void {
		$this->sourceUrl = $sourceUrl;
	}

	/**
	 * @return string|null
	 */
	public function getUrl(): ?string {
		return $this->url;
	}

	/**
	 * @param string|null $url
	 */
	public function setUrl(?string $url): void {
		$this->url = $url;
	}

	/**
	 * @return bool
	 */
	public function isSkipError(): bool {
		return $this->skipError;
	}

	/**
	 * @param bool $skipError
	 */
	public function setSkipError(bool $skipError): void {
		$this->skipError = $skipError;
	}

	/**
	 * @return bool
	 */
	public function isForceUpdate(): bool {
		return $this->forceUpdate;
	}

	/**
	 * @param bool $forceUpdate
	 */
	public function setForceUpdate(bool $forceUpdate): void {
		$this->forceUpdate = $forceUpdate;
	}

	/**
	 * @return bool|null
	 */
	public function getValid(): ?bool {
		return $this->valid;
	}

	/**
	 * @param bool|null $valid
	 */
	public function setValid(?bool $valid): void {
		$this->valid = $valid;
	}

	/**
	 * @return bool|null
	 */
	public function getLarge(): ?bool {
		return $this->large;
	}

	/**
	 * @param bool|null $large
	 */
	public function setLarge(?bool $large): void {
		$this->large = $large;
	}

	/**
	 * @return string|null
	 */
	public function getChunkSum(): ?string {
		return $this->chunkSum;
	}

	/**
	 * @param string|null $chunkSum
	 */
	public function setChunkSum(?string $chunkSum): void {
		$this->chunkSum = $chunkSum;
	}

	/**
	 * @return int|null
	 */
	public function getProcessStartedAt(): ?int {
		return $this->processStartedAt;
	}

	/**
	 * @param int|null $processStartedAt
	 */
	public function setProcessStartedAt(?int $processStartedAt): void {
		$this->processStartedAt = $processStartedAt;
	}

	/**
	 * @return int|null
	 */
	public function getProcessCompletedAt(): ?int {
		return $this->processCompletedAt;
	}

	/**
	 * @param int|null $processCompletedAt
	 */
	public function setProcessCompletedAt(?int $processCompletedAt): void {
		$this->processCompletedAt = $processCompletedAt;
	}

	/**
	 * @return bool
	 */
	public function isDownloaded() {
		return !is_null($this->url);
	}

	public function getSkipErrorText() {
		return $this->isSkipError() ? 'Yes' : 'No';
	}

	public function getForceUpdateText() {
		return $this->isForceUpdate() ? 'Yes' : 'No';
	}

	public function isReadyToImport() {
		return !is_null($this->getUrl());
	}

	public function getStatusText() {
		if ($this->getValid() === false) {
			return 'Error Feed, Stop Processing';
		}

		if (!is_null($this->getProcessStartedAt()) && is_null($this->getProcessCompletedAt())) {
			return 'Importing ...';
		}

		if (!is_null($this->getProcessStartedAt()) && !is_null($this->getProcessCompletedAt())) {
			return 'Import Completed';
		}

		if (!$this->isReadyToImport()) {
			return 'Downloading ..';
		} else {
			return 'Downloaded and ready to import';
		}
	}

	/**
	 * @return OfferEntity[]
	 */
	public function getOffers() {
		return $this->offers;
	}

	/**
	 * @param OfferEntity[] $offers
	 */
	public function setOffers($offers): void {
		$this->offers = $offers;
	}
}