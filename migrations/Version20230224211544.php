<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230224211544 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE INDEX IDX_CREATE_TIMESTAMP_USER_EVENT ON code_review_activity (create_timestamp, user_id, event_name)');
        $this->addSql('CREATE INDEX IDX_EVENT_REPOSITORY ON code_review_activity (event_name)');
        $this->addSql('ALTER TABLE code_review_activity RENAME INDEX idx_d215a2943e2e969b TO IDX_REVIEW_ID');
        $this->addSql('ALTER TABLE code_review_activity RENAME INDEX idx_d215a294a76ed395 TO IDX_USER_ID');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CREATE_TIMESTAMP_USER_EVENT ON code_review_activity');
        $this->addSql('DROP INDEX IDX_EVENT_REPOSITORY ON code_review_activity');
        $this->addSql('ALTER TABLE code_review_activity RENAME INDEX idx_user_id TO IDX_D215A294A76ED395');
        $this->addSql('ALTER TABLE code_review_activity RENAME INDEX idx_review_id TO IDX_D215A2943E2E969B');
    }
}
