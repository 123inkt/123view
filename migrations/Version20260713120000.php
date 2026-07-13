<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260713120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend repository_credential: value to LONGTEXT and auth_type ENUM to include ssh-key';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE repository_credential MODIFY COLUMN value LONGTEXT NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE repository_credential MODIFY COLUMN auth_type ENUM('basic-auth', 'ssh-key') " .
            "DEFAULT 'basic-auth' NOT NULL COMMENT '(DC2Type:enum_authentication_type)'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE repository_credential MODIFY COLUMN value VARCHAR(255) NOT NULL"
        );
        $this->addSql(
            "ALTER TABLE repository_credential MODIFY COLUMN auth_type ENUM('basic-auth') " .
            "DEFAULT 'basic-auth' NOT NULL COMMENT '(DC2Type:enum_authentication_type)'"
        );
    }
}
