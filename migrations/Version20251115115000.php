<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251115115000 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE code_review ADD COLUMN ai_review TEXT DEFAULT NULL AFTER ai_review_requested');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE code_review DROP COLUMN ai_review');
    }
}
