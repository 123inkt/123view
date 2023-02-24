<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021160519 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE comment_reply (id INT AUTO_INCREMENT NOT NULL, comment_id INT NOT NULL, user_id INT NOT NULL, message LONGTEXT NOT NULL, create_timestamp INT NOT NULL, update_timestamp INT NOT NULL, INDEX IDX_54325E11F8697D13 (comment_id), INDEX IDX_54325E11A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE comment_reply ADD CONSTRAINT FK_54325E11F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE comment_reply ADD CONSTRAINT FK_54325E11A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment_reply DROP FOREIGN KEY FK_54325E11F8697D13');
        $this->addSql('ALTER TABLE comment_reply DROP FOREIGN KEY FK_54325E11A76ED395');
        $this->addSql('DROP TABLE comment_reply');
    }
}
