<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Extend well_being_score.comment to VARCHAR(500) for clinical observations
 */
final class Version20260506171529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend well_being_score.comment column to VARCHAR(500)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE well_being_score CHANGE comment comment VARCHAR(500) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE well_being_score CHANGE comment comment VARCHAR(255) NOT NULL');
    }
}
