<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230219104940 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE user_access_token (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, token CHAR(80) NOT NULL, name VARCHAR(100) NOT NULL, create_timestamp INT NOT NULL, use_timestamp INT NOT NULL, INDEX IDX_USER_ID (user_id), UNIQUE INDEX IDX_TOKEN (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
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
