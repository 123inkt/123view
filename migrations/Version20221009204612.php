<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221009204612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code_review (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, title VARCHAR(255) NOT NULL, state ENUM(\'open\', \'closed\') DEFAULT \'open\' NOT NULL COMMENT \'(DC2Type:enum_code_review_state_type)\', INDEX IDX_6C5D96450C9D4F7 (repository_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE code_review_user (code_review_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_266672E1926BEEA3 (code_review_id), INDEX IDX_266672E1A76ED395 (user_id), PRIMARY KEY(code_review_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE code_reviewer (id INT AUTO_INCREMENT NOT NULL, review_id INT NOT NULL, user_id INT NOT NULL, state ENUM(\'open\', \'rejected\', \'accepted\') DEFAULT \'open\' NOT NULL COMMENT \'(DC2Type:enum_code_reviewer_state_type)\', INDEX IDX_19C65F1B3E2E969B (review_id), INDEX IDX_19C65F1BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE revision (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, commit_hash VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, author_email VARCHAR(255) NOT NULL, author_name VARCHAR(255) NOT NULL, INDEX IDX_6D6315CC50C9D4F7 (repository_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE code_review ADD CONSTRAINT FK_6C5D96450C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
        $this->addSql('ALTER TABLE code_review_user ADD CONSTRAINT FK_266672E1926BEEA3 FOREIGN KEY (code_review_id) REFERENCES code_review (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE code_review_user ADD CONSTRAINT FK_266672E1A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE code_reviewer ADD CONSTRAINT FK_19C65F1B3E2E969B FOREIGN KEY (review_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE code_reviewer ADD CONSTRAINT FK_19C65F1BA76ED395 FOREIGN KEY (user_id) REFERENCES code_review (id)');
        $this->addSql('ALTER TABLE revision ADD CONSTRAINT FK_6D6315CC50C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review DROP FOREIGN KEY FK_6C5D96450C9D4F7');
        $this->addSql('ALTER TABLE code_review_user DROP FOREIGN KEY FK_266672E1926BEEA3');
        $this->addSql('ALTER TABLE code_review_user DROP FOREIGN KEY FK_266672E1A76ED395');
        $this->addSql('ALTER TABLE code_reviewer DROP FOREIGN KEY FK_19C65F1B3E2E969B');
        $this->addSql('ALTER TABLE code_reviewer DROP FOREIGN KEY FK_19C65F1BA76ED395');
        $this->addSql('ALTER TABLE revision DROP FOREIGN KEY FK_6D6315CC50C9D4F7');
        $this->addSql('DROP TABLE code_review');
        $this->addSql('DROP TABLE code_review_user');
        $this->addSql('DROP TABLE code_reviewer');
        $this->addSql('DROP TABLE revision');
    }
}
