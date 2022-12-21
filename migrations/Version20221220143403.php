<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221220143403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repository ADD validate_revisions_interval INT DEFAULT 3600 NOT NULL, ADD validate_revisions_timestamp INT DEFAULT NULL, CHANGE update_revisions_interval update_revisions_interval INT DEFAULT 900 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repository DROP validate_revisions_interval, DROP validate_revisions_timestamp, CHANGE update_revisions_interval update_revisions_interval SMALLINT DEFAULT 900 NOT NULL');
    }
}
