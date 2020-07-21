<?php
Flight::map('getIdProtocoleChoisi', function ($societe, $idprotocole, PDO $con) {
    try {
        $id = "";
        $sql = "SELECT id FROM protocolechoisi where idsociete = '%s' and  idprotocole='%s'";
        $sql = sprintf($sql, $societe, $idprotocole);

        $res = $con->query($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
            $id = $donnees['id'];
        }
        $res->closeCursor();
        return $id;
    } catch (Exception $ex) {
        throw $ex;
    }
});
Flight::map('deleteProtocoleSociete', function ($societe, $data,$idcategProt, PDO $con) {
    try {
        $id = "";
        for ($i = 0; $i < Count($data); $i++) {
            $id = Flight::getIdProtocoleChoisi($societe, $data[$i]['idProtocole'], $con);
            GenericDb::update(
                "protocolechoisi",
                array("etat" => Constante::$PROTOCOLE_NON_ACTIVE),
                " idsociete='" . $societe . "' and idprotocole='" . $data[$i]['idProtocole'] . "' and idcategorieprotocole='".$idcategProt."' ",
                false,
                $con
            );
            $historiqueChangementProtocole = new HistoriqueChangementProtocole(
                $id,
                new DateTime(),
                Constante::$HISTORIQUE_PROTOCOLE_DELETE
            );
            $historiqueChangementProtocole->insert($con);
        }
    } catch (Exception $e) {
        throw $e;
    }
});
Flight::map('updateDureeProtocoleSociete', function ($societe, $data,$idcategProt, PDO $con) {
    try {
        for ($i = 0; $i < Count($data); $i++) {
            GenericDb::update(
                "protocolechoisi",
                array("duree" => $data[$i]['duree']),
                " idsociete='" . $societe . "' and idprotocole='" . $data[$i]['idProtocole'] . "' and idcategorieprotocole='".$idcategProt."' ",
                false,
                $con
            );
        }
    } catch (Exception $e) {
        throw $e;
    }
});

Flight::map('filterProtocole', function ($detailProtocole) {
    $filtername = "";
    $temp = array();
    $return = array();
    // var_dump($detailProtocole);
    for ($i = 0; $i < count($detailProtocole); $i++) {
        if ($i == 0) $filtername = $detailProtocole[0]->getDescriptioncategprotocole();

        if ($filtername === $detailProtocole[$i]->getDescriptioncategprotocole()) {
            $temp[] = $detailProtocole[$i];
            if ($i == (count($detailProtocole) - 1)) {
                $return[$filtername] = $temp;
            }
        } else {
            $return[$filtername] = $temp;
            $filtername = $detailProtocole[$i]->getDescriptioncategprotocole();
            
            $temp = array();
            $temp[] = $detailProtocole[$i];
            if ($i == (count($detailProtocole) - 1)) {
                $return[$filtername] = $temp;
            }
        }
    }

    $protocols = array();

    $farany = array();
    $objecttemp = null;

    foreach ($return as $prot => $value) {

        foreach ($value as $detailProt) {

            $objecttemp = new Protocole(
                $detailProt->getIdprotocole(),
                $detailProt->getNomprotocole(),
                $detailProt->getDescriptionprotocole(),
                $detailProt->getDatecreation()
            );

            $protocols[] = array(
                "protocole" => $objecttemp,
                "dureeLimiteDeChangement" => $detailProt->getDuree()
            );
        }
        $farany[$prot] = $protocols;
        $protocols = array();
    }
    return $farany;
});
//le type io "client,perso,all"
//raha all izy de type de retour ProtocoleDetail[]
//fa ra akotran zay de Protocole[]
Flight::map('getProtocoleBySociete', function (string $societe, string $type, PDO $con) {
    try {
        $ifData = array();
        $aprewhere = "";
        if ($type == "all") {
            $ifData = array(
                "idsociete" => $societe,
                "etat" => Constante::$PROTOCOLE_ACTIVE
            );
            $aprewhere = " order by descriptioncategprotocole";
        } else {
            $ifData = array(
                "idsociete" => $societe,
                "idcategorieprotocole" => $type,
                "etat" => Constante::$PROTOCOLE_ACTIVE
            );
        }
        $detailProtocole = GenericDb::find(
            ProtocoleDetail::class,
            'protocoledetail',
            $ifData,
            $aprewhere,
            $con
        );
        if ($type !== "all") {
            $protocols = array();
            $objecttemp = null;
            foreach ($detailProtocole as $prot) {
                $objecttemp = new Protocole(
                    $prot->getIdprotocole(),
                    $prot->getNomprotocole(),
                    $prot->getDescriptionprotocole(),
                    $prot->getDatecreation()
                );

                $protocols[] = array(
                    "protocole" => $objecttemp,
                    "dureeLimiteDeChangement" => $prot->getDuree()
                );
            }
            return $protocols;
        } else {
            return $detailProtocole;
        }
    } catch (Exception $ex) {
        throw $ex;
    }
});
Flight::map('getAllSocieteDesinfection', function (string $where, PDO $con) {
    try {
        $societe = null;
        $sql = "SELECT id,nom,description,email,tel,lieu,dateCreation,
                ST_AsGeoJSON(coordonnee) as coordonnee FROM societeDesinfection ";
        if ($where != "") $sql .= " " . $where;
        var_dump($sql);
        $ret = array();
        $res = $con->query($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
            $ret[] = new SocieteDesinfection(
                $donnees['id'],
                $donnees['nom'],
                $donnees['description'],
                $donnees['email'],
                $donnees['tel'],
                $donnees['lieu'],
                $donnees['datecreation'],
                $donnees['coordonnee']
            );
        }
        $res->closeCursor();
        return $ret;
    } catch (Exception $ex) {
        throw $ex;
    }
});
Flight::map('checkLatLng', function ($coordLat, $coordLong) {
    try {
        if (!floatval($coordLong) || !floatval($coordLat)) {
            throw new Exception("invalid coordinates", Constante::$ERROR_CODE['400']);
        }
    } catch (Exception $th) {
        throw $th;
    }
});

Flight::map('buildSql', function (string $q = "", string $cat, float $lat = null, float $lng = null) {
    $cat = preg_replace("/\s/", "", $cat);
    $sql = "SELECT id,nom,idcategorie,description,lieu,email,tel,ST_AsGeoJSON(coordonnee) as coordonnee,points
    FROM ";
    $table = " societeSearch ";
    $bool = false;
    $all = false;
    if ($lat != null && $lng != null) {
        $table = " (SELECT *,
      ST_Intersects(ST_Buffer(ST_Transform('SRID=4326;POINT(%.8f %.8f)'::geometry, 3857),%u,'quad_segs=8'),
      ST_Transform(ST_AsEWKT(coordonnee), 3857)) as etat 
      FROM societeSearch) as societe WHERE etat = 't' ";
        $table = sprintf($table, $lat, $lng, Constante::$SEARCH_RADIUS);
        $bool = true;
    }
    $subsql = "";
    $subsql2 = "";
    if ($q !== "" && $q !== " ") {
        //alana ny espace aloha sy arina

        $q = trim($q);
        $q = preg_replace("/[^a-zA-Z0-9\s]/", "", $q);
        $tab = explode(" ", $q);
        if (count($tab) > 1) {
            //raha misy maromaro sarahan espace
            //% ihany no mi echaper % refa hanao sprintf
            for ($i = 0; $i < count($tab); $i++) {
                if ($i != (count($tab) - 1)) $subsql .= " recherche ILIKE '%%%s%%' and ";
                else $subsql .= " recherche ILIKE '%%%s%%' ";
            }
            $subsql = vsprintf($subsql, $tab);
        } else {
            $subsql = " recherche ILIKE '%%%s%%' ";
            $subsql = sprintf($subsql, $q);
        }
    }
    if ($cat != "") {
        if ($cat != "all") {
            if ($subsql == "") $subsql2 = " idcategorie='%s'";
            else $subsql2 = " and idcategorie='%s'";
            $subsql2 = sprintf($subsql2, $cat);
        }else{
            $all=true;
        }
    }
    if ($bool) {
        if ($subsql != "" || $subsql2 != "") $table .= " and " . $subsql . " " . $subsql2;
        else $table .= $subsql . " " . $subsql2;
    } else{
        if($all) $table .= " " . $subsql . " " . $subsql2;
        else $table .= " where " . $subsql . " " . $subsql2;
    } 
    return $sql . $table . " order by points desc";
});
Flight::map('executeSearch', function ($sql, PDO $con) {

    try {
        $lesSociete = array();
        $res = $con->query($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
            $lesSociete[] = new SocieteAffiche(
                $donnees['id'],
                $donnees['nom'],
                $donnees['idcategorie'],
                $donnees['description'],
                $donnees['lieu'],
                $donnees['email'],
                $donnees['tel'],
                $donnees['coordonnee'],
                $donnees['points']
            );
        }
        $res->closeCursor();
        return $lesSociete;
    } catch (Exception $e) {
        throw $e;
    }
});
