<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221103220712 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE file_seen_status (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, review_id INT DEFAULT NULL, file_path VARCHAR(255) NOT NULL, create_timestamp INT NOT NULL, INDEX IDX_D5F89FCBA76ED395 (user_id), INDEX IDX_D5F89FCB3E2E969B (review_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE file_seen_status ADD CONSTRAINT FK_D5F89FCBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE file_seen_status ADD CONSTRAINT FK_D5F89FCB3E2E969B FOREIGN KEY (review_id) REFERENCES code_review (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file_seen_status DROP FOREIGN KEY FK_D5F89FCBA76ED395');
        $this->addSql('ALTER TABLE file_seen_status DROP FOREIGN KEY FK_D5F89FCB3E2E969B');
        $this->addSql('DROP TABLE file_seen_status');
    }
}
