<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200127213529 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE microsite (id INT AUTO_INCREMENT NOT NULL, organization_id INT NOT NULL, logo_id INT DEFAULT NULL, favicon_id INT DEFAULT NULL, domain_name VARCHAR(255) NOT NULL, privacy_policy LONGTEXT DEFAULT NULL, terms_of_service LONGTEXT DEFAULT NULL, mailchimp_settings LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', social_links LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', contact_phone_number VARCHAR(30) DEFAULT NULL, enabled TINYINT(1) NOT NULL, uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_2B50051BD17F50A6 (uuid), UNIQUE INDEX UNIQ_2B50051B32C8A3DE (organization_id), INDEX IDX_2B50051BF98F144A (logo_id), INDEX IDX_2B50051BD78119FD (favicon_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE microsite ADD CONSTRAINT FK_2B50051B32C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE microsite ADD CONSTRAINT FK_2B50051BF98F144A FOREIGN KEY (logo_id) REFERENCES media_file (id)');
        $this->addSql('ALTER TABLE microsite ADD CONSTRAINT FK_2B50051BD78119FD FOREIGN KEY (favicon_id) REFERENCES media_file (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE microsite');
    }
}
