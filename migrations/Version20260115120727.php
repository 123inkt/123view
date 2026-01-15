<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260115120727 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Increase filepath column length from 255 to 500 characters';
    }

    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE revision_file MODIFY filepath VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE revision_file MODIFY filepath VARCHAR(255) NOT NULL');
    }
}