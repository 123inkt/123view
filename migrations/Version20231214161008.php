<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231214161008 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE git_access_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, git_type ENUM(\'gitlab\', \'github\') NOT NULL, token VARCHAR(255) NOT NULL, INDEX IDX_C01A936A76ED395 (user_id), UNIQUE INDEX user_tokens (user_id, git_type), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE git_access_token ADD CONSTRAINT FK_C01A936A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE repository CHANGE git_type git_type ENUM(\'gitlab\', \'github\', \'other\') DEFAULT NULL');
        $this->addSql('UPDATE repository SET git_type = NULL WHERE git_type = \'other\'');
        $this->addSql('ALTER TABLE repository CHANGE git_type git_type ENUM(\'gitlab\', \'github\') DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE git_access_token DROP FOREIGN KEY FK_C01A936A76ED395');
        $this->addSql('DROP TABLE git_access_token');
        $this->addSql('ALTER TABLE repository CHANGE git_type git_type VARCHAR(255) DEFAULT \'other\' NOT NULL');
    }
}
