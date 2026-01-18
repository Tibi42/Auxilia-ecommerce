<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260118115412 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD is_featured TINYINT DEFAULT 0 NOT NULL, DROP vintage, DROP region, DROP grape_variety, DROP cooperative, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE user DROP is_active');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD vintage INT DEFAULT NULL, ADD region VARCHAR(255) DEFAULT NULL, ADD grape_variety VARCHAR(255) DEFAULT NULL, ADD cooperative VARCHAR(255) DEFAULT NULL, DROP is_featured, CHANGE description description LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE user ADD is_active TINYINT DEFAULT 1 NOT NULL');
    }
}
