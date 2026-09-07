<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260907120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create sylius_order_note table for dedicated order notes storage.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE sylius_order_note (
            id INT AUTO_INCREMENT NOT NULL,
            order_id INT NOT NULL,
            note LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_2B5D884C8D9F6D38 (order_id),
            PRIMARY KEY(id),
            CONSTRAINT FK_2B5D884C8D9F6D38 FOREIGN KEY (order_id) REFERENCES sylius_order (id) ON DELETE CASCADE
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE sylius_order_note');
    }
}
