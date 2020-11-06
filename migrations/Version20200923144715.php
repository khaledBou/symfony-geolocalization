<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200923144715 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SEQUENCE indicator_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE indicator (id INT NOT NULL, commune_code VARCHAR(255) NOT NULL, kel_quartier_id VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, type VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_D1349DB3E5127261 ON indicator (commune_code)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D1349DB3E51272612D584121 ON indicator (commune_code, kel_quartier_id)');
        $this->addSql('CREATE TABLE int_indicator (id INT NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE text_indicator (id INT NOT NULL, value TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE ratio_indicator (id INT NOT NULL, value INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE string_indicator (id INT NOT NULL, value VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE indicator ADD CONSTRAINT FK_D1349DB3E5127261 FOREIGN KEY (commune_code) REFERENCES commune (code) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE int_indicator ADD CONSTRAINT FK_76A85C8BBF396750 FOREIGN KEY (id) REFERENCES indicator (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE text_indicator ADD CONSTRAINT FK_223BAE77BF396750 FOREIGN KEY (id) REFERENCES indicator (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE ratio_indicator ADD CONSTRAINT FK_1B637028BF396750 FOREIGN KEY (id) REFERENCES indicator (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE string_indicator ADD CONSTRAINT FK_D3FC3C5ABF396750 FOREIGN KEY (id) REFERENCES indicator (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE commune DROP data_prix');
        $this->addSql('ALTER TABLE commune DROP data_portrait');
        $this->addSql('ALTER TABLE quartier DROP data_prix');
        $this->addSql('ALTER TABLE quartier DROP data_portrait');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE int_indicator DROP CONSTRAINT FK_76A85C8BBF396750');
        $this->addSql('ALTER TABLE text_indicator DROP CONSTRAINT FK_223BAE77BF396750');
        $this->addSql('ALTER TABLE ratio_indicator DROP CONSTRAINT FK_1B637028BF396750');
        $this->addSql('ALTER TABLE string_indicator DROP CONSTRAINT FK_D3FC3C5ABF396750');
        $this->addSql('DROP SEQUENCE indicator_id_seq CASCADE');
        $this->addSql('DROP TABLE indicator');
        $this->addSql('DROP TABLE int_indicator');
        $this->addSql('DROP TABLE text_indicator');
        $this->addSql('DROP TABLE ratio_indicator');
        $this->addSql('DROP TABLE string_indicator');
        $this->addSql('ALTER TABLE commune ADD data_prix JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE commune ADD data_portrait JSONB DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN commune.data_prix IS \'(DC2Type:jsonb)\'');
        $this->addSql('COMMENT ON COLUMN commune.data_portrait IS \'(DC2Type:jsonb)\'');
        $this->addSql('ALTER TABLE quartier ADD data_prix JSONB DEFAULT NULL');
        $this->addSql('ALTER TABLE quartier ADD data_portrait JSONB DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN quartier.data_prix IS \'(DC2Type:jsonb)\'');
        $this->addSql('COMMENT ON COLUMN quartier.data_portrait IS \'(DC2Type:jsonb)\'');
    }
}
