<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200301170604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE sylius_channel_countries (channel_id INT NOT NULL, country_id INT NOT NULL, INDEX IDX_D96E51AE72F5A1AA (channel_id), INDEX IDX_D96E51AEF92F3E70 (country_id), PRIMARY KEY(channel_id, country_id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_channel_countries ADD CONSTRAINT FK_D96E51AE72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_channel_countries ADD CONSTRAINT FK_D96E51AEF92F3E70 FOREIGN KEY (country_id) REFERENCES sylius_country (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE sylius_shipment ADD shipped_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_channel ADD menu_taxon_id INT DEFAULT NULL, DROP type');
        $this->addSql('ALTER TABLE sylius_channel ADD CONSTRAINT FK_16C8119EF242B1E6 FOREIGN KEY (menu_taxon_id) REFERENCES sylius_taxon (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_16C8119EF242B1E6 ON sylius_channel (menu_taxon_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE sylius_channel_countries');
        $this->addSql('ALTER TABLE sylius_channel DROP FOREIGN KEY FK_16C8119EF242B1E6');
        $this->addSql('DROP INDEX IDX_16C8119EF242B1E6 ON sylius_channel');
        $this->addSql('ALTER TABLE sylius_channel ADD type VARCHAR(255) CHARACTER SET utf8 DEFAULT NULL COLLATE `utf8_unicode_ci`, DROP menu_taxon_id');
        $this->addSql('ALTER TABLE sylius_shipment DROP shipped_at');
    }
}
