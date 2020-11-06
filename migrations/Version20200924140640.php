<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200924140640 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('DROP SEQUENCE region_code_seq CASCADE');
        $this->addSql('DROP SEQUENCE departement_code_seq CASCADE');
        $this->addSql('DROP SEQUENCE commune_code_seq CASCADE');
        $this->addSql('DROP SEQUENCE quartier_code_seq CASCADE');

        $this->addSql('CREATE SEQUENCE area_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE area (id SERIAL, code VARCHAR(255) NOT NULL, nom VARCHAR(255) NOT NULL, alias VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D7943D688CDE572977153098 ON area (type, code)');

        $this->addSql('INSERT INTO area (nom, alias, code, type) SELECT nom, alias, code, \'region\' as type FROM region');
        $this->addSql('INSERT INTO area (nom, alias, code, type) SELECT nom, alias, code, \'departement\' as type FROM departement');
        $this->addSql('INSERT INTO area (nom, alias, code, type) SELECT nom, alias, code, \'commune\' as type FROM commune');
        $this->addSql('INSERT INTO area (nom, alias, code, type) SELECT nom, alias, code, \'quartier\' as type FROM quartier');

        $this->addSql('ALTER TABLE region DROP CONSTRAINT region_pkey CASCADE;');
        $this->addSql('ALTER TABLE departement DROP CONSTRAINT departement_pkey CASCADE;');
        $this->addSql('ALTER TABLE quartier DROP CONSTRAINT quartier_pkey CASCADE;');
        $this->addSql('ALTER TABLE commune DROP CONSTRAINT commune_pkey CASCADE;');

        $this->addSql('ALTER TABLE region ADD id SERIAL');
        $this->addSql('ALTER TABLE departement ADD id SERIAL');
        $this->addSql('ALTER TABLE commune ADD id SERIAL');
        $this->addSql('ALTER TABLE quartier ADD id SERIAL');

        $this->addSql('UPDATE region SET id = (SELECT area.id FROM area WHERE area.type = \'region\' AND area.code = region.code)');
        $this->addSql('UPDATE departement SET id = (SELECT area.id FROM area WHERE area.type = \'departement\' AND area.code = departement.code)');
        $this->addSql('UPDATE commune SET id = (SELECT area.id FROM area WHERE area.type = \'commune\' AND area.code = commune.code)');
        $this->addSql('UPDATE quartier SET id = (SELECT area.id FROM area WHERE area.type = \'quartier\' AND area.code = quartier.code)');

        $this->addSql('ALTER TABLE region ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE departement ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE commune ALTER id DROP DEFAULT');
        $this->addSql('ALTER TABLE quartier ALTER id DROP DEFAULT');

        $this->addSql('ALTER TABLE region ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE departement ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE commune ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE quartier ADD PRIMARY KEY (id)');

        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176BF396750 FOREIGN KEY (id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B63BF396750 FOREIGN KEY (id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EEBF396750 FOREIGN KEY (id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962DBF396750 FOREIGN KEY (id) REFERENCES area (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('DROP SEQUENCE commune_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE departement_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE quartier_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE area_id_seq1 CASCADE');
        $this->addSql('DROP SEQUENCE region_id_seq CASCADE');
        $this->addSql('ALTER TABLE area ALTER id DROP DEFAULT');

        $this->addSql('ALTER TABLE departement ADD region_id INT');
        $this->addSql('ALTER TABLE commune ADD region_id INT');
        $this->addSql('ALTER TABLE commune ADD departement_id INT');
        $this->addSql('ALTER TABLE quartier ADD region_id INT');
        $this->addSql('ALTER TABLE quartier ADD departement_id INT');
        $this->addSql('ALTER TABLE quartier ADD commune_id INT');
        $this->addSql('ALTER TABLE indicator ADD commune_id INT NOT NULL');

        $this->addSql('ALTER TABLE departement ADD CONSTRAINT FK_C1765B6370E4A9D4 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EE70E4A9D4 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commune ADD CONSTRAINT FK_E2E2D1EE8837B2D3 FOREIGN KEY (departement_id) REFERENCES departement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962D70E4A9D4 FOREIGN KEY (region_id) REFERENCES region (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962D8837B2D3 FOREIGN KEY (departement_id) REFERENCES departement (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE quartier ADD CONSTRAINT FK_FEE8962DDA459572 FOREIGN KEY (commune_id) REFERENCES commune (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE indicator ADD CONSTRAINT FK_D1349DB3E5127261 FOREIGN KEY (commune_id) REFERENCES commune (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE UNIQUE INDEX UNIQ_D1349DB3131A4F722D584121 ON indicator (commune_id, kel_quartier_id)');

        $this->addSql('UPDATE departement SET region_id = (SELECT area.id FROM area WHERE area.type=\'region\' AND area.code = departement.code_region)');
        $this->addSql('UPDATE commune SET region_id = (SELECT area.id FROM area WHERE area.type=\'region\' AND area.code = commune.code_region)');
        $this->addSql('UPDATE commune SET departement_id = (SELECT area.id FROM area WHERE area.type=\'departement\' AND area.code = commune.code_departement)');
        $this->addSql('UPDATE quartier SET region_id = (SELECT area.id FROM area WHERE area.type=\'region\' AND area.code = quartier.code_region)');
        $this->addSql('UPDATE quartier SET departement_id = (SELECT area.id FROM area WHERE area.type=\'departement\' AND area.code = quartier.code_departement)');
        $this->addSql('UPDATE quartier SET commune_id = (SELECT area.id FROM area WHERE area.type=\'commune\' AND area.code = quartier.code_commune)');

        $this->addSql('ALTER TABLE region DROP code');
        $this->addSql('ALTER TABLE region DROP nom');
        $this->addSql('ALTER TABLE region DROP alias');

        $this->addSql('ALTER TABLE departement DROP code');
        $this->addSql('ALTER TABLE departement DROP code_region');
        $this->addSql('ALTER TABLE departement DROP nom');
        $this->addSql('ALTER TABLE departement DROP alias');

        $this->addSql('ALTER TABLE commune DROP code');
        $this->addSql('ALTER TABLE commune DROP code_region');
        $this->addSql('ALTER TABLE commune DROP code_departement');
        $this->addSql('ALTER TABLE commune DROP nom');
        $this->addSql('ALTER TABLE commune DROP alias');

        $this->addSql('ALTER TABLE quartier DROP code');
        $this->addSql('ALTER TABLE quartier DROP code_region');
        $this->addSql('ALTER TABLE quartier DROP code_departement');
        $this->addSql('ALTER TABLE quartier DROP code_commune');
        $this->addSql('ALTER TABLE quartier DROP nom');
        $this->addSql('ALTER TABLE quartier DROP alias');

        $this->addSql('ALTER TABLE indicator DROP commune_code');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');
    }
}
