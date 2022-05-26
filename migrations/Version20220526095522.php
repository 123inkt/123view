<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220526095522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rule (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, name VARCHAR(255) NOT NULL, diff_algorithm VARCHAR(50) NOT NULL, active TINYINT(1) NOT NULL, INDEX IDX_46D8ACCCA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rule_repository (rule_id INT NOT NULL, repository_id INT NOT NULL, INDEX IDX_674FDB16744E0351 (rule_id), INDEX IDX_674FDB1650C9D4F7 (repository_id), PRIMARY KEY(rule_id, repository_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rule ADD CONSTRAINT FK_46D8ACCCA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rule_repository ADD CONSTRAINT FK_674FDB16744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rule_repository ADD CONSTRAINT FK_674FDB1650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipient ADD rule_id INT NOT NULL');
        $this->addSql('ALTER TABLE recipient ADD CONSTRAINT FK_6804FB49744E0351 FOREIGN KEY (rule_id) REFERENCES rule (id)');
        $this->addSql('CREATE INDEX IDX_6804FB49744E0351 ON recipient (rule_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipient DROP FOREIGN KEY FK_6804FB49744E0351');
        $this->addSql('ALTER TABLE rule_repository DROP FOREIGN KEY FK_674FDB16744E0351');
        $this->addSql('DROP TABLE rule');
        $this->addSql('DROP TABLE rule_repository');
        $this->addSql('DROP INDEX IDX_6804FB49744E0351 ON recipient');
        $this->addSql('ALTER TABLE recipient DROP rule_id');
    }
}
