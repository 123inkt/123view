<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230118190825 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'CREATE TABLE user_mention (comment_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_BA0F47C7F8697D13 (comment_id), INDEX IDX_BA0F47C7A76ED395 (user_id), PRIMARY KEY(comment_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB'
        );
        $this->addSql('ALTER TABLE user_mention ADD CONSTRAINT FK_BA0F47C7F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE user_mention ADD CONSTRAINT FK_BA0F47C7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function postUp(Schema $schema): void
    {
        $commentMentions = $this->connection->executeQuery("SELECT `id` AS `comment_id`, `message` FROM `comment` WHERE `message` LIKE '%@user:%'");
        $replyMentions   = $this->connection->executeQuery("SELECT `comment_id`, `message` FROM `comment_reply` WHERE `message` LIKE '%@user:%'");

        $mentions = array_merge($commentMentions->fetchAllAssociative(), $replyMentions->fetchAllAssociative());
        $results  = [];

        // grab all user mentions from comments
        foreach ($mentions as $mention) {
            $commentId = (int)$mention['comment_id'];
            $count     = preg_match_all('/@user:(\d+)\[/', $mention['message'], $matches);

            for ($i = 0; $i < $count; $i++) {
                $results[$commentId][(int)$matches[1][$i]] = true;
            }
        }

        // insert mentions uniquely
        foreach ($results as $commentId => $users) {
            foreach (array_keys($users) as $userId) {
                $this->connection->insert('user_mention', ['user_id' => $userId, 'comment_id' => $commentId]);
            }
        }
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_mention DROP FOREIGN KEY FK_BA0F47C7F8697D13');
        $this->addSql('ALTER TABLE user_mention DROP FOREIGN KEY FK_BA0F47C7A76ED395');
        $this->addSql('DROP TABLE user_mention');
    }
}
