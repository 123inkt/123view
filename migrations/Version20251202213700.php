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
        $this->addSql('ALTER TABLE revision ADD `sort` BINARY(16) DEFAULT NULL AFTER `first_branch`');
        $this->addSql('CREATE INDEX repository_parent_hash ON revision (`repository_id`, `parent_hash`)');
        $this->addSql('CREATE INDEX sort_timestamp ON revision (`sort`, `create_timestamp`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX repository_parent_hash ON revision');
        $this->addSql('DROP INDEX sort_timestamp ON revision');
        $this->addSql('ALTER TABLE revision DROP parent_hash');
        $this->addSql('ALTER TABLE revision DROP sort');
    }
}
