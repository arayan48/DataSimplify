<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206145054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create logs table for system logging';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE logs (
            id INT AUTO_INCREMENT NOT NULL, 
            type VARCHAR(50) NOT NULL, 
            message LONGTEXT NOT NULL, 
            user_email VARCHAR(255) DEFAULT NULL, 
            user_id INT DEFAULT NULL, 
            ip_address VARCHAR(45) DEFAULT NULL, 
            status VARCHAR(20) NOT NULL, 
            context JSON DEFAULT NULL, 
            created_at DATETIME NOT NULL, 
            INDEX idx_log_type (type), 
            INDEX idx_log_user (user_email), 
            INDEX idx_log_created (created_at), 
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE logs');
    }
}
