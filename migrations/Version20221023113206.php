<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221023113206 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review ADD project_id INT NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX IDX_REPOSITORY_ID_PROJECT_ID ON code_review (project_id, repository_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_REPOSITORY_ID_PROJECT_ID ON code_review');
        $this->addSql('ALTER TABLE code_review DROP project_id');
    }
}
