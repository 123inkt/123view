<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220526142018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE external_link ADD rule_id INT NOT NULL');
        $this->addSql('ALTER TABLE external_link ADD CONSTRAINT FK_A3B3F9DD744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
        $this->addSql('CREATE INDEX IDX_A3B3F9DD744E0351 ON external_link (rule_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE external_link DROP FOREIGN KEY FK_A3B3F9DD744E0351');
        $this->addSql('DROP INDEX IDX_A3B3F9DD744E0351 ON external_link');
        $this->addSql('ALTER TABLE external_link DROP rule_id');
    }
}
