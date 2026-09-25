<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260512202200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type column to comment table for draft/final comment support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE comment ADD type ENUM('draft', 'final') DEFAULT 'final' NOT NULL COMMENT '(DC2Type:enum_comment_type)'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP type');
    }
}
