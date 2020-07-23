<?php


//back office,
//refresh the token of the user
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'user/acces-token', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    try {
      $res = Flight::refreshAccessToken(Flight::db());
      Flight::json(
        new ApiResponse("succes", Constante::$SUCCES_CODE['201'], array("token" => $res), "OK"),
        Constante::$SUCCES_CODE['201']
      );
    } catch (Exception $ex) {
      if ($ex->getCode() != 500) {
        Flight::json(
          new ApiResponse("error", $ex->getCode(), null, $ex->getMessage()),
          $ex->getCode()
        );
      } else {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error please contact api providers"),
          Constante::$ERROR_CODE['500']
        );
      }
    }
  }
});
//logout
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'user/logout', function () {
  Flight::getAccesControl();

  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    $prot = Flight::protectionPage("logout");
    try {
      $ret = Flight::logOut($prot, Flight::db());
      //resultat
      Flight::json(
        new ApiResponse("succes", Constante::$SUCCES_CODE['204'], "dsfsdf", "logged out"),
        Constante::$SUCCES_CODE['204']
      );
    } catch (Exception $e) {
      if ($e->getCode() != 500 && $e->getCode() != 503) {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $e->getMessage()),
          Constante::$ERROR_CODE['400']
        );
      } else {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, $e->getMessage()),
          Constante::$ERROR_CODE['500']
        );
      }
    }
  }
});

// *************
//initialisation token mobile, normalement indray ihany par idunique ana phone
Flight::route('GET|OPTIONS  ' . Constante::$BASE . 'mobile/init', function () {
  Flight::getAccesControlPublic();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    try {
      $res = Flight::initMobileApp(Flight::db());
      Flight::json(
        new ApiResponse("succes", Constante::$SUCCES_CODE['201'], array("token" => $res), "OK"),
        Constante::$SUCCES_CODE['201']
      );
    } catch (Exception $ex) {
      if ($ex->getCode() != 500) {
        Flight::json(
          new ApiResponse("error", $ex->getCode(), null, $ex->getMessage()),
          $ex->getCode()
        );
      } else {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error please contact api providers"),
          Constante::$ERROR_CODE['500']
        );
      }
    }
  }
});
//insert protocole
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'protocoles', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();
    if (!isset($req->data->nom) || $req->data->nom == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid nom"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->description) || $req->data->description == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        Flight::db()->beginTransaction();
        $id = 'PRT' . GenericDb::formatNumber(GenericDb::getNextVal("seq_protocole", Flight::db()), Constante::$ID_COUNT);
        $description = $req->data->description;
        $nom = $req->data->nom;
        $date = new DateTime();

        $protocole = new Protocole($id, $nom, $description, $date);
        //if there is a duplicate name
        if (count($protocole->getByNom(Flight::db())) != 0) {
          throw new Exception("found duplicate nom", Constante::$ERROR_CODE['400']);
        } else {
          $protocole->insert(Flight::db());
          Flight::db()->commit();
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $protocole, "protocole inserted"),
            Constante::$SUCCES_CODE['201']
          );
        }
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});

//insert societe desinfeciton
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'societeDesinfect', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();
    if (!isset($req->data->nom) || $req->data->nom == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid nom"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->description) || $req->data->description == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->lieu) || $req->data->lieu == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid lieu"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->email) || $req->data->email == "" || !filter_var($req->data->email, FILTER_VALIDATE_EMAIL)) {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid email"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->tel) || $req->data->tel == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid telephone number"),
        Constante::$ERROR_CODE['400']
      );
    } else if (
      !isset($req->data->coordLat) || $req->data->coordLat == "" ||
      !isset($req->data->coordLong) || $req->data->coordLong == "" ||
      !is_numeric($req->data->coordLong) || !is_numeric($req->data->coordLat)
    ) {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid coordonnee"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        Flight::db()->beginTransaction();
        $id = 'DES' . GenericDb::formatNumber(
          GenericDb::getNextVal("seq_societeDesinfection", Flight::db()),
          Constante::$ID_COUNT
        );

        $nom = $req->data->nom;
        $email = $req->data->email;
        $lieu = $req->data->lieu;
        $description = $req->data->description;
        $tel = $req->data->tel;
        $coordonnee = 'SRID=4326;POINT(%.8f %.8f)';
        $coordonnee = sprintf($coordonnee, $req->data->coordLat, $req->data->coordLong);
        $date = new DateTime();

        $societedes = new SocieteDesinfection(
          $id,
          $nom,
          $description,
          $email,
          $tel,
          $lieu,
          $date,
          $coordonnee
        );
        var_dump($societedes);
        //if there is a duplicate name
        if (Flight::validationNom('societeDesinfection', 'nom', $nom, Flight::db(), " and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
          throw new Exception("found duplicate nom", Constante::$ERROR_CODE['400']);
        } else {
          $societedes->insert(Flight::db());
          Flight::db()->commit();
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $societedes, "societe de desinfection inserted"),
            Constante::$SUCCES_CODE['201']
          );
        }
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});

//insert prestation pour societe desinfeciton
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'prestation', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();
    if (!isset($req->data->prix) || $req->data->prix == "" || !is_numeric($req->data->prix)) {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid prix"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->societe) || $req->data->societe == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "id of societe invalid"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->description) || $req->data->description == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        if (!Flight::validationNom('societeDesinfection', 'id', $req->data->societe, Flight::db(), " and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
          throw new Exception("societe not found, it might be deleted", Constante::$ERROR_CODE['400']);
        }
        Flight::db()->beginTransaction();
        $id = 'PRE' . GenericDb::formatNumber(GenericDb::getNextVal("seq_prestation", Flight::db()), Constante::$ID_COUNT);
        $description = $req->data->description;
        $prix = $req->data->prix;
        $societe = $req->data->societe;


        $prest = new Prestation($id, $description, $societe, $prix, Constante::$PRESTATION_ACTIVE);

        $prest->insert(Flight::db());
        Flight::db()->commit();
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $prest, "prestaiton for societe:" . $societe . " inserted"),
          Constante::$SUCCES_CODE['201']
        );
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {

          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});
//get protocole by societe
// ************
Flight::route('GET|OPTIONS  ' . Constante::$BASE . 'protocoles', function () {

  Flight::getAccesControlPublic();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("public-private");
    $req = Flight::request();

    if (!isset($req->query['societe']) || $req->query['societe'] === "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid societe"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->query['type']) || $req->query['type'] === "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid type"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        $type = $req->query['type'];

        $protocols = Flight::getProtocoleBySociete($req->query['societe'], $type, Flight::db());


        if ($type === "all") $protocols = Flight::filterProtocole($protocols);

        $societeTemp = new Societe($req->query['societe'], '', '', '', '', '', '', '', '');
        $societeTemp = $societeTemp->getById(Flight::db());

        $dataReturn = array(
          "societe" => $societeTemp,
          "protocoles" => $protocols
        );

        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
          Constante::$SUCCES_CODE['200']
        );
      } catch (Exception $ex) {

        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during getting protocole"),
          Constante::$ERROR_CODE['500']
        );
      }
    }
  }
});

//update protocole by societe
Flight::route('PUT|OPTIONS  ' . Constante::$BASE . 'protocoleChoisi', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();

    if (!isset($req->data->societe) || $req->data->societe === "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid societe"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->protocoleChoisi) || $req->data->protocoleChoisi == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "ProtocoleChoisi not found"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->delete) || $req->data->delete == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "delete parameter not found"),
        Constante::$ERROR_CODE['400']
      );
    } else if (!isset($req->data->idCategorieProtocole) || $req->data->idCategorieProtocole == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "CategorieProtocole not found"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        Flight::db()->beginTransaction();
        //si delete
        if ($req->data->delete == "true") {
          Flight::deleteProtocoleSociete($req->data->societe, $req->data->protocoleChoisi, $req->data->idCategorieProtocole, Flight::db());
        } else if ($req->data->delete == "false") {
          //si update
          Flight::updateDureeProtocoleSociete($req->data->societe, $req->data->protocoleChoisi, $req->data->idCategorieProtocole, Flight::db());
        } else {
          throw new Exception("delete parameter invalid", Constante::$ERROR_CODE['400']);
        }

        Flight::db()->commit();
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], null, "protocoles modified"),
          Constante::$SUCCES_CODE['200']
        );
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {

          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});

//get societeDesinfection
//mila hatao cursor based raha bedabe le donnee no sady real time fa zany hoe lasa tsisy mjump amna page secific
//ra limit sy offset de afaka mjump amna page specifique fa ra misy  manampy tampoka no blem,mo ra bdb le donnee de lent b
Flight::route('GET|OPTIONS  ' . Constante::$BASE . 'societeDesinfect', function () {

  Flight::getAccesControlPublic();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("public-private");
    $req = Flight::request();
    if (isset($req->query['all']) || $req->query['all'] === "true") {
      try {
        $data = Flight::getAllSocieteDesinfection(" where id not in (select idSocieteDesinfection from societedesinfectiondelete)", Flight::db());

        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $data, "société de desinfection"),
          Constante::$SUCCES_CODE['200']
        );
      } catch (Exception $ex) {

        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during getting protocole"),
          Constante::$ERROR_CODE['500']
        );
      }
    } else if (isset($req->query['page']) && isset($req->query['count'])) {
      if (!intval($req->query['page']) || !intval($req->query['count'])) {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid parameter"),
          Constante::$ERROR_CODE['400']
        );
      } else {
        $page = intval($req->query['page']);
        $count = intval($req->query['count']);
        $totalrow = Flight::Count("societeDesinfection", " id not in (select idSocieteDesinfection from societedesinfectiondelete)", Flight::db());
        $totalPages = round($totalrow / $count);
        if ($page > $totalPages) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "there is no more pages"),
            Constante::$ERROR_CODE['400']
          );
        } else {
          $offset = ($page * $count) - $count;
          $sql = " order by id asc LIMIT %u OFFSET %u ";
          $sql = sprintf($sql, $count, $offset);
          // var_dump($sql);
          $data = Flight::getAllSocieteDesinfection($sql, Flight::db());

          $retour = array(
            "data" => $data,
            "paging" => array(
              "total" => $totalrow,
              "page" => $page,
              "pages" => $totalPages
            )
          );
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $retour, "société de desinfection"),
            Constante::$SUCCES_CODE['200']
          );
        }
      }
    } else {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "no parameter found"),
        Constante::$ERROR_CODE['400']
      );
    }
  }
});
/*
  recherche tsotra ana societe
  pagination:mamerina ny next,mila mi implementer cache ho anle requette
*/

//q(le query apdirnle olona,afaka tsisy)
//cat=all(par defaut),ou misy(idcategorie)
//lat,lng(afaka tsisy,afaka misy fa tsmaints nombre valide ary tsmaints misy izy roa)
//raha tsisy lat,lng de recherche tsotra tsy eo am manodidina zany
//raha tsis inin mintsy afats lat,lng de mvoka eo dol ny societe eo am manodidina

//raha tena ho tsis dol reo rehetra reo fa categorie=all ihany de  tsy mamoka inin fa eo am accueil
Flight::route('GET|OPTIONS  ' . Constante::$BASE . 'search', function () {

  Flight::getAccesControlPublic();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("public-private");
    $req = Flight::request();
    try {


      if (!isset($req->query['cat']) || trim($req->query['cat']) === "") {
        //erreur satry tokony misy categorie fona
        throw new Exception("invalid request, no categorie found", Constante::$ERROR_CODE['400']);
      }
      //raha tsisy afats categorie
      if ((!isset($req->query['q']) || trim($req->query['q']) === "") && !isset($req->query['lat']) && !isset($req->query['lng'])) {
        // mamerina  ny societe rehetra ao raha categorie=all
        if ($req->query['cat'] === "all") {
          $sql = Flight::buildSql("", $req->query['cat']);
          $dataReturn = Flight::executeSearch($sql, Flight::db());
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
            Constante::$SUCCES_CODE['200']
          );
        } else {
          //raha tsy zay de mamerina ze rehetra amniny categorie iny
          $sql = Flight::buildSql("", $req->query['cat']);
          $dataReturn = Flight::executeSearch($sql, Flight::db());
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
            Constante::$SUCCES_CODE['200']
          );
        }
      } else if ((!isset($req->query['q']) || trim($req->query['q']) === "") && isset($req->query['lat']) && isset($req->query['lng'])) {
        //raha lat sy lng ftsn
        //mbola verifiena ho tena nb ve
        Flight::checkLatLng($req->query['lat'], $req->query['lng']);
        $sql = Flight::buildSql("", $req->query['cat'], floatval($req->query['lat']), floatval($req->query['lng']));
        $dataReturn = Flight::executeSearch($sql, Flight::db());
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
          Constante::$SUCCES_CODE['200']
        );
      } else if ((isset($req->query['q']) || trim($req->query['q']) !== "") && !isset($req->query['lat']) && !isset($req->query['lng'])) {
        //raha query ftsn
        $sql = Flight::buildSql($req->query['q'], $req->query['cat']);
        $dataReturn = Flight::executeSearch($sql, Flight::db());
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
          Constante::$SUCCES_CODE['200']
        );
      } else if ((isset($req->query['q']) || trim($req->query['q']) !== "") && isset($req->query['lat']) && isset($req->query['lng'])) {
        //raha ohatra hoe misy do izy rehztra
        //mbl verifierna le latitude sy longitude
        Flight::checkLatLng($req->query['lat'], $req->query['lng']);
        $sql = Flight::buildSql($req->query['q'], $req->query['cat'], floatval($req->query['lat']), floatval($req->query['lng']));
        $dataReturn = Flight::executeSearch($sql, Flight::db());
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
          Constante::$SUCCES_CODE['200']
        );
      } else {
        throw new Exception("cannot fetch any result,  please contact the api provider", Constante::$ERROR_CODE['400']);
      }
    } catch (Exception $ex) {
      if ($ex->getCode() != 500) {
        Flight::json(
          new ApiResponse("error", 400, null, $ex->getMessage()),
          400
        );
      } else {

        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error, please contact the api provider"),
          Constante::$ERROR_CODE['500']
        );
      }
    }
  }
});
//get prestation by societe desinfection
// ************
Flight::route('GET|OPTIONS  ' . Constante::$BASE . 'prestations', function () {

  Flight::getAccesControlPublic();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("public-private");
    $req = Flight::request();

    if (!isset($req->query['societe']) || $req->query['societe'] === "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid societe desinfection"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        if (!Flight::validationNom('societeDesinfection', 'id', $req->query['societe'], Flight::db(), " and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
          throw new Exception("societeDesinfection not found, it might have already been deleted", Constante::$ERROR_CODE['400']);
        }
        $prestations = GenericDb::find(
          Prestation::class,
          'prestation',
          array('idSocieteDesinfection' => $req->query['societe']),
          "",
          Flight::db()
        );

        $societeTemp = new SocieteDesinfection($req->query['societe'], '', '', '', '', '', '', '');
        $societeTemp = $societeTemp->getById(Flight::db());

        $dataReturn = array(
          "societeDesinfection" => $societeTemp,
          "prestations" => $prestations
        );

        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $dataReturn, "protocoles"),
          Constante::$SUCCES_CODE['200']
        );
      } catch (Exception $ex) {

        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during getting protocole"),
          Constante::$ERROR_CODE['500']
        );
      }
    }
  }
});
//delete societe
Flight::route('DELETE|OPTIONS ' . Constante::$BASE . 'societe', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();
    if (!isset($req->data->societe) || $req->data->societe == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid id societe"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {

        if (Flight::validationNom('societe', 'id', $req->data->societe, Flight::db(), " and id not in (select idSociete from societedelete)")) {
          throw new Exception("societe not found, it might have already been deleted", Constante::$ERROR_CODE['400']);
        } else {
          Flight::db()->beginTransaction();
          $id = 'DELS' . GenericDb::formatNumber(
            GenericDb::getNextVal("seq_societedelete", Flight::db()),
            Constante::$ID_COUNT
          );

          $idsociete = $req->data->societe;

          $societeDelete = new SocieteDelete($id, $idsociete);
          $societeDelete->delete(Flight::db());
          Flight::db()->commit();
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['204'], null, "societe deleted"),
            Constante::$SUCCES_CODE['204']
          );
        }
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});
//delete societedESINFECTION
Flight::route('DELETE|OPTIONS ' . Constante::$BASE . 'societeDesinfect', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();
    if (!isset($req->data->societe) || $req->data->societe == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid id societeDesinfection"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {

        if (Flight::validationNom('societeDesinfection', 'id', $req->data->societe, Flight::db(), " and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
          throw new Exception("societeDesinfection not found, it might have already been deleted", Constante::$ERROR_CODE['400']);
        } else {
          Flight::db()->beginTransaction();
          $id = 'DELSD' . GenericDb::formatNumber(
            GenericDb::getNextVal("seq_societedesinfectiondelete", Flight::db()),
            Constante::$ID_COUNT
          );

          $idsociete = $req->data->societe;

          $societeDelete = new SocieteDesinfectionDelete($id, $idsociete);
          $societeDelete->delete(Flight::db());
          Flight::db()->commit();
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['204'], null, "societe desinfection deleted"),
            Constante::$SUCCES_CODE['204']
          );
        }
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});
//delete prestation
Flight::route('DELETE|OPTIONS ' . Constante::$BASE . 'prestation', function () {
  Flight::getAccesControl();
  if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    Flight::json(
      "OK",
      200
    );
  } else {
    Flight::protectionPage("private");
    $req = Flight::request();
    if (!isset($req->data->societe) || $req->data->id == "") {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid id prestation"),
        Constante::$ERROR_CODE['400']
      );
    } else {
      try {
        Flight::db()->beginTransaction();
        $id = $req->data->id;

        $idsociete = $req->data->societe;

        $prestation = new Prestation($id, '', '', '', '');
        $prestation->delete(Flight::db());
        Flight::db()->commit();
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['204'], null, "prestation deleted"),
          Constante::$SUCCES_CODE['204']
        );
      } catch (Exception $ex) {
        Flight::db()->rollBack();
        if ($ex->getCode() == 400) {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
            Constante::$ERROR_CODE['400']
          );
        } else {
          Flight::json(
            new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert"),
            Constante::$ERROR_CODE['500']
          );
        }
      }
    }
  }
});
