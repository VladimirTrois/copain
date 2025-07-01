<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250630175507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE business_user (id SERIAL NOT NULL, business_id  INT NOT NULL, user_id INT NOT NULL, responsibilities JSON NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_DD08D444A89DB457 ON business_user (business_id)');
        $this->addSql('CREATE INDEX IDX_DD08D444A76ED395 ON business_user (user_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_USER_BUSINESS ON business_user (user_id, business_id)');
        $this->addSql('ALTER TABLE business_user ADD CONSTRAINT FK_DD08D444A89DB457 FOREIGN KEY (business_id) REFERENCES business (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE business_user ADD CONSTRAINT FK_DD08D444A76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE business_user DROP CONSTRAINT FK_DD08D444A89DB457');
        $this->addSql('ALTER TABLE business_user DROP CONSTRAINT FK_DD08D444A76ED395');
        $this->addSql('DROP TABLE business_user');
    }
}
