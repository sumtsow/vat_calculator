<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220614210400 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculation ADD vat_rate DOUBLE PRECISION NOT NULL, ADD vat_amount DOUBLE PRECISION NOT NULL, DROP custom_value, DROP vat_value');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calculation ADD custom_value DOUBLE PRECISION NOT NULL, ADD vat_value DOUBLE PRECISION NOT NULL, DROP vat_rate, DROP vat_amount');
    }
}
