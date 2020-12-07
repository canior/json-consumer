<?php


namespace App\Entity\Traits;


use App\Utils\Config;

Trait CreatedTrait
{
	/**
	 * @var int
	 * @ORM\Column(name="created_at", type="integer", nullable=false)
	 */
	private $createdAt;

	/**
	 * Get createdAt
	 *
	 * @param bool $formatted
	 * @return int
	 */
	public function getCreatedAt($formatted = true)
	{
		if ($formatted) {
			return $this->createdAt ? date(Config::DATETIME_FORMAT, $this->createdAt) : null;
		}
		return $this->createdAt;
	}

	/**
	 * @return string
	 */
	public function getCreatedAtFormatted() {
		return $this->getCreatedAt(true);
	}

	/**
	 * @return string
	 */
	public function getCreatedAtDateFormatted() {
		return date(Config::DATE_FORMAT, $this->createdAt);
	}

	/**
	 * Set createdAt
	 *
	 * @param int $createdAt | null
	 *
	 * @return $this
	 */
	public function setCreatedAt($createdAt = null)
	{
		if ($createdAt == null) {
			$this->createdAt = time();
		} else {
			$this->createdAt = $createdAt;
		}

		return $this;
	}


}