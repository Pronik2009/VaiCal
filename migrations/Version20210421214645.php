<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210421214645 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create test user with password 555';
    }

    public function up(Schema $schema) : void
    {
        $sql = <<<SQL
INSERT INTO user (id, email, roles, password) VALUES (1,'test@test.com','{"role": "ROLE_ADMIN"}','\$argon2id\$v=19\$m=65536,t=4,p=1\$OHN0NUJVanFBOGdNMXJnSg\$RmTaU+0Yd5WRpnwjHK2090aXwWe1dFRM8VfBmX9g4fk')
SQL;
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql($sql);
        $this->addSql('CREATE TABLE year (id INT AUTO_INCREMENT NOT NULL, value INT NOT NULL, jan LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE year');
    }
}
