<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808154708 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP FOREIGN KEY FK_AC74095A3A8EB775');
        $this->addSql('ALTER TABLE activity_user DROP FOREIGN KEY FK_8E570DDBA76ED395');
        $this->addSql('ALTER TABLE activity_user DROP FOREIGN KEY FK_8E570DDB81C06096');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D6493862031C');
        $this->addSql('DROP TABLE activity_user');
        $this->addSql('DROP TABLE role_user');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP INDEX IDX_AC74095A3A8EB775 ON activity');
        $this->addSql('ALTER TABLE activity DROP id_user_admin_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_user (activity_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_8E570DDBA76ED395 (user_id), INDEX IDX_8E570DDB81C06096 (activity_id), PRIMARY KEY(activity_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE role_user (id INT AUTO_INCREMENT NOT NULL, long_wording VARCHAR(100) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, short_wording VARCHAR(10) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, id_role_user_id INT NOT NULL, username VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, password VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, first_name VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, birth_date DATETIME NOT NULL, mail VARCHAR(30) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, phone VARCHAR(10) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, presentation LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_8D93D6493862031C (id_role_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE activity_user ADD CONSTRAINT FK_8E570DDBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_user ADD CONSTRAINT FK_8E570DDB81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D6493862031C FOREIGN KEY (id_role_user_id) REFERENCES role_user (id)');
        $this->addSql('ALTER TABLE activity ADD id_user_admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE activity ADD CONSTRAINT FK_AC74095A3A8EB775 FOREIGN KEY (id_user_admin_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_AC74095A3A8EB775 ON activity (id_user_admin_id)');
    }
}
