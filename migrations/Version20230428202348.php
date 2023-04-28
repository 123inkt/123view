<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230428202348 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE file_coverage (id INT AUTO_INCREMENT NOT NULL, repository_id INT NOT NULL, file_path VARCHAR(500) NOT NULL, coverage MEDIUMTEXT NOT NULL, create_timestamp INT NOT NULL, INDEX IDX_E4067CF650C9D4F7 (repository_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE file_coverage ADD CONSTRAINT FK_E4067CF650C9D4F7 FOREIGN KEY (repository_id) REFERENCES repository (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE file_coverage DROP FOREIGN KEY FK_E4067CF650C9D4F7');
        $this->addSql('DROP TABLE file_coverage');
    }
}
