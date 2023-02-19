<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230219091816 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE user_access_token (identifier VARCHAR(80) NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(100) NOT NULL, create_timestamp INT NOT NULL, use_timestamp INT NOT NULL, INDEX IDX_366EA16AA76ED395 (user_id), PRIMARY KEY(identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE user_access_token ADD CONSTRAINT FK_366EA16AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_access_token DROP FOREIGN KEY FK_366EA16AA76ED395');
        $this->addSql('DROP TABLE user_access_token');
    }
}
