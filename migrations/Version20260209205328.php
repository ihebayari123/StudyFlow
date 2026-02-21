<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260209205328 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapitre ADD content_type VARCHAR(50) DEFAULT NULL, ADD video_url VARCHAR(500) DEFAULT NULL, ADD file_url VARCHAR(500) DEFAULT NULL, ADD links JSON DEFAULT NULL, ADD image_url VARCHAR(500) DEFAULT NULL, ADD duration_minutes INT DEFAULT NULL, CHANGE contenu contenu LONGTEXT NOT NULL, CHANGE ordre ordre INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chapitre DROP content_type, DROP video_url, DROP file_url, DROP links, DROP image_url, DROP duration_minutes, CHANGE contenu contenu VARCHAR(255) NOT NULL, CHANGE ordre ordre VARCHAR(255) NOT NULL');
    }
}
