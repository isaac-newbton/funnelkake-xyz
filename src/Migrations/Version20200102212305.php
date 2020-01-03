<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200102212305 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, ticket_id INT DEFAULT NULL, task_id INT DEFAULT NULL, content LONGTEXT NOT NULL, timestamp DATETIME NOT NULL, INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526C700047D2 (ticket_id), INDEX IDX_9474526C8DB60186 (task_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_file (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, organization_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, path VARCHAR(1000) NOT NULL, size INT NOT NULL, mime_type VARCHAR(255) NOT NULL, timestamp DATETIME NOT NULL, uuid CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', UNIQUE INDEX UNIQ_4FD8E9C3D17F50A6 (uuid), INDEX IDX_4FD8E9C3A76ED395 (user_id), INDEX IDX_4FD8E9C332C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_file_ticket (media_file_id INT NOT NULL, ticket_id INT NOT NULL, INDEX IDX_D8DFF98FF21CFF25 (media_file_id), INDEX IDX_D8DFF98F700047D2 (ticket_id), PRIMARY KEY(media_file_id, ticket_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE media_file_task (media_file_id INT NOT NULL, task_id INT NOT NULL, INDEX IDX_FEE200C5F21CFF25 (media_file_id), INDEX IDX_FEE200C58DB60186 (task_id), PRIMARY KEY(media_file_id, task_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE organization (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE task (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, subject VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, status INT DEFAULT NULL, INDEX IDX_97A0ADA332C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, organization_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D64932C8A3DE (organization_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C8DB60186 FOREIGN KEY (task_id) REFERENCES task (id)');
        $this->addSql('ALTER TABLE media_file ADD CONSTRAINT FK_4FD8E9C3A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE media_file ADD CONSTRAINT FK_4FD8E9C332C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE media_file_ticket ADD CONSTRAINT FK_D8DFF98FF21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_file_ticket ADD CONSTRAINT FK_D8DFF98F700047D2 FOREIGN KEY (ticket_id) REFERENCES ticket (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_file_task ADD CONSTRAINT FK_FEE200C5F21CFF25 FOREIGN KEY (media_file_id) REFERENCES media_file (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE media_file_task ADD CONSTRAINT FK_FEE200C58DB60186 FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA332C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64932C8A3DE FOREIGN KEY (organization_id) REFERENCES organization (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE media_file_ticket DROP FOREIGN KEY FK_D8DFF98FF21CFF25');
        $this->addSql('ALTER TABLE media_file_task DROP FOREIGN KEY FK_FEE200C5F21CFF25');
        $this->addSql('ALTER TABLE media_file DROP FOREIGN KEY FK_4FD8E9C332C8A3DE');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA332C8A3DE');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64932C8A3DE');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C8DB60186');
        $this->addSql('ALTER TABLE media_file_task DROP FOREIGN KEY FK_FEE200C58DB60186');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C700047D2');
        $this->addSql('ALTER TABLE media_file_ticket DROP FOREIGN KEY FK_D8DFF98F700047D2');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE media_file DROP FOREIGN KEY FK_4FD8E9C3A76ED395');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE media_file');
        $this->addSql('DROP TABLE media_file_ticket');
        $this->addSql('DROP TABLE media_file_task');
        $this->addSql('DROP TABLE organization');
        $this->addSql('DROP TABLE task');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE user');
    }
}
