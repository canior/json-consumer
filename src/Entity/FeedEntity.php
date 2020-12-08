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
	 * @var string
	 * @ORM\Column(type="string", nullable=false)
	 */
	private $status;


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


	const STATUS_DOWNLOADING = 'Downloading';
	const STATUS_DOWNLOADED = 'Downloaded';
	const STATUS_DOWNLOADED_ERROR = 'Download Error';
	const STATUS_IMPORTING = 'Importing';
	const STATUS_IMPORTED = 'Imported';
	const STATUS_IMPORTED_ERROR = 'Import Error';

	public function __construct() {
		$this->setCreatedAt();
		$this->skipError = true;
		$this->forceUpdate = true;
		$this->offers = new ArrayCollection();
		$this->setStatus(self::STATUS_DOWNLOADING);
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
	 * @return string
	 */
	public function getSkipErrorText() {
		return $this->isSkipError() ? 'Yes' : 'No';
	}

	/**
	 * @return string
	 */
	public function getForceUpdateText() {
		return $this->isForceUpdate() ? 'Yes' : 'No';
	}

	/**
	 * @return string
	 */
	public function getStatus(): string {
		return $this->status;
	}

	/**
	 * @param string $status
	 */
	public function setStatus(string $status): void {
		$this->status = $status;
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

	public function isDownloading() {
		return self::STATUS_DOWNLOADING == $this->status;
	}

	public function isDownloaded() {
		return self::STATUS_DOWNLOADED == $this->status;
	}

	public function isDownloadedError() {
		return self::STATUS_DOWNLOADED_ERROR == $this->status;
	}

	public function isImporting() {
		return self::STATUS_IMPORTING == $this->status;
	}

	public function isImported() {
		return self::STATUS_IMPORTED == $this->status;
	}

	public function isImportedError() {
		return self::STATUS_IMPORTED_ERROR == $this->status;
	}
}