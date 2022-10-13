<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221013190712 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_reviewer DROP FOREIGN KEY FK_19C65F1B3E2E969B');
        $this->addSql('ALTER TABLE code_reviewer DROP FOREIGN KEY FK_19C65F1BA76ED395');
        $this->addSql('ALTER TABLE code_reviewer ADD CONSTRAINT FK_19C65F1B3E2E969B FOREIGN KEY (review_id) REFERENCES code_review (id)');
        $this->addSql('ALTER TABLE code_reviewer ADD CONSTRAINT FK_19C65F1BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_reviewer DROP FOREIGN KEY FK_19C65F1B3E2E969B');
        $this->addSql('ALTER TABLE code_reviewer DROP FOREIGN KEY FK_19C65F1BA76ED395');
        $this->addSql('ALTER TABLE code_reviewer ADD CONSTRAINT FK_19C65F1B3E2E969B FOREIGN KEY (review_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE code_reviewer ADD CONSTRAINT FK_19C65F1BA76ED395 FOREIGN KEY (user_id) REFERENCES code_review (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
