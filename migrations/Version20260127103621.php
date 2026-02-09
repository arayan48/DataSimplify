<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260127103621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE entreprise_mise_en_relation (id INT AUTO_INCREMENT NOT NULL, responsible VARCHAR(255) DEFAULT NULL, technology VARCHAR(255) DEFAULT NULL, service_price VARCHAR(100) DEFAULT NULL, price_invoiced VARCHAR(100) DEFAULT NULL, start_date DATE DEFAULT NULL, finish_date DATE DEFAULT NULL, entreprise_id INT NOT NULL, INDEX IDX_4FCACA97A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise_wp2 (id INT AUTO_INCREMENT NOT NULL, score_dmao VARCHAR(100) DEFAULT NULL, digital_strategy VARCHAR(255) DEFAULT NULL, digital_readiness VARCHAR(255) DEFAULT NULL, human_centric VARCHAR(255) DEFAULT NULL, data_governance VARCHAR(255) DEFAULT NULL, ai VARCHAR(255) DEFAULT NULL, green VARCHAR(255) DEFAULT NULL, score_dma1 VARCHAR(100) DEFAULT NULL, entreprise_id INT NOT NULL, UNIQUE INDEX UNIQ_62B842EBA4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise_wp5_event (id INT AUTO_INCREMENT NOT NULL, year INT DEFAULT NULL, passage VARCHAR(255) DEFAULT NULL, responsable_wp5 VARCHAR(255) DEFAULT NULL, responsable_wp4 VARCHAR(255) DEFAULT NULL, need_wp5 VARCHAR(255) DEFAULT NULL, action_wp5 VARCHAR(255) DEFAULT NULL, entreprise_id INT NOT NULL, INDEX IDX_B91441E6A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise_wp5_formation (id INT AUTO_INCREMENT NOT NULL, responsible VARCHAR(255) DEFAULT NULL, technology VARCHAR(255) DEFAULT NULL, service_price VARCHAR(100) DEFAULT NULL, price_invoiced VARCHAR(100) DEFAULT NULL, start_date DATE DEFAULT NULL, finish_date DATE DEFAULT NULL, entreprise_id INT NOT NULL, INDEX IDX_2BBD8F8A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise_wp6 (id INT AUTO_INCREMENT NOT NULL, responsible VARCHAR(255) DEFAULT NULL, technology VARCHAR(255) DEFAULT NULL, service_price VARCHAR(100) DEFAULT NULL, price_invoiced VARCHAR(100) DEFAULT NULL, start_date DATE DEFAULT NULL, finish_date DATE DEFAULT NULL, entreprise_id INT NOT NULL, INDEX IDX_65D586F2A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE entreprise_wp7 (id INT AUTO_INCREMENT NOT NULL, responsible VARCHAR(255) DEFAULT NULL, amount_trigger VARCHAR(100) DEFAULT NULL, type_investment VARCHAR(255) DEFAULT NULL, source_financing VARCHAR(255) DEFAULT NULL, amount_obtained VARCHAR(100) DEFAULT NULL, service_price VARCHAR(100) DEFAULT NULL, price_invoiced VARCHAR(100) DEFAULT NULL, start_date DATE DEFAULT NULL, finish_date DATE DEFAULT NULL, entreprise_id INT NOT NULL, INDEX IDX_12D2B664A4AEAFEA (entreprise_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE entreprise_mise_en_relation ADD CONSTRAINT FK_4FCACA97A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise_wp2 ADD CONSTRAINT FK_62B842EBA4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise_wp5_event ADD CONSTRAINT FK_B91441E6A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise_wp5_formation ADD CONSTRAINT FK_2BBD8F8A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise_wp6 ADD CONSTRAINT FK_65D586F2A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise_wp7 ADD CONSTRAINT FK_12D2B664A4AEAFEA FOREIGN KEY (entreprise_id) REFERENCES entreprise (id)');
        $this->addSql('ALTER TABLE entreprise ADD nom VARCHAR(255) NOT NULL, ADD annee_edih INT DEFAULT NULL, ADD type_structure VARCHAR(100) NOT NULL, ADD annee_creation INT NOT NULL, ADD secteur VARCHAR(255) NOT NULL, ADD siret VARCHAR(14) NOT NULL, ADD taille VARCHAR(50) DEFAULT NULL, ADD chiffre_affaires VARCHAR(100) DEFAULT NULL, ADD code_postal VARCHAR(10) NOT NULL, ADD ville VARCHAR(100) NOT NULL, ADD region VARCHAR(100) DEFAULT NULL, ADD pays VARCHAR(100) NOT NULL, ADD adresse LONGTEXT NOT NULL, ADD description LONGTEXT DEFAULT NULL, ADD created_at DATETIME NOT NULL, ADD updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE entreprise_mise_en_relation DROP FOREIGN KEY FK_4FCACA97A4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_wp2 DROP FOREIGN KEY FK_62B842EBA4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_wp5_event DROP FOREIGN KEY FK_B91441E6A4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_wp5_formation DROP FOREIGN KEY FK_2BBD8F8A4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_wp6 DROP FOREIGN KEY FK_65D586F2A4AEAFEA');
        $this->addSql('ALTER TABLE entreprise_wp7 DROP FOREIGN KEY FK_12D2B664A4AEAFEA');
        $this->addSql('DROP TABLE entreprise_mise_en_relation');
        $this->addSql('DROP TABLE entreprise_wp2');
        $this->addSql('DROP TABLE entreprise_wp5_event');
        $this->addSql('DROP TABLE entreprise_wp5_formation');
        $this->addSql('DROP TABLE entreprise_wp6');
        $this->addSql('DROP TABLE entreprise_wp7');
        $this->addSql('ALTER TABLE entreprise DROP nom, DROP annee_edih, DROP type_structure, DROP annee_creation, DROP secteur, DROP siret, DROP taille, DROP chiffre_affaires, DROP code_postal, DROP ville, DROP region, DROP pays, DROP adresse, DROP description, DROP created_at, DROP updated_at');
    }
}
