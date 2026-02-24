<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223211533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP INDEX UNIQ_5373C96638248176, ADD INDEX IDX_5373C96638248176 (currency_id)');
        $this->addSql('ALTER TABLE user CHANGE avatar avatar VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE wish_item CHANGE price price NUMERIC(10, 2) DEFAULT NULL, CHANGE currency_id currency_id INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE country DROP INDEX IDX_5373C96638248176, ADD UNIQUE INDEX UNIQ_5373C96638248176 (currency_id)');
        $this->addSql('ALTER TABLE user CHANGE avatar avatar VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE wish_item CHANGE price price NUMERIC(10, 2) NOT NULL, CHANGE currency_id currency_id INT NOT NULL');
    }
}
