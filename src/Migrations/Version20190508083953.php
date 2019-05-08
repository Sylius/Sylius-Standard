<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190508083953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE sylius_promotion_coupon ADD reusable_from_cancelled_orders TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('CREATE INDEX IDX_6196A1F9A393D2FB43625D9F ON sylius_order (state, updated_at)');
        $this->addSql('ALTER TABLE sylius_product_review CHANGE title title VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_6196A1F9A393D2FB43625D9F ON sylius_order');
        $this->addSql('ALTER TABLE sylius_product_review CHANGE title title VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE sylius_promotion_coupon DROP reusable_from_cancelled_orders');
    }
}
