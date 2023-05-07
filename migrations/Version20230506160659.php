<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230506160659 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE webhook_repository (webhook_id INT NOT NULL, repository_id INT NOT NULL, INDEX IDX_57821885C9BA60B (webhook_id), ' .
            'INDEX IDX_578218850C9D4F7 (repository_id), ' .
            'PRIMARY KEY(webhook_id, repository_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql(
            'ALTER TABLE webhook_repository ADD CONSTRAINT FK_57821885C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhook (id) ON DELETE CASCADE'
        );
        $this->addSql(
            'ALTER TABLE webhook_repository ' .
            'ADD CONSTRAINT FK_578218850C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id) ON DELETE CASCADE'
        );
        $this->addSql('ALTER TABLE webhook DROP FOREIGN KEY FK_8A74175650C9D4F7');
        $this->addSql('DROP INDEX IDX_8A74175650C9D4F7 ON webhook');
        $this->addSql('INSERT INTO webhook_repository SELECT `id` AS webhook_id, repository_id FROM webhook');
        $this->addSql('ALTER TABLE webhook DROP repository_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhook_repository DROP FOREIGN KEY FK_57821885C9BA60B');
        $this->addSql('ALTER TABLE webhook_repository DROP FOREIGN KEY FK_578218850C9D4F7');
        $this->addSql('DROP TABLE webhook_repository');
        $this->addSql('ALTER TABLE webhook ADD repository_id INT DEFAULT NULL');
        $this->addSql(
            'ALTER TABLE webhook ' .
            'ADD CONSTRAINT FK_8A74175650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id) ON UPDATE NO ACTION ON DELETE NO ACTION'
        );
        $this->addSql('CREATE INDEX IDX_8A74175650C9D4F7 ON webhook (repository_id)');
    }
}
