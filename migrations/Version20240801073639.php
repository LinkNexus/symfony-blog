<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240801073639 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment_reaction (id INT AUTO_INCREMENT NOT NULL, comment_id INT NOT NULL, owner_id INT NOT NULL, type VARCHAR(255) NOT NULL, INDEX IDX_B99364F1F8697D13 (comment_id), INDEX IDX_B99364F17E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment_reaction ADD CONSTRAINT FK_B99364F1F8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE comment_reaction ADD CONSTRAINT FK_B99364F17E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F1F8697D13');
        $this->addSql('ALTER TABLE comment_reaction DROP FOREIGN KEY FK_B99364F17E3C61F9');
        $this->addSql('DROP TABLE comment_reaction');
    }
}
