<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210608063142 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
      $this->addSql('CREATE SEQUENCE lesson_id_seq');
      $this->addSql('ALTER SEQUENCE lesson_id_seq owner to pguser');
      $this->addSql('CREATE SEQUENCE course_id_seq');
      $this->addSql('ALTER SEQUENCE course_id_seq owner to pguser');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
    }
}
