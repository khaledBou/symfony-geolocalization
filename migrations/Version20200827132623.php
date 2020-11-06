<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200827132623 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE commune_code_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE region_code_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE quartier_code_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE departement_code_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE commune (code VARCHAR(255) NOT NULL, code_region VARCHAR(255) DEFAULT NULL, code_departement VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, surface INT DEFAULT NULL, population INT DEFAULT NULL, code_postal INT DEFAULT NULL, codes_postaux JSONB NOT NULL, arrondissements BOOLEAN NOT NULL, centre JSONB DEFAULT NULL, postgis_centre geometry(POINT, 0) DEFAULT NULL, contour JSONB DEFAULT NULL, postgis_contour geometry(MULTIPOLYGON, 0) DEFAULT NULL, contenu_points_cles TEXT DEFAULT NULL, contenu_prix_immobilier TEXT DEFAULT NULL, tsv TEXT DEFAULT NULL, rang INT DEFAULT NULL, data_prix JSONB DEFAULT NULL, data_portrait JSONB DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX idx_e2e2d1ee8837b2d3 ON commune (code_departement)');
        $this->addSql('CREATE INDEX idx_e2e2d1ee70e4a9d4 ON commune (code_region)');
        $this->addSql('CREATE TABLE region (code VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, published BOOLEAN DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE UNIQUE INDEX uniq_f62f176e16c6b94 ON region (alias)');
        $this->addSql('CREATE UNIQUE INDEX uniq_f62f1766c6e55b5 ON region (nom)');
        $this->addSql('CREATE TABLE quartier (code VARCHAR(255) NOT NULL, code_region VARCHAR(255) DEFAULT NULL, code_departement VARCHAR(255) DEFAULT NULL, code_commune VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, population INT DEFAULT NULL, centre JSONB DEFAULT NULL, postgis_centre geometry(POINT, 0) DEFAULT NULL, contour JSONB DEFAULT NULL, postgis_contour geometry(MULTIPOLYGON, 0) DEFAULT NULL, tsv TEXT DEFAULT NULL, data_prix JSONB DEFAULT NULL, data_portrait JSONB DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX idx_fee8962dda459572 ON quartier (code_commune)');
        $this->addSql('CREATE INDEX idx_fee8962d70e4a9d4 ON quartier (code_region)');
        $this->addSql('CREATE INDEX idx_fee8962d8837b2d3 ON quartier (code_departement)');
        $this->addSql('CREATE TABLE departement (code VARCHAR(255) NOT NULL, code_region VARCHAR(255) DEFAULT NULL, nom VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, published BOOLEAN DEFAULT NULL, centre JSONB DEFAULT NULL, postgis_centre geometry(POINT, 0) DEFAULT NULL, contour JSONB DEFAULT NULL, postgis_contour geometry(MULTIPOLYGON, 0) DEFAULT NULL, h1 TEXT DEFAULT NULL, content TEXT DEFAULT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(code))');
        $this->addSql('CREATE INDEX idx_c1765b6370e4a9d4 ON departement (code_region)');
        $this->addSql('CREATE UNIQUE INDEX uniq_c1765b63e16c6b94 ON departement (alias)');
        $this->addSql('CREATE UNIQUE INDEX uniq_c1765b636c6e55b5 ON departement (nom)');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EE70E4A9D4 FOREIGN KEY (code_region) REFERENCES region (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EE8837B2D3 FOREIGN KEY (code_departement) REFERENCES departement (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962D70E4A9D4 FOREIGN KEY (code_region) REFERENCES region (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962D8837B2D3 FOREIGN KEY (code_departement) REFERENCES departement (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962DDA459572 FOREIGN KEY (code_commune) REFERENCES commune (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B6370E4A9D4 FOREIGN KEY (code_region) REFERENCES region (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE quartier DROP CONSTRAINT FK_FEE8962DDA459572');
        $this->addSql('ALTER TABLE commune DROP CONSTRAINT FK_E2E2D1EE70E4A9D4');
        $this->addSql('ALTER TABLE quartier DROP CONSTRAINT FK_FEE8962D70E4A9D4');
        $this->addSql('ALTER TABLE departement DROP CONSTRAINT FK_C1765B6370E4A9D4');
        $this->addSql('ALTER TABLE commune DROP CONSTRAINT FK_E2E2D1EE8837B2D3');
        $this->addSql('ALTER TABLE quartier DROP CONSTRAINT FK_FEE8962D8837B2D3');
        $this->addSql('DROP SEQUENCE commune_code_seq CASCADE');
        $this->addSql('DROP SEQUENCE region_code_seq CASCADE');
        $this->addSql('DROP SEQUENCE quartier_code_seq CASCADE');
        $this->addSql('DROP SEQUENCE departement_code_seq CASCADE');
        $this->addSql('DROP TABLE commune');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE quartier');
        $this->addSql('DROP TABLE departement');
    }
}
