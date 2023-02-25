<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230128124800 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review ADD actors JSON DEFAULT (\'[]\') NOT NULL AFTER `state`');
        $this->addSql('CREATE INDEX IDX_ACTORS ON code_review ( (CAST(actors AS UNSIGNED ARRAY)) );');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review DROP actors');
    }
}
