<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240827091757 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity ADD time TIME DEFAULT NULL');
        $this->addSql('ALTER TABLE activity_picture_gallery DROP FOREIGN KEY FK_1723E56481C06096');
        $this->addSql('ALTER TABLE activity_picture_gallery ADD CONSTRAINT FK_1723E56481C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE activity_user DROP FOREIGN KEY FK_8E570DDB81C06096');
        $this->addSql('ALTER TABLE activity_user ADD CONSTRAINT FK_8E570DDB81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id)');
        $this->addSql('ALTER TABLE user ADD registered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', ADD profile_picture VARCHAR(255) DEFAULT NULL, ADD is_active TINYINT(1) DEFAULT NULL, CHANGE first_name first_name VARCHAR(30) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity DROP time');
        $this->addSql('ALTER TABLE activity_picture_gallery DROP FOREIGN KEY FK_1723E56481C06096');
        $this->addSql('ALTER TABLE activity_picture_gallery ADD CONSTRAINT FK_1723E56481C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_user DROP FOREIGN KEY FK_8E570DDB81C06096');
        $this->addSql('ALTER TABLE activity_user ADD CONSTRAINT FK_8E570DDB81C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user DROP registered_at, DROP profile_picture, DROP is_active, CHANGE first_name first_name VARCHAR(30) NOT NULL');
    }
}
