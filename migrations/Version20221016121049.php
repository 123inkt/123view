<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221016121049 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE repository ADD active TINYINT(1) NOT NULL, ADD update_revisions_interval SMALLINT NOT NULL, ADD update_revisions_timestamp INT NOT NULL'
        );
        $this->addSql('CREATE INDEX active_idx ON repository (active)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX active_idx ON repository');
        $this->addSql('ALTER TABLE repository DROP active, DROP update_revisions_interval, DROP update_revisions_timestamp');
    }
}
