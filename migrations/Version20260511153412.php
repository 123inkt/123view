<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260511153412 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `revision` ADD KEY `reviewId_repositoryId` (`repository_id` , `review_id`)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `revision` DROP KEY `reviewId_repositoryId`');
    }
}
