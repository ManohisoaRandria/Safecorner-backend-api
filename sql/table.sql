CREATE EXTENSION postgis;
CREATE EXTENSION postgis_topology;
CREATE DATABASE safecorner_db;
CREATE TABLE users(
  id VARCHAR(50) PRIMARY KEY,
  nom VARCHAR(50),
  mdp VARCHAR(225)
);
CREATE SEQUENCE seq_users;
--pour logout
CREATE TABLE refreshToken(
  id VARCHAR(50) references users,
  token VARCHAR(225),
  etat integer --1 valide,--10:revoked
);
--pour app mobile
CREATE TABLE TokenMobile(
  identifier VARCHAR(100) PRIMARY KEY,
  etat integer
);
CREATE TABLE categorieSociete(
  id VARCHAR(50) PRIMARY KEY,
  description VARCHAR(50)
);
CREATE SEQUENCE seq_categorieSociete;
CREATE TABLE societe(
  id VARCHAR(50) PRIMARY KEY,
  nom VARCHAR(60),
  idCategorieSociete VARCHAR(50) REFERENCES categorieSociete,
  description Text,
  lieu VARCHAR(100),
  dateCreation Timestamp,
  email VARCHAR(150),
  tel VARCHAR(20),
  coordonnee geometry
);
CREATE SEQUENCE seq_societe;
CREATE TABLE protocole(
  id VARCHAR(50) PRIMARY KEY,
  nom VARCHAR(60),
  description Text,
  dateCreation Timestamp
);
CREATE SEQUENCE seq_protocole;
CREATE TABLE categorieProtocole(
  id VARCHAR(50) PRIMARY KEY,
  description VARCHAR(20)
);
CREATE SEQUENCE seq_categorieprotocole;
CREATE TABLE protocoleChoisi(
  id VARCHAR(50) PRIMARY KEY,
  idSociete VARCHAR(50) REFERENCES societe,
  idCategorieProtocole VARCHAR(50) REFERENCES categorieProtocole,
  idProtocole VARCHAR(50) REFERENCES protocole,
  dateCreation Timestamp,
  Duree integer,
  etat integer ---1:active,10:non_active
);
CREATE SEQUENCE seq_protocoleChoisi;
CREATE TABLE HistoriqueChangementProtocole(
  idProtocoleChoisi VARCHAR(50) REFERENCES protocoleChoisi,
  dateChangement Timestamp,
  action integer ---1: add,10: delete
);
CREATE SEQUENCE seq_historiqueChangementProtocole;
CREATE TABLE historiqueDescente(
  id VARCHAR(50) PRIMARY KEY,
  idSociete VARCHAR(50) REFERENCES societe,
  description Text,
  points decimal(10, 2),
  dateCreation Timestamp,
  etat integer --1:valide --10:annuler
);
CREATE SEQUENCE seq_historiqueDescente;
CREATE TABLE societeDesinfection(
  id VARCHAR(50) PRIMARY KEy,
  nom VARCHAR(60),
  description Text,
  email VARCHAR(150),
  tel VARCHAR(20),
  lieu VARCHAR(100),
  dateCreation Timestamp,
  coordonnee geometry
);
CREATE SEQUENCE seq_societeDesinfection;
CREATE TABLE prestation(
  id VARCHAR(50) PRIMARY KEY,
  description Text,
  prix Decimal(10, 2),
  idSocieteDesinfection VARCHAR(50) REFERENCES societeDesinfection
);
CREATE SEQUENCE seq_prestation;
CREATE TABLE societeDelete(
  id VARCHAR(50) PRIMARY KEY,
  idSociete VARCHAR(50) REFERENCES societe
);
CREATE SEQUENCE seq_societedelete;
CREATE TABLE societeDesinfectionDelete(
  id VARCHAR(50) PRIMARY KEY,
  idSocieteDesinfection VARCHAR(50) REFERENCES societeDesinfection
);
CREATE SEQUENCE seq_societedesinfectiondelete;
create view protocoleDetail as
select
  protocoleChoisi.*,
  protocole.nom nomProtocole,
  protocole.description descriptionProtocole,
  protocole.dateCreation dateCreationProtocole,
  categorieProtocole.description descriptionCategProtocole
from protocoleChoisi
join protocole on protocoleChoisi.idProtocole = protocole.id
join categorieProtocole on protocoleChoisi.idCategorieProtocole = categorieProtocole.id;
--//Creation view historiqueChangementProtocoleDetail
  CREATE VIEW historiqueChangementProtocoleDetail as
SELECT
  historiqueChangementProtocole.idprotocolechoisi,
  protocoleChoisi.idsociete,
  protocoleChoisi.idcategorieprotocole,
  protocoleChoisi.idprotocole,
  protocoleChoisi.duree,
  protocoleChoisi.etat,
  protocoleChoisi.datecreation,
  historiqueChangementProtocole.datechangement,
  historiqueChangementProtocole.action
FROM protocoleChoisi,
  historiqueChangementProtocole
WHERE
  protocoleChoisi.id = historiqueChangementProtocole.idProtocoleChoisi;
--*******
  create view detailSocieteHistorique as
select
  societe.id,
  societe.nom,
  societe.idCategorieSociete,
  societe.description,
  societe.lieu,
  societe.email,
  societe.tel,
  societe.coordonnee,
  -- raha sendra mbola tsisy points
  CASE
    WHEN historiqueDescente.id is null THEN 'idtemp'
    ELSE historiqueDescente.id
  END idhisto,
  CASE
    WHEN historiqueDescente.points >= 0 THEN historiqueDescente.points
    ELSE 0
  END points,
  CASE
    WHEN historiqueDescente.dateCreation is not null THEN historiqueDescente.dateCreation
    ELSE now()
  END dateCreation
from societe
LEFT JOIN historiqueDescente on societe.id = historiqueDescente.idSociete;
--*******
  -- ty no vue miasa amle recherche
  create view societeSearch as
select
  detailSocieteHistorique.id,
  detailSocieteHistorique.nom,
  detailSocieteHistorique.description,
  detailSocieteHistorique.lieu,
  detailSocieteHistorique.email,
  detailSocieteHistorique.tel,
  detailSocieteHistorique.coordonnee,
  categorieSociete.description categorie,
  categorieSociete.id idcategorie,
  detailSocieteHistorique.points points,
  concat_ws(
    ' ',
    detailSocieteHistorique.nom,
    categorieSociete.description
  ) recherche
from detailSocieteHistorique
join categorieSociete on detailSocieteHistorique.idcategoriesociete = categorieSociete.id
where
  dateCreation in(
    select
      max(dateCreation)
    from detailSocieteHistorique
    group by
      id
  );
--