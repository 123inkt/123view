<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230521131012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE code_coverage_file (id INT AUTO_INCREMENT NOT NULL, report_id INT NOT NULL, file VARCHAR(255) NOT NULL, coverage VARBINARY(40000) NOT NULL, INDEX IDX_3965FF284BD2A4C0 (report_id), INDEX report_filepath (report_id, file), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE code_coverage_report (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, commit_hash VARCHAR(255) NOT NULL, create_timestamp INT NOT NULL, INDEX IDX_3108049150C9D4F7 (repository_id), INDEX create_timestamp (create_timestamp), INDEX repository_create_timestamp (repository_id, create_timestamp), INDEX repository_commit_hash (repository_id, commit_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE code_coverage_file ADD CONSTRAINT FK_3965FF284BD2A4C0 FOREIGN KEY (report_id) REFERENCES code_coverage_report (id)'
        );
        $this->addSql('ALTER TABLE code_coverage_report ADD CONSTRAINT FK_3108049150C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
        $this->addSql('ALTER TABLE file_coverage DROP FOREIGN KEY FK_E4067CF650C9D4F7');
        $this->addSql('DROP TABLE file_coverage');
        $this->addSql('DROP INDEX file_report_idx ON code_inspection_issue');
        $this->addSql('CREATE INDEX file_report_idx ON code_inspection_issue (report_id, file)');
        $this->addSql('DROP INDEX IDX_COMMIT_HASH_REPOSITORY_ID ON code_inspection_report');
        $this->addSql('CREATE INDEX repository_create_timestamp ON code_inspection_report (repository_id, create_timestamp)');
        $this->addSql('CREATE UNIQUE INDEX IDX_COMMIT_HASH_REPOSITORY_ID ON code_inspection_report (repository_id, inspection_id, commit_hash)');
        $this->addSql('ALTER TABLE code_inspection_report RENAME INDEX create_timestamp_idx TO create_timestamp');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE file_coverage (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, file_path VARCHAR(500) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, coverage MEDIUMTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, create_timestamp INT NOT NULL, INDEX IDX_E4067CF650C9D4F7 (repository_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' '
        );
        $this->addSql(
            'ALTER TABLE file_coverage ADD CONSTRAINT FK_E4067CF650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id) ON UPDATE NO ACTION ON DELETE NO ACTION'
        );
        $this->addSql('ALTER TABLE code_coverage_file DROP FOREIGN KEY FK_3965FF284BD2A4C0');
        $this->addSql('ALTER TABLE code_coverage_report DROP FOREIGN KEY FK_3108049150C9D4F7');
        $this->addSql('DROP TABLE code_coverage_file');
        $this->addSql('DROP TABLE code_coverage_report');
        $this->addSql('DROP INDEX file_report_idx ON code_inspection_issue');
        $this->addSql('CREATE INDEX file_report_idx ON code_inspection_issue (file, report_id)');
        $this->addSql('DROP INDEX repository_create_timestamp ON code_inspection_report');
        $this->addSql('DROP INDEX IDX_COMMIT_HASH_REPOSITORY_ID ON code_inspection_report');
        $this->addSql('CREATE UNIQUE INDEX IDX_COMMIT_HASH_REPOSITORY_ID ON code_inspection_report (commit_hash, repository_id, inspection_id)');
        $this->addSql('ALTER TABLE code_inspection_report RENAME INDEX create_timestamp TO create_timestamp_idx');
    }
}
