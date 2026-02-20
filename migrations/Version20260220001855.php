<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260220001855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chapitre (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, ordre INT NOT NULL, content_type VARCHAR(50) DEFAULT NULL, video_url VARCHAR(500) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, links JSON DEFAULT NULL, image_url VARCHAR(500) DEFAULT NULL, duration_minutes INT DEFAULT NULL, course_id INT NOT NULL, INDEX IDX_8C62B025591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE chapitre_version (id INT AUTO_INCREMENT NOT NULL, version_number INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, ordre INT NOT NULL, content_type VARCHAR(50) DEFAULT NULL, video_url VARCHAR(500) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, links JSON DEFAULT NULL, image_url VARCHAR(500) DEFAULT NULL, duration_minutes INT DEFAULT NULL, created_at DATETIME NOT NULL, change_description LONGTEXT DEFAULT NULL, changes_detected JSON DEFAULT NULL, modification_percentage DOUBLE PRECISION DEFAULT NULL, modified_by VARCHAR(255) DEFAULT NULL, chapitre_id INT NOT NULL, INDEX IDX_6B9DE2EE1FBEEF7B (chapitre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE cours (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_FDCA8C9CA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, type VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, user_id INT NOT NULL, INDEX IDX_3BAE0AA7A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, message VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, is_read TINYINT NOT NULL, created_at DATETIME NOT NULL, user_id INT DEFAULT NULL, INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prix INT NOT NULL, image VARCHAR(255) NOT NULL, type_categorie_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_29A5EC273BB65D28 (type_categorie_id), INDEX IDX_29A5EC27A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE question (id INT AUTO_INCREMENT NOT NULL, texte VARCHAR(255) NOT NULL, niveau VARCHAR(20) NOT NULL, indice VARCHAR(255) DEFAULT NULL, quiz_id INT NOT NULL, type VARCHAR(255) NOT NULL, choix_a VARCHAR(255) DEFAULT NULL, choix_b VARCHAR(255) DEFAULT NULL, choix_c VARCHAR(255) DEFAULT NULL, choix_d VARCHAR(255) DEFAULT NULL, bonne_reponse_choix VARCHAR(1) DEFAULT NULL, bonne_reponse_bool TINYINT DEFAULT NULL, reponse_attendue LONGTEXT DEFAULT NULL, INDEX IDX_B6F7494E853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, duree INT NOT NULL, date_creation DATETIME NOT NULL, course_id INT NOT NULL, INDEX IDX_A412FA92591CC992 (course_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, score_questions INT NOT NULL, score_points INT NOT NULL, total_questions INT NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_AB6AFC6A76ED395 (user_id), INDEX IDX_AB6AFC6853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE sponsor (id INT AUTO_INCREMENT NOT NULL, nom_sponsor VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, montant INT NOT NULL, event_titre_id INT NOT NULL, INDEX IDX_818CC9D493A3478E (event_titre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE stress_survey (id INT AUTO_INCREMENT NOT NULL, date DATE NOT NULL, sleep_hours INT NOT NULL, study_hours INT NOT NULL, user_id INT NOT NULL, INDEX IDX_9C770227A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE type_categorie (id INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL, statut_compte VARCHAR(255) NOT NULL, login_frequency INT NOT NULL, last_login DATETIME DEFAULT NULL, failed_login_attempts INT NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE well_being_score (id INT AUTO_INCREMENT NOT NULL, recommendation VARCHAR(255) NOT NULL, action_plan VARCHAR(255) NOT NULL, comment VARCHAR(255) NOT NULL, score INT NOT NULL, survey_id INT NOT NULL, UNIQUE INDEX UNIQ_8EB62E33B3FE509D (survey_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chapitre ADD CONSTRAINT FK_8C62B025591CC992 FOREIGN KEY (course_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE chapitre_version ADD CONSTRAINT FK_6B9DE2EE1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES chapitre (id)');
        $this->addSql('ALTER TABLE cours ADD CONSTRAINT FK_FDCA8C9CA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC273BB65D28 FOREIGN KEY (type_categorie_id) REFERENCES type_categorie (id)');
        $this->addSql('ALTER TABLE produit ADD CONSTRAINT FK_29A5EC27A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE question ADD CONSTRAINT FK_B6F7494E853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE quiz ADD CONSTRAINT FK_A412FA92591CC992 FOREIGN KEY (course_id) REFERENCES cours (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE sponsor ADD CONSTRAINT FK_818CC9D493A3478E FOREIGN KEY (event_titre_id) REFERENCES event (id)');
        $this->addSql('ALTER TABLE stress_survey ADD CONSTRAINT FK_9C770227A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE well_being_score ADD CONSTRAINT FK_8EB62E33B3FE509D FOREIGN KEY (survey_id) REFERENCES stress_survey (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapitre DROP FOREIGN KEY FK_8C62B025591CC992');
        $this->addSql('ALTER TABLE chapitre_version DROP FOREIGN KEY FK_6B9DE2EE1FBEEF7B');
        $this->addSql('ALTER TABLE cours DROP FOREIGN KEY FK_FDCA8C9CA76ED395');
        $this->addSql('ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7A76ED395');
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC273BB65D28');
        $this->addSql('ALTER TABLE produit DROP FOREIGN KEY FK_29A5EC27A76ED395');
        $this->addSql('ALTER TABLE question DROP FOREIGN KEY FK_B6F7494E853CD175');
        $this->addSql('ALTER TABLE quiz DROP FOREIGN KEY FK_A412FA92591CC992');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6A76ED395');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6853CD175');
        $this->addSql('ALTER TABLE sponsor DROP FOREIGN KEY FK_818CC9D493A3478E');
        $this->addSql('ALTER TABLE stress_survey DROP FOREIGN KEY FK_9C770227A76ED395');
        $this->addSql('ALTER TABLE well_being_score DROP FOREIGN KEY FK_8EB62E33B3FE509D');
        $this->addSql('DROP TABLE chapitre');
        $this->addSql('DROP TABLE chapitre_version');
        $this->addSql('DROP TABLE cours');
        $this->addSql('DROP TABLE event');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE produit');
        $this->addSql('DROP TABLE question');
        $this->addSql('DROP TABLE quiz');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('DROP TABLE sponsor');
        $this->addSql('DROP TABLE stress_survey');
        $this->addSql('DROP TABLE type_categorie');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE well_being_score');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
