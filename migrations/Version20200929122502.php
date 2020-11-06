<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200929122502 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE commune DROP contenu_points_cles');
        $this->addSql('ALTER TABLE commune DROP contenu_prix_immobilier');
        $this->addSql('ALTER TABLE commune DROP tsv');
        $this->addSql('ALTER TABLE commune DROP rang');
        $this->addSql('ALTER TABLE region DROP published');
        $this->addSql('ALTER TABLE quartier DROP tsv');
        $this->addSql('ALTER TABLE departement DROP published');
        $this->addSql('ALTER TABLE departement DROP h1');
        $this->addSql('ALTER TABLE departement DROP content');
        $this->addSql('ALTER TABLE departement DROP description');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE region ADD published BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE departement ADD published BOOLEAN DEFAULT NULL');
        $this->addSql('ALTER TABLE departement ADD h1 TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE departement ADD content TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE departement ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE commune ADD contenu_points_cles TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE commune ADD contenu_prix_immobilier TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE commune ADD tsv TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE commune ADD rang INT DEFAULT NULL');
        $this->addSql('ALTER TABLE quartier ADD tsv TEXT DEFAULT NULL');
    }
}
