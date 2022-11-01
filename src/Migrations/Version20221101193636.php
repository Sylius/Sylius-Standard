<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221101193636 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $sql = <<<SQL
CREATE TABLE sessions (
    sess_id VARBINARY(128) NOT NULL PRIMARY KEY,
    sess_data BLOB NOT NULL,
    sess_lifetime INTEGER UNSIGNED NOT NULL,
    sess_time INTEGER UNSIGNED NOT NULL,
    INDEX sessions_sess_lifetime_idx (sess_lifetime)
) COLLATE utf8mb4_bin, ENGINE = InnoDB;
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        $sql = <<<SQL
DROP TABLE sessions;
SQL;
        $this->addSql($sql);
    }
}
