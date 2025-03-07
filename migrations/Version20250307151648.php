<?php

declare(strict_types=1);

namespace App;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250307151648 extends AbstractMigration {
    public function getDescription(): string {
        return 'Added note field to sylius_order';
    }

    public function up(Schema $schema): void {
        $this->addSql('ALTER TABLE sylius_order ADD note VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void {
        $this->addSql('ALTER TABLE sylius_order DROP note');
    }
}
