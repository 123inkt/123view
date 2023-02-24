<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221024195841 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_REFERENCE_ID_REPOSITORY_ID ON code_review');
        $this->addSql('ALTER TABLE code_review CHANGE reference_id reference_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX IDX_REFERENCE_ID_REPOSITORY_ID ON code_review (reference_id, repository_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_REFERENCE_ID_REPOSITORY_ID ON code_review');
        $this->addSql('ALTER TABLE code_review CHANGE reference_id reference_id VARCHAR(255) NOT NULL');
        $this->addSql('CREATE INDEX IDX_REFERENCE_ID_REPOSITORY_ID ON code_review (reference_id, repository_id)');
    }
}
