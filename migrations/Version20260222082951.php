<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260222082951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, date_de_consultation DATETIME NOT NULL, motif VARCHAR(255) NOT NULL, genre VARCHAR(255) NOT NULL, niveau VARCHAR(255) NOT NULL, medecin_id INT DEFAULT NULL, stress_survey_id INT DEFAULT NULL, INDEX IDX_964685A64F31A84 (medecin_id), INDEX IDX_964685A6A71413D4 (stress_survey_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE medecin (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, disponibilite VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE quiz_attempt (id INT AUTO_INCREMENT NOT NULL, score_questions INT NOT NULL, score_points INT NOT NULL, total_questions INT NOT NULL, started_at DATETIME NOT NULL, finished_at DATETIME DEFAULT NULL, user_id INT NOT NULL, quiz_id INT NOT NULL, INDEX IDX_AB6AFC6A76ED395 (user_id), INDEX IDX_AB6AFC6853CD175 (quiz_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A6A71413D4 FOREIGN KEY (stress_survey_id) REFERENCES stress_survey (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6A76ED395 FOREIGN KEY (user_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE quiz_attempt ADD CONSTRAINT FK_AB6AFC6853CD175 FOREIGN KEY (quiz_id) REFERENCES quiz (id)');
        $this->addSql('ALTER TABLE cours ADD ai_time_estimation VARCHAR(100) DEFAULT NULL, ADD ai_objective LONGTEXT DEFAULT NULL, ADD ai_competences LONGTEXT DEFAULT NULL, ADD ai_min_chapters INT DEFAULT NULL, ADD ai_chapters_list JSON DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE question ADD type VARCHAR(255) NOT NULL, ADD bonne_reponse_choix VARCHAR(1) DEFAULT NULL, ADD reponse_attendue LONGTEXT DEFAULT NULL, ADD bonne_reponse_bool TINYINT DEFAULT NULL, CHANGE choix_a choix_a VARCHAR(255) DEFAULT NULL, CHANGE choix_b choix_b VARCHAR(255) DEFAULT NULL, CHANGE choix_c choix_c VARCHAR(255) DEFAULT NULL, CHANGE choix_d choix_d VARCHAR(255) DEFAULT NULL, CHANGE bonne_reponse bonne_reponse VARCHAR(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A6A71413D4');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6A76ED395');
        $this->addSql('ALTER TABLE quiz_attempt DROP FOREIGN KEY FK_AB6AFC6853CD175');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE medecin');
        $this->addSql('DROP TABLE quiz_attempt');
        $this->addSql('ALTER TABLE cours DROP ai_time_estimation, DROP ai_objective, DROP ai_competences, DROP ai_min_chapters, DROP ai_chapters_list, CHANGE image image VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE question DROP type, DROP bonne_reponse_choix, DROP reponse_attendue, DROP bonne_reponse_bool, CHANGE choix_a choix_a VARCHAR(255) NOT NULL, CHANGE choix_b choix_b VARCHAR(255) NOT NULL, CHANGE choix_c choix_c VARCHAR(255) NOT NULL, CHANGE choix_d choix_d VARCHAR(255) NOT NULL, CHANGE bonne_reponse bonne_reponse VARCHAR(1) NOT NULL');
    }
}
