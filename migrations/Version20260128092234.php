<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128092234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise_wp5_event ADD event_name_english VARCHAR(255) DEFAULT NULL, ADD event_name_original VARCHAR(255) DEFAULT NULL, ADD edih_co_organiser VARCHAR(255) DEFAULT NULL, ADD co_organiser VARCHAR(255) DEFAULT NULL, ADD start_date DATE DEFAULT NULL, ADD end_date DATE DEFAULT NULL, ADD attendees_number INT DEFAULT NULL, ADD delivery_mode VARCHAR(255) DEFAULT NULL, ADD website_url VARCHAR(500) DEFAULT NULL, ADD main_technologies LONGTEXT DEFAULT NULL, ADD service_category VARCHAR(255) DEFAULT NULL, ADD main_sectors LONGTEXT DEFAULT NULL, ADD event_description LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise_wp5_event DROP event_name_english, DROP event_name_original, DROP edih_co_organiser, DROP co_organiser, DROP start_date, DROP end_date, DROP attendees_number, DROP delivery_mode, DROP website_url, DROP main_technologies, DROP service_category, DROP main_sectors, DROP event_description');
    }
}
