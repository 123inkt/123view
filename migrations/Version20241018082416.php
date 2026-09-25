<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241018082416 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE revision_file (id INT AUTO_INCREMENT NOT NULL, lines_added INT NOT NULL, lines_removed INT NOT NULL, filepath VARCHAR(255) NOT NULL, revision_id INT NOT NULL, INDEX IDX_5A84EF1DFA7C8F (revision_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE revision_file ADD CONSTRAINT FK_5A84EF1DFA7C8F FOREIGN KEY (revision_id) REFERENCES revision (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revision_file DROP FOREIGN KEY FK_5A84EF1DFA7C8F');
        $this->addSql('DROP TABLE revision_file');
    }
}
