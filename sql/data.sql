--Data test
INSERT INTO Societe
VALUES(
    'SOC0001',
    'detailSocieteHistorique',
    'CATS0001',
    'dfgshgf',
    'ampary',
    '2020/12/01',
    'detailSocieteHistorique@gmail.com',
    '0322821212',
    ST_GeomFromGeoJSON(
      '{"type":"Point","coordinates":[-48.23456,20.12345]}'
    )
  );
insert into categorieSociete
values(
    'CATS000' || nextVal('seq_categorieSociete'),
    'RESTAURANT'
  );
insert into categorieProtocole
values(
    'CTP000' || nextVal('seq_categorieprotocole'),
    'client'
  );
insert into categorieProtocole
values(
    'CTP000' || nextVal('seq_categorieprotocole'),
    'perso'
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0006',
    'fzefzzef',
    4,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0006',
    'fzefzzef',
    7,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0006',
    'fzefzzef',
    10,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0006',
    'fzefzzef',
    60,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0006',
    'fzefzzef',
    3,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0008',
    'fzefzzef',
    10,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0008',
    'fzefzzef',
    9,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0008',
    'fzefzzef',
    8,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0008',
    'fzefzzef',
    10,
    now(),
    1
  );
insert into historiqueDescente
values(
    nextVal('seq_historiqueDescente'),
    'SOC0008',
    'fzefzzef',
    204,
    now(),
    1
  );
-- insert into protocoleChoisi values('PRC000'||nextVal('seq_protocoleChoisi'),'SOC0001','CTP0001','0001','2020/12/01 12:00:00',2,1);
  -- insert into protocoleChoisi values('PRC000'||nextVal('seq_protocoleChoisi'),'SOC0001','CTP0001','0003','2020/12/01 12:00:00',2,1);
  --//Prendre en JSON ST_AsGeoJSON(GEOMETRY);
  --//Prendre la distance entre 2 point:
  --//Prendre la distance entre 2 point: SELECT ST_Distance(ST_SetSRID(ST_GeomFromGeoJSON('{"type":"Point","coordinates":[-18.882732, 47.507620]}'),4326),ST_SetSRID(ST_GeomFromGeoJSON('{"type":"Point","coordinates":[-18.881828,47.506950]}'),4326));
  --//Voir les point approximite:
  --SELECT ST_Intersects(ST_Buffer(ST_GeomFromGeoJSON('{"type":"Point","coordinates":[-18.895141,47.546958]}'),0.005,'quad_segs=8'),ST_GeomFromGeoJSON('{"type":"Point","coordinates":[-18.892716,47.550817]}'));
  --//La requete pour avoir tous les societe a proximite
SELECT
  id,
  nom,
  idcategoriesociete,
  description,
  lieu,
  datecreation,
  email,
  tel,
  ST_AsGeoJSON(coordonnee) as coordonnee
FROM (
    SELECT
      *,
      ST_Intersects(
        ST_Buffer(
          ST_GeomFromGeoJSON(
            '{"type":"Point","coordinates":[-18.829834,47.513142]}'
          ),
          0.00561,
          'quad_segs=8'
        ),
        --//le coordonnee du client
        coordonnee
      ) as etat
    FROM societe
  ) as societe
WHERE
  etat = 't';