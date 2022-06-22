<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614123512 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculation ADD country_rate_id INT DEFAULT NULL, ADD custom_value DOUBLE PRECISION NOT NULL, ADD vat_value DOUBLE PRECISION DEFAULT NULL, ADD net_amount DOUBLE PRECISION NOT NULL, ADD gross_amount DOUBLE PRECISION NOT NULL, ADD vat_added TINYINT(1) NOT NULL, ADD vat_removed TINYINT(1) NOT NULL, ADD deleted TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE calculation ADD CONSTRAINT FK_F6A7697075D5C7F2 FOREIGN KEY (country_rate_id) REFERENCES country_rate (id)');
		$this->addSql("INSERT INTO `currency` (`id`, `symbol`) VALUES (NULL, '€'), (NULL, '$'), (NULL, '£'), (NULL, 'zł'), (NULL, 'kr')");
        $this->addSql("INSERT INTO `country_rate` (`id`, `currency_id`, `country_name`, `rate`, `country_code`) VALUES (NULL, '5', 'Switzerland', '7.7', 'ch'), (NULL, '1', 'Austria', '20', 'at'), (NULL, '1', 'Belgium', '21', 'be'), (NULL, '3', 'United Kingdom', '20', 'uk'), (NULL, '4', 'Poland', '23', 'pl'), (NULL, '2', 'Australia', '18', 'au')");
        $this->addSql('CREATE INDEX IDX_F6A7697075D5C7F2 ON calculation (country_rate_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculation DROP FOREIGN KEY FK_F6A7697075D5C7F2');
        $this->addSql('DROP INDEX IDX_F6A7697075D5C7F2 ON calculation');
        $this->addSql('TRUNCATE `country_rate`');
		$this->addSql('TRUNCATE `currency`');
        $this->addSql('ALTER TABLE calculation DROP country_rate_id, DROP custom_value, DROP vat_value, DROP net_amount, DROP gross_amount, DROP vat_added, DROP vat_removed, DROP deleted');
    }
}
