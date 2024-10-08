<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240808152123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE activity_picture_gallery (activity_id INT NOT NULL, picture_gallery_id INT NOT NULL, INDEX IDX_1723E56481C06096 (activity_id), INDEX IDX_1723E5642837EF88 (picture_gallery_id), PRIMARY KEY(activity_id, picture_gallery_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE activity_picture_gallery ADD CONSTRAINT FK_1723E56481C06096 FOREIGN KEY (activity_id) REFERENCES activity (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE activity_picture_gallery ADD CONSTRAINT FK_1723E5642837EF88 FOREIGN KEY (picture_gallery_id) REFERENCES picture_gallery (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE activity_picture_gallery DROP FOREIGN KEY FK_1723E56481C06096');
        $this->addSql('ALTER TABLE activity_picture_gallery DROP FOREIGN KEY FK_1723E5642837EF88');
        $this->addSql('DROP TABLE activity_picture_gallery');
    }
}
