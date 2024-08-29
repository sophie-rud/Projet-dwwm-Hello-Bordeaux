<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240804180555 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, id_role_user_id INT NOT NULL, username VARCHAR(30) NOT NULL, password VARCHAR(30) NOT NULL, first_name VARCHAR(30) NOT NULL, birth_date DATETIME NOT NULL, mail VARCHAR(30) NOT NULL, phone VARCHAR(10) DEFAULT NULL, presentation LONGTEXT DEFAULT NULL, INDEX IDX_8D93D6493862031C (id_role_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493862031C FOREIGN KEY (id_role_user_id) REFERENCES role_user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493862031C');
        $this->addSql('DROP TABLE user');
    }
}
