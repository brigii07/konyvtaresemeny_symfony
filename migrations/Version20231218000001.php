<?php
declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20231218000001 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Ellenőrizzük, hogy létezik-e már a tábla
        $this->addSql('CREATE TABLE IF NOT EXISTS registration (
            id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, 
            user_name VARCHAR(255) NOT NULL, 
            status VARCHAR(50) NOT NULL, 
            created_at DATETIME NOT NULL
        )');
        
        // Index csak akkor, ha még nem létezik
        $this->addSql('CREATE UNIQUE INDEX IF NOT EXISTS UNIQ_62A8A7A724A232CF ON registration (user_name)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS registration');
    }
}