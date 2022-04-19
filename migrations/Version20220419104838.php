<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220419104838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dish ADD created_on DATETIME NOT NULL, ADD updated_on DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_957D8CB85E237E06 ON dish (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_957D8CB85E237E06 ON dish');
        $this->addSql('ALTER TABLE dish DROP created_on, DROP updated_on');
    }
}
