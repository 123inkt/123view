<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231110204207 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE rule_notification (id INT AUTO_INCREMENT NOT NULL, rule_id INT NOT NULL, notify_timestamp INT NOT NULL, create_timestamp INT NOT NULL, INDEX IDX_RULE_ID (rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE rule_notification ADD CONSTRAINT FK_39969EA4744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rule_notification DROP FOREIGN KEY FK_39969EA4744E0351');
        $this->addSql('DROP TABLE rule_notification');
    }
}
