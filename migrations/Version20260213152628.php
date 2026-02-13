<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260213152628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chapitre_version (id INT AUTO_INCREMENT NOT NULL, version_number INT NOT NULL, titre VARCHAR(255) NOT NULL, contenu LONGTEXT NOT NULL, ordre INT NOT NULL, content_type VARCHAR(50) DEFAULT NULL, video_url VARCHAR(500) DEFAULT NULL, file_name VARCHAR(255) DEFAULT NULL, links JSON DEFAULT NULL, image_url VARCHAR(500) DEFAULT NULL, duration_minutes INT DEFAULT NULL, created_at DATETIME NOT NULL, change_description LONGTEXT DEFAULT NULL, changes_detected JSON DEFAULT NULL, modification_percentage DOUBLE PRECISION DEFAULT NULL, modified_by VARCHAR(255) DEFAULT NULL, chapitre_id INT NOT NULL, INDEX IDX_6B9DE2EE1FBEEF7B (chapitre_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE chapitre_version ADD CONSTRAINT FK_6B9DE2EE1FBEEF7B FOREIGN KEY (chapitre_id) REFERENCES chapitre (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapitre_version DROP FOREIGN KEY FK_6B9DE2EE1FBEEF7B');
        $this->addSql('DROP TABLE chapitre_version');
    }
}
