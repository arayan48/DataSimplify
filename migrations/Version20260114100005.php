<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260114100005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD partnaire_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6494087CAE9 FOREIGN KEY (partnaire_id) REFERENCES partnaire (id)');
        $this->addSql('CREATE INDEX IDX_8D93D6494087CAE9 ON user (partnaire_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6494087CAE9');
        $this->addSql('DROP INDEX IDX_8D93D6494087CAE9 ON user');
        $this->addSql('ALTER TABLE user DROP partnaire_id');
    }
}
