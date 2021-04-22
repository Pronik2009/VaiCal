<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210422001833 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE year ADD feb LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD mar LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD apr LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD may LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD jun LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD jul LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD aug LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD sem LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD oct LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD nov LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\', ADD dem LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:array)\'');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE year DROP feb, DROP mar, DROP apr, DROP may, DROP jun, DROP jul, DROP aug, DROP sem, DROP oct, DROP nov, DROP dem');
    }
}
