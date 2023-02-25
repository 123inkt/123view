<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221201212737 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE code_review_activity (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, review_id INT NOT NULL, event_name VARCHAR(255) NOT NULL, data JSON DEFAULT NULL, create_timestamp INT NOT NULL, INDEX IDX_D215A294A76ED395 (user_id), INDEX IDX_D215A2943E2E969B (review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE code_review_activity ADD CONSTRAINT FK_D215A294A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE code_review_activity ADD CONSTRAINT FK_D215A2943E2E969B FOREIGN KEY (review_id) REFERENCES code_review (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review_activity DROP FOREIGN KEY FK_D215A294A76ED395');
        $this->addSql('ALTER TABLE code_review_activity DROP FOREIGN KEY FK_D215A2943E2E969B');
        $this->addSql('DROP TABLE code_review_activity');
    }
}
