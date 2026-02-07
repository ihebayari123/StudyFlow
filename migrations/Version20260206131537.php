<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260206131537 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chapitre (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, contenu VARCHAR(255) NOT NULL, ordre VARCHAR(255) NOT NULL, course_id INT NOT NULL, INDEX IDX_8C62B025591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_FDCA8C9CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, type VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_3BAE0AA7A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prix INT NOT NULL, image VARCHAR(255) NOT NULL, type_categorie_id_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_29A5EC277D08E7F3 (type_categorie_id_id), INDEX IDX_29A5EC27A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, texte VARCHAR(255) NOT NULL, choix_a VARCHAR(255) NOT NULL, choix_b VARCHAR(255) NOT NULL, choix_c VARCHAR(255) NOT NULL, choix_d VARCHAR(255) NOT NULL, bonne_reponse VARCHAR(1) NOT NULL, indice VARCHAR(255) DEFAULT NULL, quiz_id INT NOT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, duree INT NOT NULL, date_creation DATETIME NOT NULL, user_id INT NOT NULL, course_id INT NOT NULL, INDEX IDX_A412FA92A76ED395 (user_id), INDEX IDX_A412FA92591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, nom_sponsor VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, montant INT NOT NULL, event_titre_id INT NOT NULL, INDEX IDX_818CC9D493A3478E (event_titre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE stress_survey (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, sleep_hours INT NOT NULL, study_hours INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9C770227A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE type_categorie (id INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, statut_compte VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE well_being_score (id INT AUTO_INCREMENT NOT NULL, recommendation VARCHAR(255) NOT NULL, action_plan VARCHAR(255) NOT NULL, comment VARCHAR(255) NOT NULL, score INT NOT NULL, survey_id INT NOT NULL, UNIQUE INDEX UNIQ_8EB62E33B3FE509D (survey_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B025591CC992 FOREIGN KEY (course_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC277D08E7F3 FOREIGN KEY (type_categorie_id_id) REFERENCES type_categorie (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D493A3478E FOREIGN KEY (event_titre_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE stress_survey ADD CONSTRAINT FK_9C770227A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE well_being_score ADD CONSTRAINT FK_8EB62E33B3FE509D FOREIGN KEY (survey_id) REFERENCES stress_survey (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B025591CC992');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CA76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC277D08E7F3');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A76ED395');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92A76ED395');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D493A3478E');
        $this->addSql('ALTER TABLE stress_survey DROP FOREIGN KEY FK_9C770227A76ED395');
        $this->addSql('ALTER TABLE well_being_score DROP FOREIGN KEY FK_8EB62E33B3FE509D');
        $this->addSql('DROP TABLE chapitre');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE sponsor');
        $this->addSql('DROP TABLE stress_survey');
        $this->addSql('DROP TABLE type_categorie');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE well_being_score');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
