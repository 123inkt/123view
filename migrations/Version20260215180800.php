<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260215180800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add git_approval_sync column to repository table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE repository ADD git_approval_sync TINYINT(1) DEFAULT 1 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE repository DROP git_approval_sync');
    }
}
