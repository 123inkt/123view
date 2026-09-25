<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add index on title field for CodeReview entity
 */
final class Version20250728193000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add index on title field for CodeReview entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX IDX_TITLE ON code_review (title)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_TITLE ON code_review');
    }
}