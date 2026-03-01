<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228235054 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE utilisateur ADD email_verified TINYINT DEFAULT 0 NOT NULL, ADD email_verification_token VARCHAR(64) DEFAULT NULL, ADD email_verification_token_expires_at DATETIME DEFAULT NULL, ADD email_verified_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1D1C63B3C4995C67 ON utilisateur (email_verification_token)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_1D1C63B3C4995C67 ON utilisateur');
        $this->addSql('ALTER TABLE utilisateur DROP email_verified, DROP email_verification_token, DROP email_verification_token_expires_at, DROP email_verified_at');
    }
}
