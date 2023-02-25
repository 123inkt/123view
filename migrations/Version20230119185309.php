<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230119185309 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE code_review ADD update_timestamp INT NOT NULL');
        $this->addSql('CREATE INDEX IDX_CREATE_TIMESTAMP_REPOSITORY ON code_review (create_timestamp, repository_id)');
        $this->addSql('CREATE INDEX IDX_UPDATE_TIMESTAMP_REPOSITORY ON code_review (update_timestamp, repository_id)');
    }

    public function postUp(Schema $schema): void
    {
        $query = "UPDATE code_review cr
            LEFT JOIN
	        (SELECT review_id, MAX(create_timestamp) AS update_timestamp FROM code_review_activity GROUP BY review_id) AS cra
            ON   cra.review_id=cr.id
            SET cr.update_timestamp=IF(ISNULL(cra.update_timestamp), cr.create_timestamp, cra.update_timestamp)";
        $this->connection->executeQuery($query);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CREATE_TIMESTAMP_REPOSITORY ON code_review');
        $this->addSql('DROP INDEX IDX_UPDATE_TIMESTAMP_REPOSITORY ON code_review');
        $this->addSql('ALTER TABLE code_review DROP update_timestamp');
    }
}
