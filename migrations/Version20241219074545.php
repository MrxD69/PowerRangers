<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241219074545 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande_db (id INT AUTO_INCREMENT NOT NULL, freelancer_id INT NOT NULL, prix NUMERIC(10, 2) NOT NULL, description LONGTEXT NOT NULL, technologie VARCHAR(255) NOT NULL, datefin DATETIME NOT NULL, status VARCHAR(100) DEFAULT NULL, INDEX IDX_DE27F52C8545BDF5 (freelancer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, date DATE NOT NULL, nbre_dispo INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, evenement_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, age INT NOT NULL, email VARCHAR(255) NOT NULL, num_telephone INT NOT NULL, INDEX IDX_D79F6B11FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_db (id INT AUTO_INCREMENT NOT NULL, client_id INT NOT NULL, domaine VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_4AA0C4E419EB6921 (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE projet (id INT AUTO_INCREMENT NOT NULL, domaine VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, project_db_id INT DEFAULT NULL, commande_db_id INT DEFAULT NULL, complainant_id INT NOT NULL, against_user_id INT NOT NULL, date DATE NOT NULL, etat VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, INDEX IDX_CE606404B1EA0CA4 (project_db_id), INDEX IDX_CE6064049BD7DAF9 (commande_db_id), INDEX IDX_CE6064044C422040 (complainant_id), INDEX IDX_CE606404A70F11BA (against_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, user_id INT DEFAULT NULL, date DATE NOT NULL, message VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_5FB6DEC72D6BA2D9 (reclamation_id), INDEX IDX_5FB6DEC7A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, biography LONGTEXT DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, skills JSON DEFAULT NULL, work_experience JSON DEFAULT NULL, phone VARCHAR(15) DEFAULT NULL, profile_picture VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, facebook VARCHAR(255) DEFAULT NULL, instagram VARCHAR(255) DEFAULT NULL, linkedin VARCHAR(255) DEFAULT NULL, github VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE commande_db ADD CONSTRAINT FK_DE27F52C8545BDF5 FOREIGN KEY (freelancer_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE project_db ADD CONSTRAINT FK_4AA0C4E419EB6921 FOREIGN KEY (client_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404B1EA0CA4 FOREIGN KEY (project_db_id) REFERENCES project_db (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064049BD7DAF9 FOREIGN KEY (commande_db_id) REFERENCES commande_db (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064044C422040 FOREIGN KEY (complainant_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE606404A70F11BA FOREIGN KEY (against_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id)');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE commande_db DROP FOREIGN KEY FK_DE27F52C8545BDF5');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11FD02F13');
        $this->addSql('ALTER TABLE project_db DROP FOREIGN KEY FK_4AA0C4E419EB6921');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404B1EA0CA4');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064049BD7DAF9');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064044C422040');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE606404A70F11BA');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D9');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC7A76ED395');
        $this->addSql('DROP TABLE commande_db');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE project_db');
        $this->addSql('DROP TABLE projet');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
