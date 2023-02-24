<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221020195639 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment ADD file_path VARCHAR(500) NOT NULL');
        $this->addSql('CREATE INDEX IDX_REVIEW_ID_FILE_PATH ON comment (review_id, file_path)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_REVIEW_ID_FILE_PATH ON comment');
        $this->addSql('ALTER TABLE comment DROP file_path');
    }
}
