<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021125734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Expense ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE Expense ADD CONSTRAINT FK_E287B43AA76ED395 FOREIGN KEY (user_id) REFERENCES Users (id)');
        $this->addSql('CREATE INDEX IDX_E287B43AA76ED395 ON expense (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Expense DROP FOREIGN KEY FK_E287B43AA76ED395');
        $this->addSql('DROP INDEX IDX_E287B43AA76ED395 ON Expense');
        $this->addSql('ALTER TABLE Expense DROP user_id');
    }
}
