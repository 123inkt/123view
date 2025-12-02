<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202213700 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add parent hash column with index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE revision ADD parent_hash VARCHAR(50) DEFAULT NULL after commit_hash');
        $this->addSql('CREATE INDEX IDX_PARENT_HASH ON revision (parent_hash)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_PARENT_HASH ON revision');
        $this->addSql('ALTER TABLE revision DROP parent_hash');
    }
}
