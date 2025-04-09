<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250409104029 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE colis (id SERIAL NOT NULL, expediteur_id INT DEFAULT NULL, destinaire_id INT NOT NULL, warehouse_id INT DEFAULT NULL, code_tracking VARCHAR(255) NOT NULL, dimensions VARCHAR(255) NOT NULL, poids DOUBLE PRECISION NOT NULL, valeur_declaree DOUBLE PRECISION NOT NULL, date_creation TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, nature_marchandise VARCHAR(255) NOT NULL, description_marchandise TEXT DEFAULT NULL, classification_douaniere VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_470BDFF910335F61 ON colis (expediteur_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_470BDFF9C8DCC84B ON colis (destinaire_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_470BDFF95080ECDE ON colis (warehouse_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE colis_transport (id SERIAL NOT NULL, colis_id INT DEFAULT NULL, date_association TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_D016FE294D268D70 ON colis_transport (colis_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE departement (id SERIAL NOT NULL, nom_departemnt VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE destinataire (id SERIAL NOT NULL, nom_entreprise_individu VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(255) DEFAULT NULL, pays VARCHAR(255) NOT NULL, adresse_complete VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE document_support (id SERIAL NOT NULL, colis_id INT DEFAULT NULL, nom_fichier VARCHAR(255) NOT NULL, type_document VARCHAR(100) NOT NULL, date_upload TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, chemin_stockage VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_246B4D794D268D70 ON document_support (colis_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE employe (id SERIAL NOT NULL, departement_id INT NOT NULL, nom VARCHAR(100) NOT NULL, prenom VARCHAR(100) NOT NULL, email VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, niveau_acces VARCHAR(50) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_F804D3B9CCF9E01E ON employe (departement_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE expediteur (id SERIAL NOT NULL, nom_entreprise_individu VARCHAR(255) NOT NULL, telephone VARCHAR(20) NOT NULL, email VARCHAR(255) NOT NULL, pays VARCHAR(100) NOT NULL, adresse_complete TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE photo (id SERIAL NOT NULL, colis_id INT DEFAULT NULL, url_photo VARCHAR(255) DEFAULT NULL, date_upload TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_14B784184D268D70 ON photo (colis_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE status (id SERIAL NOT NULL, employe_id INT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_7B00651C1B65292 ON status (employe_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE statut (id SERIAL NOT NULL, colis_id INT NOT NULL, id_status INT NOT NULL, date_status TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, localisation VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_E564F0BF4D268D70 ON statut (colis_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE transport (id SERIAL NOT NULL, type_transport VARCHAR(100) NOT NULL, compagnie_transport VARCHAR(255) NOT NULL, numero_vol_navire VARCHAR(100) DEFAULT NULL, date_depart TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, lieu_depart VARCHAR(255) NOT NULL, date_arrivee TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, lieu_arrivee VARCHAR(255) NOT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE warehouse (id SERIAL NOT NULL, code_ut VARCHAR(50) NOT NULL, localisation_warehouse VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis ADD CONSTRAINT FK_470BDFF910335F61 FOREIGN KEY (expediteur_id) REFERENCES expediteur (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis ADD CONSTRAINT FK_470BDFF9C8DCC84B FOREIGN KEY (destinaire_id) REFERENCES destinataire (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis ADD CONSTRAINT FK_470BDFF95080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis_transport ADD CONSTRAINT FK_D016FE294D268D70 FOREIGN KEY (colis_id) REFERENCES colis (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document_support ADD CONSTRAINT FK_246B4D794D268D70 FOREIGN KEY (colis_id) REFERENCES colis (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employe ADD CONSTRAINT FK_F804D3B9CCF9E01E FOREIGN KEY (departement_id) REFERENCES departement (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo ADD CONSTRAINT FK_14B784184D268D70 FOREIGN KEY (colis_id) REFERENCES colis (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE status ADD CONSTRAINT FK_7B00651C1B65292 FOREIGN KEY (employe_id) REFERENCES employe (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE statut ADD CONSTRAINT FK_E564F0BF4D268D70 FOREIGN KEY (colis_id) REFERENCES colis (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE SCHEMA public
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis DROP CONSTRAINT FK_470BDFF910335F61
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis DROP CONSTRAINT FK_470BDFF9C8DCC84B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis DROP CONSTRAINT FK_470BDFF95080ECDE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE colis_transport DROP CONSTRAINT FK_D016FE294D268D70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE document_support DROP CONSTRAINT FK_246B4D794D268D70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE employe DROP CONSTRAINT FK_F804D3B9CCF9E01E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo DROP CONSTRAINT FK_14B784184D268D70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE status DROP CONSTRAINT FK_7B00651C1B65292
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE statut DROP CONSTRAINT FK_E564F0BF4D268D70
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE colis
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE colis_transport
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE departement
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE destinataire
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE document_support
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE employe
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE expediteur
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE photo
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE status
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE statut
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE transport
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE warehouse
        SQL);
    }
}
