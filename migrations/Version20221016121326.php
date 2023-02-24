<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221016121326 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE repository CHANGE active active TINYINT(1) DEFAULT 1 NOT NULL, CHANGE update_revisions_interval update_revisions_interval SMALLINT DEFAULT 900 NOT NULL, CHANGE update_revisions_timestamp update_revisions_timestamp INT DEFAULT NULL'
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE repository CHANGE active active TINYINT(1) NOT NULL, CHANGE update_revisions_interval update_revisions_interval SMALLINT NOT NULL, CHANGE update_revisions_timestamp update_revisions_timestamp INT NOT NULL'
        );
    }
}
