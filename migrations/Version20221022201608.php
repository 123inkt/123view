<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221022201608 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhook_activity ADD webhook_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE webhook_activity ADD CONSTRAINT FK_528E8F725C9BA60B FOREIGN KEY (webhook_id) REFERENCES webhook_activity (id)');
        $this->addSql('CREATE INDEX IDX_528E8F725C9BA60B ON webhook_activity (webhook_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE webhook_activity DROP FOREIGN KEY FK_528E8F725C9BA60B');
        $this->addSql('DROP INDEX IDX_528E8F725C9BA60B ON webhook_activity');
        $this->addSql('ALTER TABLE webhook_activity DROP webhook_id');
    }
}
