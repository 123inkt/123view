<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240919185043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment ADD tag VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE comment_reply ADD tag VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE comment DROP tag');
        $this->addSql('ALTER TABLE comment_reply DROP tag');
    }
}
