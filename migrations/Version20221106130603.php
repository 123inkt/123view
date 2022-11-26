<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221106130603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhook ADD repository_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE webhook ADD CONSTRAINT FK_8A74175650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
        $this->addSql('CREATE INDEX IDX_8A74175650C9D4F7 ON webhook (repository_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhook DROP FOREIGN KEY FK_8A74175650C9D4F7');
        $this->addSql('DROP INDEX IDX_8A74175650C9D4F7 ON webhook');
        $this->addSql('ALTER TABLE webhook DROP repository_id');
    }
}
