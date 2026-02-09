<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260128092815 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise_wp5_event DROP year, DROP passage, DROP responsable_wp5, DROP responsable_wp4, DROP need_wp5, DROP action_wp5');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise_wp5_event ADD year INT DEFAULT NULL, ADD passage VARCHAR(255) DEFAULT NULL, ADD responsable_wp5 VARCHAR(255) DEFAULT NULL, ADD responsable_wp4 VARCHAR(255) DEFAULT NULL, ADD need_wp5 VARCHAR(255) DEFAULT NULL, ADD action_wp5 VARCHAR(255) DEFAULT NULL');
    }
}
