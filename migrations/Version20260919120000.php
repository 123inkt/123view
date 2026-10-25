<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260919120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Convert comment state to a native enum';
    }

    public function up(Schema $schema): void
    {
        $invalidStateCount = $this->connection->fetchOne("SELECT COUNT(*) FROM comment WHERE state IS NULL OR state NOT IN ('open', 'resolved')");
        $hasInvalidStates  = match (true) {
            is_int($invalidStateCount)   => $invalidStateCount > 0,
            is_string($invalidStateCount) => $invalidStateCount !== '0',
            default                      => true,
        };

        $this->abortIf(
            $hasInvalidStates,
            'Cannot convert comment.state to enum: unexpected state values exist.'
        );

        $this->addSql(
            "ALTER TABLE comment CHANGE state state ENUM('open', 'resolved') DEFAULT 'open' NOT NULL COMMENT '(DC2Type:enum_comment_state_type)'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE comment CHANGE state state VARCHAR(20) DEFAULT 'open' NOT NULL");
    }
}
