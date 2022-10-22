<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221022191618 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE webhook (id INT AUTO_INCREMENT NOT NULL, enabled TINYINT(1) DEFAULT 1 NOT NULL, url VARCHAR(255) NOT NULL, retries INT DEFAULT 3 NOT NULL, verify_ssl TINYINT(1) DEFAULT 1 NOT NULL, headers JSON DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE webhook_activity (id INT AUTO_INCREMENT NOT NULL, request VARCHAR(1000) NOT NULL, request_headers JSON DEFAULT NULL, status_code INT NOT NULL, response VARCHAR(5000) NOT NULL, response_headers JSON DEFAULT NULL, create_timestamp INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE webhook');
        $this->addSql('DROP TABLE webhook_activity');
    }
}
