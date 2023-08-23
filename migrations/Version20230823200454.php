<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230823200454 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE repository_credential (id INT AUTO_INCREMENT NOT NULL, auth_type ENUM(\'basic-auth\') DEFAULT \'basic-auth\' NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE repository ADD credential_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE repository ADD CONSTRAINT FK_5CFE57CD2558A7A5 FOREIGN KEY (credential_id) REFERENCES repository_credential (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5CFE57CD2558A7A5 ON repository (credential_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE repository DROP FOREIGN KEY FK_5CFE57CD2558A7A5');
        $this->addSql('DROP TABLE repository_credential');
        $this->addSql('DROP INDEX UNIQ_5CFE57CD2558A7A5 ON repository');
        $this->addSql('ALTER TABLE repository DROP credential_id');
    }
}
