<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251006201507 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE teacher_subject (teacher_id INT NOT NULL, subject_id INT NOT NULL, PRIMARY KEY(teacher_id, subject_id))');
        $this->addSql('CREATE INDEX IDX_360CB33B41807E1D ON teacher_subject (teacher_id)');
        $this->addSql('CREATE INDEX IDX_360CB33B23EDC87 ON teacher_subject (subject_id)');
        $this->addSql('ALTER TABLE teacher_subject ADD CONSTRAINT FK_360CB33B41807E1D FOREIGN KEY (teacher_id) REFERENCES teachers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE teacher_subject ADD CONSTRAINT FK_360CB33B23EDC87 FOREIGN KEY (subject_id) REFERENCES subjects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE teacher_subject DROP CONSTRAINT FK_360CB33B41807E1D');
        $this->addSql('ALTER TABLE teacher_subject DROP CONSTRAINT FK_360CB33B23EDC87');
        $this->addSql('DROP TABLE teacher_subject');
    }
}
