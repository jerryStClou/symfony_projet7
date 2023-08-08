<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230808124559 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chambre DROP FOREIGN KEY FK_C509E4FF9B177F54');
        $this->addSql('DROP INDEX IDX_C509E4FF9B177F54 ON chambre');
        $this->addSql('ALTER TABLE chambre DROP chambre_id');
        $this->addSql('ALTER TABLE photo ADD chambre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE photo ADD CONSTRAINT FK_14B784189B177F54 FOREIGN KEY (chambre_id) REFERENCES chambre (id)');
        $this->addSql('CREATE INDEX IDX_14B784189B177F54 ON photo (chambre_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chambre ADD chambre_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chambre ADD CONSTRAINT FK_C509E4FF9B177F54 FOREIGN KEY (chambre_id) REFERENCES photo (id)');
        $this->addSql('CREATE INDEX IDX_C509E4FF9B177F54 ON chambre (chambre_id)');
        $this->addSql('ALTER TABLE photo DROP FOREIGN KEY FK_14B784189B177F54');
        $this->addSql('DROP INDEX IDX_14B784189B177F54 ON photo');
        $this->addSql('ALTER TABLE photo DROP chambre_id');
    }
}
