<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Sync schema: consultation + medecin column changes
 */
final class Version20260506134704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update consultation and medecin columns to match entity constraints';
    }

    public function up(Schema $schema): void
    {
        // consultation: genre VARCHAR(20), niveau VARCHAR(30), FK NOT NULL
        $this->addSql('ALTER TABLE consultation
            CHANGE genre genre VARCHAR(20) NOT NULL,
            CHANGE niveau niveau VARCHAR(30) NOT NULL,
            CHANGE medecin_id medecin_id INT NOT NULL,
            CHANGE stress_survey_id stress_survey_id INT NOT NULL');

        // medecin: nom/prenom VARCHAR(100), email VARCHAR(180) UNIQUE, disponibilite TINYINT
        $this->addSql('ALTER TABLE medecin
            CHANGE nom nom VARCHAR(100) NOT NULL,
            CHANGE prenom prenom VARCHAR(100) NOT NULL,
            CHANGE email email VARCHAR(180) NOT NULL,
            CHANGE disponibilite disponibilite TINYINT(1) NOT NULL');

        // unique index on medecin.email
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BDA53C6E7927C74 ON medecin (email)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX UNIQ_1BDA53C6E7927C74 ON medecin');

        $this->addSql('ALTER TABLE consultation
            CHANGE genre genre VARCHAR(255) NOT NULL,
            CHANGE niveau niveau VARCHAR(255) NOT NULL,
            CHANGE medecin_id medecin_id INT DEFAULT NULL,
            CHANGE stress_survey_id stress_survey_id INT DEFAULT NULL');

        $this->addSql('ALTER TABLE medecin
            CHANGE nom nom VARCHAR(255) NOT NULL,
            CHANGE prenom prenom VARCHAR(255) NOT NULL,
            CHANGE email email VARCHAR(255) NOT NULL,
            CHANGE disponibilite disponibilite VARCHAR(255) NOT NULL');
    }
}
