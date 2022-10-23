<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221023112216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review ADD reference_id VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE webhook_activity DROP FOREIGN KEY FK_528E8F725C9BA60B');
        $this->addSql('ALTER TABLE webhook_activity ADD CONSTRAINT FK_528E8F725C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhook (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review DROP reference_id');
        $this->addSql('ALTER TABLE webhook_activity DROP FOREIGN KEY FK_528E8F725C9BA60B');
        $this->addSql('ALTER TABLE webhook_activity ADD CONSTRAINT FK_528E8F725C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhook_activity (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
