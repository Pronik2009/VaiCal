<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211029084158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create test user with password 555';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE new_city (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, latitude VARCHAR(255) NOT NULL, longitude VARCHAR(255) NOT NULL, user_agent VARCHAR(255) NOT NULL, ip VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE year (id INT AUTO_INCREMENT NOT NULL, city_id INT NOT NULL, value INT NOT NULL, jan LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', feb LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', mar LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', apr LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', may LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', jun LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', jul LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', aug LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', sem LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', oct LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', nov LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', dem LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', INDEX IDX_BB8273378BAC62AF (city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE year ADD CONSTRAINT FK_BB8273378BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
        $sql = <<<SQL
INSERT INTO user (id, email, roles, password) VALUES (1,'test@test.com','{"role": "ROLE_ADMIN"}','\$argon2id\$v=19\$m=65536,t=4,p=1\$OHN0NUJVanFBOGdNMXJnSg\$RmTaU+0Yd5WRpnwjHK2090aXwWe1dFRM8VfBmX9g4fk')
SQL;
        $this->addSql($sql);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE year DROP FOREIGN KEY FK_BB8273378BAC62AF');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE new_city');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE year');
    }
}
