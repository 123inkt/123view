<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230429114808 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX file_report_idx ON code_inspection_issue (file, report_id)');
        $this->addSql('CREATE INDEX create_timestamp_idx ON code_inspection_report (create_timestamp)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX file_report_idx ON code_inspection_issue');
        $this->addSql('DROP INDEX create_timestamp_idx ON code_inspection_report');
    }
}
