<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919190124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD tag ENUM(\'change_request\', \'explanation\', \'nice_to_have\', \'suggestion\') DEFAULT NULL');
        $this->addSql('ALTER TABLE comment_reply ADD tag ENUM(\'change_request\', \'explanation\', \'nice_to_have\', \'suggestion\') DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP tag');
        $this->addSql('ALTER TABLE comment_reply DROP tag');
    }
}
