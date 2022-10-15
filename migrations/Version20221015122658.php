<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221015122658 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code_review_user (code_review_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_266672E1926BEEA3 (code_review_id), INDEX IDX_266672E1A76ED395 (user_id), PRIMARY KEY(code_review_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code_review_user ADD CONSTRAINT FK_266672E1926BEEA3 FOREIGN KEY (code_review_id) REFERENCES code_review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE code_review_user ADD CONSTRAINT FK_266672E1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review_user DROP FOREIGN KEY FK_266672E1926BEEA3');
        $this->addSql('ALTER TABLE code_review_user DROP FOREIGN KEY FK_266672E1A76ED395');
        $this->addSql('DROP TABLE code_review_user');
    }
}
