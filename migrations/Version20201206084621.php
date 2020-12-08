<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201206084621 extends AbstractMigration
{
	public function getDescription(): string {
		return 'Create Feed and Offer tables';
	}

	public function up(Schema $schema): void {
		$this->addSql('CREATE TABLE `feed` (
								  `id` int(10) NOT NULL AUTO_INCREMENT,
								  `status` varchar(255) NOT NULL,
								  `source_url` varchar(1000) NOT NULL,
								  `url` varchar(1000),
								  `skip_error` tinyint(1) NOT NULL,
								  `force_update` tinyint(1) NOT NULL,
								  `valid` tinyint(1),
								  `large` tinyint(1),
								  `created_at` int(10) NOT NULL,
								  `process_started_at` int(10),
								  `process_completed_at` int (10),
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8');
		$this->addSql('CREATE TABLE `offer` (
								  `id` int(10) NOT NULL AUTO_INCREMENT,
								  `offer_id` int(10) NOT NULL,
								  `name` varchar(255) NOT NULL,
								  `image_url` varchar (1000),
								  `cash_back` decimal (10, 2) NOT NULL,
								  `update_feed_id` int(10) NOT NULL,
								  `updated_at` int(10) NOT NULL,
								  `created_at` int(10) NOT NULL,
								  PRIMARY KEY (id),
								  FOREIGN KEY (update_feed_id) REFERENCES feed(id)
								) ENGINE=InnoDB DEFAULT CHARSET=utf8');
	}

	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs

	}
}
