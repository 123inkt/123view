<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240428162448 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE folder_collapse_status (id INT AUTO_INCREMENT NOT NULL, path VARCHAR(500) NOT NULL, user_id INT NOT NULL, review_id INT NOT NULL, INDEX IDX_259B2931A76ED395 (user_id), INDEX IDX_259B29313E2E969B (review_id), UNIQUE INDEX IDX_REVIEW_USER_PATH (review_id, user_id, path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE folder_collapse_status ADD CONSTRAINT FK_259B2931A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE folder_collapse_status ADD CONSTRAINT FK_259B29313E2E969B FOREIGN KEY (review_id) REFERENCES code_review (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE folder_collapse_status DROP FOREIGN KEY FK_259B2931A76ED395');
        $this->addSql('ALTER TABLE folder_collapse_status DROP FOREIGN KEY FK_259B29313E2E969B');
        $this->addSql('DROP TABLE folder_collapse_status');
    }
}
