<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220530173455 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE external_link (id INT AUTO_INCREMENT NOT NULL, pattern VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE filter (id INT AUTO_INCREMENT NOT NULL, rule_id INT NOT NULL, type VARCHAR(50) NOT NULL, inclusion TINYINT(1) NOT NULL, pattern VARCHAR(255) NOT NULL, INDEX IDX_7FC45F1D744E0351 (rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE recipient (id INT AUTO_INCREMENT NOT NULL, rule_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, INDEX IDX_6804FB49744E0351 (rule_id), UNIQUE INDEX rule_email (rule_id, email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repository (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE repository_property (name VARCHAR(255) NOT NULL, repository_id INT NOT NULL, value VARCHAR(255) NOT NULL, INDEX IDX_FC54CE7650C9D4F7 (repository_id), PRIMARY KEY(repository_id, name)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, active TINYINT(1) NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_46D8ACCCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule_repository (rule_id INT NOT NULL, repository_id INT NOT NULL, INDEX IDX_674FDB16744E0351 (rule_id), INDEX IDX_674FDB1650C9D4F7 (repository_id), PRIMARY KEY(rule_id, repository_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule_options (id INT AUTO_INCREMENT NOT NULL, rule_id INT NOT NULL, frequency ENUM(\'once-per-hour\', \'once-per-two-hours\', \'once-per-three-hours\', \'once-per-four-hours\', \'once-per-day\', \'once-per-week\') NOT NULL COMMENT \'(DC2Type:enum_frequency)\', diff_algorithm ENUM(\'patience\', \'minimal\', \'histogram\', \'myers\') NOT NULL COMMENT \'(DC2Type:enum_diff_algorithm)\', ignore_space_at_eol TINYINT(1) DEFAULT 1 NOT NULL, ignore_space_change TINYINT(1) DEFAULT 0 NOT NULL, ignore_all_space TINYINT(1) DEFAULT 0 NOT NULL, ignore_blank_lines TINYINT(1) DEFAULT 0 NOT NULL, subject VARCHAR(255) DEFAULT NULL, theme ENUM(\'upsource\', \'darcula\') NOT NULL COMMENT \'(DC2Type:enum_mail_theme)\', UNIQUE INDEX UNIQ_20A1C333744E0351 (rule_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE filter ADD CONSTRAINT FK_7FC45F1D744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
        $this->addSql('ALTER TABLE repository_property ADD CONSTRAINT FK_FC54CE7650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
        $this->addSql('ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rule_repository ADD CONSTRAINT FK_674FDB16744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rule_repository ADD CONSTRAINT FK_674FDB1650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rule_options ADD CONSTRAINT FK_20A1C333744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repository_property DROP FOREIGN KEY FK_FC54CE7650C9D4F7');
        $this->addSql('ALTER TABLE rule_repository DROP FOREIGN KEY FK_674FDB1650C9D4F7');
        $this->addSql('ALTER TABLE filter DROP FOREIGN KEY FK_7FC45F1D744E0351');
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49744E0351');
        $this->addSql('ALTER TABLE rule_repository DROP FOREIGN KEY FK_674FDB16744E0351');
        $this->addSql('ALTER TABLE rule_options DROP FOREIGN KEY FK_20A1C333744E0351');
        $this->addSql('ALTER TABLE rule DROP FOREIGN KEY FK_46D8ACCCA76ED395');
        $this->addSql('DROP TABLE external_link');
        $this->addSql('DROP TABLE filter');
        $this->addSql('DROP TABLE recipient');
        $this->addSql('DROP TABLE repository');
        $this->addSql('DROP TABLE repository_property');
        $this->addSql('DROP TABLE rule');
        $this->addSql('DROP TABLE rule_repository');
        $this->addSql('DROP TABLE rule_options');
        $this->addSql('DROP TABLE user');
    }
}
