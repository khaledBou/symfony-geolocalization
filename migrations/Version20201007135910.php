<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201007135910 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commune ADD arrondissement BOOLEAN');
        $this->addSql('UPDATE commune SET arrondissement = false');
        $this->addSql('ALTER TABLE commune ALTER COLUMN arrondissement SET NOT NULL');
        $this->addSql('UPDATE commune SET arrondissement = true WHERE id IN (22727, 10467, 22591, 188, 237, 406, 22409, 751, 3973, 22398, 22679, 31183, 31188, 22629, 31261, 36029, 36030, 36033, 31234, 36034, 330, 407, 244, 22754, 216, 676, 22450, 384, 22451, 22630, 22631, 31219, 22632, 31235, 22714, 22715, 22716, 137, 31247, 168, 299, 31141, 22528, 31181, 31182)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE commune DROP arrondissement');
    }
}
