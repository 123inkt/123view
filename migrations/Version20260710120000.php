<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260710120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add configurable AI code review instructions and reference tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            'CREATE TABLE ai_review_configuration (id INT NOT NULL, instructions LONGTEXT DEFAULT NULL, ' .
            'update_timestamp INT NOT NULL, PRIMARY KEY(id)) ' .
            'DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE ai_review_reference (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, ' .
            'description VARCHAR(500) DEFAULT NULL, ' .
            'enabled TINYINT(1) DEFAULT 1 NOT NULL, priority INT DEFAULT 0 NOT NULL, PRIMARY KEY(id)) ' .
            'DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE ai_review_reference_section (id INT AUTO_INCREMENT NOT NULL, reference_id INT NOT NULL, ' .
            'heading VARCHAR(255) NOT NULL, ' .
            'content LONGTEXT NOT NULL, sort_order INT DEFAULT 0 NOT NULL, ' .
            'INDEX IDX_AI_REVIEW_REFERENCE_SECTION_REFERENCE (reference_id), PRIMARY KEY(id)) ' .
            'DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE ai_review_reference_matcher (id INT AUTO_INCREMENT NOT NULL, reference_id INT NOT NULL, ' .
            'file_pattern VARCHAR(255) NOT NULL, ' .
            'code_marker VARCHAR(255) DEFAULT NULL, INDEX IDX_AI_REVIEW_REFERENCE_MATCHER_REFERENCE (reference_id), PRIMARY KEY(id)) ' .
            'DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE ai_review_reference_section ADD CONSTRAINT FK_AI_REVIEW_REFERENCE_SECTION_REFERENCE FOREIGN KEY (reference_id) ' .
            'REFERENCES ai_review_reference (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE ai_review_reference_matcher ADD CONSTRAINT FK_AI_REVIEW_REFERENCE_MATCHER_REFERENCE FOREIGN KEY (reference_id) ' .
            'REFERENCES ai_review_reference (id) ON DELETE CASCADE'
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ai_review_reference_section DROP FOREIGN KEY FK_AI_REVIEW_REFERENCE_SECTION_REFERENCE');
        $this->addSql('ALTER TABLE ai_review_reference_matcher DROP FOREIGN KEY FK_AI_REVIEW_REFERENCE_MATCHER_REFERENCE');
        $this->addSql('DROP TABLE ai_review_reference_section');
        $this->addSql('DROP TABLE ai_review_reference_matcher');
        $this->addSql('DROP TABLE ai_review_reference');
        $this->addSql('DROP TABLE ai_review_configuration');
    }
}
