<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220530192923 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter CHANGE type type ENUM(\'file\', \'author\', \'subject\') NOT NULL COMMENT \'(DC2Type:enum_filter_type)\'');
        $this->addSql('ALTER TABLE recipient CHANGE name name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE filter CHANGE type type VARCHAR(50) NOT NULL');
        $this->addSql('ALTER TABLE recipient CHANGE name name VARCHAR(255) NOT NULL');
    }
}
