<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230107093040 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE revision_visibility (revision_id INT NOT NULL, review_id INT NOT NULL, user_id INT NOT NULL, visible TINYINT(1) NOT NULL, INDEX IDX_76F14F681DFA7C8F (revision_id), INDEX IDX_76F14F683E2E969B (review_id), INDEX IDX_76F14F68A76ED395 (user_id), INDEX review_user_idx (review_id, user_id), PRIMARY KEY(revision_id, review_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE revision_visibility ADD CONSTRAINT FK_76F14F681DFA7C8F FOREIGN KEY (revision_id) REFERENCES revision (id)');
        $this->addSql('ALTER TABLE revision_visibility ADD CONSTRAINT FK_76F14F683E2E969B FOREIGN KEY (review_id) REFERENCES code_review (id)');
        $this->addSql('ALTER TABLE revision_visibility ADD CONSTRAINT FK_76F14F68A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE revision_visibility DROP FOREIGN KEY FK_76F14F681DFA7C8F');
        $this->addSql('ALTER TABLE revision_visibility DROP FOREIGN KEY FK_76F14F683E2E969B');
        $this->addSql('ALTER TABLE revision_visibility DROP FOREIGN KEY FK_76F14F68A76ED395');
        $this->addSql('DROP TABLE revision_visibility');
    }
}
