<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230729093129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE company_image (company_id INT NOT NULL, image_id INT NOT NULL, INDEX IDX_82CCA63A979B1AD6 (company_id), INDEX IDX_82CCA63A3DA5256D (image_id), PRIMARY KEY(company_id, image_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE company_image ADD CONSTRAINT FK_82CCA63A979B1AD6 FOREIGN KEY (company_id) REFERENCES company (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE company_image ADD CONSTRAINT FK_82CCA63A3DA5256D FOREIGN KEY (image_id) REFERENCES image (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE company_image DROP FOREIGN KEY FK_82CCA63A979B1AD6');
        $this->addSql('ALTER TABLE company_image DROP FOREIGN KEY FK_82CCA63A3DA5256D');
        $this->addSql('DROP TABLE company_image');
    }
}
