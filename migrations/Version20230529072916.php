<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230529072916 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_coverage_report ADD branch_id VARCHAR(255) DEFAULT NULL AFTER commit_hash');
        $this->addSql('ALTER TABLE code_inspection_report ADD branch_id VARCHAR(255) DEFAULT NULL AFTER inspection_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_coverage_report DROP branch_id');
        $this->addSql('ALTER TABLE code_inspection_report DROP branch_id');
    }
}
