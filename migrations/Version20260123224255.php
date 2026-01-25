<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260123224255 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create user_review_setting table for storing review preferences';
    }

    public function isTransactional(): bool
    {
        return true;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE user_review_setting (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, diff_visible_lines INT DEFAULT 6 NOT NULL, diff_comparison_policy VARCHAR(50) DEFAULT \'all\' NOT NULL, review_diff_mode VARCHAR(50) DEFAULT \'inline\' NOT NULL, review_comment_visibility VARCHAR(50) DEFAULT \'all\' NOT NULL, UNIQUE INDEX UNIQ_USER_REVIEW_SETTING_USER (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_review_setting ADD CONSTRAINT FK_USER_REVIEW_SETTING_USER FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_review_setting DROP FOREIGN KEY FK_USER_REVIEW_SETTING_USER');
        $this->addSql('DROP TABLE user_review_setting');
    }
}
