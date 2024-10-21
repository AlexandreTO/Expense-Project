<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241021154456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS Expense');
        $this->addSql('DROP TABLE IF EXISTS Users');
        $this->addSql('CREATE TABLE Expense (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, category VARCHAR(100) NOT NULL, amount NUMERIC(10, 3) NOT NULL, date DATETIME NOT NULL, description LONGTEXT DEFAULT NULL, INDEX IDX_E287B43AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Users (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(180) NOT NULL, roles JSON NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_D5428AEDF85E0677 (username), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Expense ADD CONSTRAINT FK_E287B43AA76ED395 FOREIGN KEY (user_id) REFERENCES Users (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Expense DROP FOREIGN KEY FK_E287B43AA76ED395');
        $this->addSql('DROP TABLE Expense');
        $this->addSql('DROP TABLE Users');
    }
}
