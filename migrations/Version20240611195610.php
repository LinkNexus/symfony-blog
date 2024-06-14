<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240611195610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DE6160631');
        $this->addSql('DROP INDEX UNIQ_5A8A6C8DE6160631 ON post');
        $this->addSql('ALTER TABLE post DROP restriction_id');
        $this->addSql('ALTER TABLE post_restriction ADD post_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post_restriction ADD CONSTRAINT FK_96D911E14B89032C FOREIGN KEY (post_id) REFERENCES post (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_96D911E14B89032C ON post_restriction (post_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD restriction_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DE6160631 FOREIGN KEY (restriction_id) REFERENCES post_restriction (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5A8A6C8DE6160631 ON post (restriction_id)');
        $this->addSql('ALTER TABLE post_restriction DROP FOREIGN KEY FK_96D911E14B89032C');
        $this->addSql('DROP INDEX UNIQ_96D911E14B89032C ON post_restriction');
        $this->addSql('ALTER TABLE post_restriction DROP post_id');
    }
}
