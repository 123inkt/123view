<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260514163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Reverse column order of IDX_UPDATE_TIMESTAMP_REPOSITORY to (repository_id, update_timestamp) for efficient ORDER BY on filtered queries';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_UPDATE_TIMESTAMP_REPOSITORY ON code_review');
        $this->addSql('CREATE INDEX IDX_UPDATE_TIMESTAMP_REPOSITORY ON code_review (repository_id, update_timestamp)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_UPDATE_TIMESTAMP_REPOSITORY ON code_review');
        $this->addSql('CREATE INDEX IDX_UPDATE_TIMESTAMP_REPOSITORY ON code_review (update_timestamp, repository_id)');
    }
}
