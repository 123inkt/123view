<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230825185928 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repository DROP INDEX UNIQ_5CFE57CD2558A7A5, ADD INDEX IDX_5CFE57CD2558A7A5 (credential_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repository DROP INDEX IDX_5CFE57CD2558A7A5, ADD UNIQUE INDEX UNIQ_5CFE57CD2558A7A5 (credential_id)');
    }
}
