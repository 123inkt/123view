<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251007185900 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE code_review ADD COLUMN ai_review_requested TINYINT(1) DEFAULT 0 NOT NULL AFTER reference_id');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE code_review DROP COLUMN ai_review_requested');
    }
}
