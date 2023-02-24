<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221019195222 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_reviewer CHANGE state_timestamp state_timestamp INT NOT NULL');
        $this->addSql('ALTER TABLE comment ADD create_timestamp INT NOT NULL, ADD update_timestamp INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP create_timestamp, DROP update_timestamp');
        $this->addSql('ALTER TABLE code_reviewer CHANGE state_timestamp state_timestamp INT DEFAULT NULL');
    }
}
