<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201006134352 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE region ADD centre JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE region ADD postgis_centre geometry(POINT, 0) DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN region.centre IS \'(DC2Type:jsonb)\'');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["-61.581247", "16.246487"]}\' WHERE id = 1');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["-61.002832", "14.643223"]}\' WHERE id = 2');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["-53.200899", "3.874447"]}\' WHERE id = 3');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["55.551983", "-21.142797"]}\' WHERE id = 4');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["45.157832", "-12.821912"]}\' WHERE id = 5');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["2.472265", "48.760974"]}\' WHERE id = 6');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["1.735711", "47.479107"]}\' WHERE id = 7');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["4.806932", "47.162533"]}\' WHERE id = 8');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["0.093934", "49.131348"]}\' WHERE id = 9');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["2.828253", "49.922341"]}\' WHERE id = 10');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["5.543460", "48.664227"]}\' WHERE id = 11');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["-0.827412", "47.538036"]}\' WHERE id = 12');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["-2.513086", "48.201241"]}\' WHERE id = 13');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["0.001271", "45.241745"]}\' WHERE id = 14');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["2.301345", "43.779069"]}\' WHERE id = 15');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["4.732400", "45.376235"]}\' WHERE id = 16');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["6.182864", "43.922466"]}\' WHERE id = 17');
        $this->addSql('UPDATE region SET centre=\'{"type": "Point", "coordinates": ["9.142127", "42.096394"]}\' WHERE id = 18');
        $this->addSql('UPDATE region SET postgis_centre = ST_GeomFromGeoJSON(centre)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE region DROP centre');
        $this->addSql('ALTER TABLE region DROP postgis_centre');
    }
}
