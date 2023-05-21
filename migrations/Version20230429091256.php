<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429091256 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE code_inspection_issue (id INT AUTO_INCREMENT NOT NULL, report_id INT NOT NULL, severity VARCHAR(50) NOT NULL, file VARCHAR(255) NOT NULL, line_number INT NOT NULL, message VARCHAR(255) NOT NULL, rule VARCHAR(255) DEFAULT NULL, INDEX IDX_EDD5780F4BD2A4C0 (report_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'CREATE TABLE code_inspection_report (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, commit_hash VARCHAR(255) NOT NULL, inspection_id VARCHAR(50) NOT NULL, create_timestamp INT NOT NULL, INDEX IDX_950E0FE550C9D4F7 (repository_id), UNIQUE INDEX IDX_COMMIT_HASH_REPOSITORY_ID (commit_hash, repository_id, inspection_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE code_inspection_issue ADD CONSTRAINT FK_EDD5780F4BD2A4C0 FOREIGN KEY (report_id) REFERENCES code_inspection_report (id)'
        );
        $this->addSql('ALTER TABLE code_inspection_report ADD CONSTRAINT FK_950E0FE550C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_inspection_issue DROP FOREIGN KEY FK_EDD5780F4BD2A4C0');
        $this->addSql('ALTER TABLE code_inspection_report DROP FOREIGN KEY FK_950E0FE550C9D4F7');
        $this->addSql('DROP TABLE code_inspection_issue');
        $this->addSql('DROP TABLE code_inspection_report');
    }
}
