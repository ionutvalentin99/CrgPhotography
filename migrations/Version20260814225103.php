<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260814225103 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE album (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, cover_id INT DEFAULT NULL, sort_order INT NOT NULL, visible TINYINT NOT NULL, created_at DATETIME NOT NULL, created_by INT NOT NULL, updated_at DATETIME DEFAULT NULL, updated_by INT DEFAULT NULL, INDEX IDX_39986E43922726E9 (cover_id), INDEX IDX_39986E43DE12AB56 (created_by), INDEX IDX_39986E4316FE72E1 (updated_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, album_id INT NOT NULL, path VARCHAR(500) NOT NULL, title VARCHAR(255) DEFAULT NULL, description LONGTEXT DEFAULT NULL, sort_order INT NOT NULL, visible TINYINT NOT NULL, created_at DATETIME NOT NULL, created_by INT NOT NULL, updated_at DATETIME DEFAULT NULL, updated_by INT DEFAULT NULL, INDEX IDX_14B784181137ABCF (album_id), INDEX IDX_14B78418DE12AB56 (created_by), INDEX IDX_14B7841816FE72E1 (updated_by), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43922726E9 FOREIGN KEY (cover_id) REFERENCES photo (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E43DE12AB56 FOREIGN KEY (created_by) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE album ADD CONSTRAINT FK_39986E4316FE72E1 FOREIGN KEY (updated_by) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784181137ABCF FOREIGN KEY (album_id) REFERENCES album (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B78418DE12AB56 FOREIGN KEY (created_by) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B7841816FE72E1 FOREIGN KEY (updated_by) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43922726E9');
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E43DE12AB56');
        $this->addSql('ALTER TABLE album DROP FOREIGN KEY FK_39986E4316FE72E1');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784181137ABCF');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B78418DE12AB56');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B7841816FE72E1');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE photo');
    }
}
