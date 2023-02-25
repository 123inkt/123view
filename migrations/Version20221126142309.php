<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221126142309 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE user_setting CHANGE color_theme color_theme ENUM(\'auto\', \'light\', \'dark\') DEFAULT \'auto\' NOT NULL COMMENT \'(DC2Type:enum_color_theme)\''
        );
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(
            'ALTER TABLE user_setting CHANGE color_theme color_theme ENUM(\'auto\', \'light\', \'dark\') DEFAULT \'1\' NOT NULL COMMENT \'(DC2Type:enum_color_theme)\''
        );
    }
}
