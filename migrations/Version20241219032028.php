<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219032028 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_db ADD status VARCHAR(100) DEFAULT NULL');
        $this->addSql('ALTER TABLE user CHANGE location location VARCHAR(255) DEFAULT NULL, CHANGE skills skills JSON DEFAULT NULL, CHANGE work_experience work_experience JSON DEFAULT NULL, CHANGE phone phone VARCHAR(15) DEFAULT NULL, CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT NULL, CHANGE twitter twitter VARCHAR(255) DEFAULT NULL, CHANGE facebook facebook VARCHAR(255) DEFAULT NULL, CHANGE instagram instagram VARCHAR(255) DEFAULT NULL, CHANGE linkedin linkedin VARCHAR(255) DEFAULT NULL, CHANGE github github VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_db DROP status');
        $this->addSql('ALTER TABLE messenger_messages CHANGE delivered_at delivered_at DATETIME DEFAULT \'NULL\' COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE location location VARCHAR(255) DEFAULT \'NULL\', CHANGE skills skills LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE work_experience work_experience LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE phone phone VARCHAR(15) DEFAULT \'NULL\', CHANGE profile_picture profile_picture VARCHAR(255) DEFAULT \'NULL\', CHANGE twitter twitter VARCHAR(255) DEFAULT \'NULL\', CHANGE facebook facebook VARCHAR(255) DEFAULT \'NULL\', CHANGE instagram instagram VARCHAR(255) DEFAULT \'NULL\', CHANGE linkedin linkedin VARCHAR(255) DEFAULT \'NULL\', CHANGE github github VARCHAR(255) DEFAULT \'NULL\'');
    }
}
