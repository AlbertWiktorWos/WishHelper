<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260228121558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE wish_item_recommendation (id INT AUTO_INCREMENT NOT NULL, score SMALLINT NOT NULL, is_seen TINYINT NOT NULL, created_at DATETIME NOT NULL, notified_at DATETIME DEFAULT NULL, wish_item_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1CAE3CFEF95E77C4 (wish_item_id), INDEX IDX_1CAE3CFEA76ED395 (user_id), UNIQUE INDEX uniq_wish_user (wish_item_id, user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE wish_item_recommendation ADD CONSTRAINT FK_1CAE3CFEF95E77C4 FOREIGN KEY (wish_item_id) REFERENCES wish_item (id)');
        $this->addSql('ALTER TABLE wish_item_recommendation ADD CONSTRAINT FK_1CAE3CFEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE wish_item_recommendation DROP FOREIGN KEY FK_1CAE3CFEF95E77C4');
        $this->addSql('ALTER TABLE wish_item_recommendation DROP FOREIGN KEY FK_1CAE3CFEA76ED395');
        $this->addSql('DROP TABLE wish_item_recommendation');
    }
}
