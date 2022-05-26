<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220526142520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rule_options (id INT AUTO_INCREMENT NOT NULL, rule_id INT NOT NULL, diff_algorithm VARCHAR(255) NOT NULL, ignore_space_at_eol TINYINT(1) NOT NULL, ignore_space_change TINYINT(1) NOT NULL, ignore_all_space TINYINT(1) NOT NULL, ignore_blank_lines TINYINT(1) NOT NULL, subject VARCHAR(255) DEFAULT NULL, theme VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_20A1C333744E0351 (rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rule_options ADD CONSTRAINT FK_20A1C333744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE rule_options');
    }
}
