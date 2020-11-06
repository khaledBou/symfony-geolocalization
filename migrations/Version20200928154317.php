<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200928154317 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('ALTER TABLE indicator DROP CONSTRAINT fk_d1349db3e5127261');
        $this->addSql('DROP INDEX uniq_d1349db3131a4f722d584121');
        $this->addSql('ALTER TABLE indicator RENAME COLUMN commune_id TO area_id');
        $this->addSql('ALTER TABLE indicator ADD CONSTRAINT FK_D1349DB3BD0F409C FOREIGN KEY (area_id) REFERENCES area (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D1349DB3BD0F409C ON indicator (area_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D1349DB3BD0F409C2D584121 ON indicator (area_id, kel_quartier_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE indicator DROP CONSTRAINT FK_D1349DB3BD0F409C');
        $this->addSql('DROP INDEX UNIQ_D1349DB3BD0F409C2D584121');
        $this->addSql('ALTER TABLE indicator RENAME COLUMN area_id TO commune_id');
        $this->addSql('ALTER TABLE indicator ADD CONSTRAINT fk_d1349db3e5127261 FOREIGN KEY (commune_id) REFERENCES commune (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_d1349db3131a4f722d584121 ON indicator (commune_id, kel_quartier_id)');
        $this->addSql('CREATE INDEX IDX_D1349DB3131A4F72 ON indicator (commune_id)');
    }
}
