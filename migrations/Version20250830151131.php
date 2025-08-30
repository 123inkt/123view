<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add ShortUrl entity table for URL shortening functionality
 */
final class Version20250830151131 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ShortUrl entity table for URL shortening functionality';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE short_url (
            id INT AUTO_INCREMENT NOT NULL,
            short_key VARCHAR(50) NOT NULL,
            original_url VARCHAR(2000) NOT NULL,
            create_timestamp INT NOT NULL,
            UNIQUE INDEX UK_SHORT_KEY (short_key),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE short_url');
    }
}