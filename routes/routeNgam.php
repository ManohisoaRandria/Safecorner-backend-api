<?php


//back office,
//refresh the token of the user
Flight::route('GET ' . Constante::$BASE . 'user/acces-token', function () {
  try {
    $res = Flight::refreshAccessToken(Flight::db());
    Flight::json(
      new ApiResponse("succes", Constante::$SUCCES_CODE['201'], array("token" => $res), "OK"),
      Constante::$SUCCES_CODE['201']
    );
  } catch (Exception $ex) {
    if ($ex->getCode() != 500) {
      Flight::json(
        new ApiResponse("error", $ex->getCode(), null, $ex->getMessage())
      );
    } else {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error please contact api providers")
      );
    }
  }
});
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'user/logout', function () {
  $prot=Flight::protectionPage("logout");
  Flight::getAccesControl();
  
      try {
          $ret=Flight::logOut($prot,Flight::db());
          //resultat
          Flight::json(
              new ApiResponse("succes", Constante::$SUCCES_CODE['204'], null,$ret),
              Constante::$SUCCES_CODE['204']
          );
      } catch (Exception $e) {
          if ($e->getCode() != 500 && $e->getCode() != 503) {
              Flight::json(
                  new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $e->getMessage())
              );
          } else {
              Flight::json(
                  new ApiResponse("error", Constante::$ERROR_CODE['500'], null, $e->getMessage())
              );
          }
      } finally {
          $con = null;
      }
});

// *************
//initialisation token mobile, normalement indray ihany par idunique ana phone
Flight::route('GET ' . Constante::$BASE . 'mobile/init', function () {
  try {
    $res = Flight::initMobileApp(Flight::db());
    Flight::json(
      new ApiResponse("succes", Constante::$SUCCES_CODE['201'], array("token" => $res), "OK"),
      Constante::$SUCCES_CODE['201']
    );
  } catch (Exception $ex) {
    if ($ex->getCode() != 500) {
      Flight::json(
        new ApiResponse("error", $ex->getCode(), null, $ex->getMessage())
      );
    } else {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error please contact api providers")
      );
    }
  }
});
//insert protocole
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'protocoles', function () {
  Flight::getAccesControl();
  $req = Flight::request();
  if (!isset($req->data->nom) || $req->data->nom == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid nom")
    );
  } else if (!isset($req->data->description) || $req->data->description == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description")
     
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
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage())   
        );
      } else {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert")
        );
      }
    }
  }
});

//insert societe desinfeciton
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'societeDesinfect', function () {
  Flight::getAccesControl();
  $req = Flight::request();
  if (!isset($req->data->nom) || $req->data->nom == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid nom") 
    );
  } else if (!isset($req->data->description) || $req->data->description == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description") 
    );
  } else if (!isset($req->data->lieu) || $req->data->lieu == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid lieu") 
    );
  } else if (!isset($req->data->email) || $req->data->email == "" || !filter_var($req->data->email, FILTER_VALIDATE_EMAIL)) {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid email") 
    );
  } else if (!isset($req->data->tel) || $req->data->tel == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid telephone number") 
    );
  } else if (
    !isset($req->data->coordLat) || $req->data->coordLat == "" ||
    !isset($req->data->coordLong) || $req->data->coordLong == "" ||
    !is_numeric($req->data->coordLong) || !is_numeric($req->data->coordLat)
  ) {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid coordonnee") 
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
      if (Flight::validationNom('societeDesinfection', 'nom', $nom, Flight::db())) {
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
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage())
        );
      } else {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert")
        );
      }
    }
  }
});

//insert prestation pour societe desinfeciton
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'prestation', function () {
  Flight::getAccesControl();
  $req = Flight::request();
  if (!isset($req->data->prix) || $req->data->prix == "" || !is_numeric($req->data->prix)) {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid prix") 
    );
  } else if (!isset($req->data->societe) || $req->data->societe == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "id of societe invalid") 
    );
  } else if (!isset($req->data->description) || $req->data->description == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description") 
    );
  } else {
    try {
      Flight::db()->beginTransaction();
      $id = 'PRE' . GenericDb::formatNumber(GenericDb::getNextVal("seq_prestation", Flight::db()), Constante::$ID_COUNT);
      $description = $req->data->description;
      $prix = $req->data->prix;
      $societe = $req->data->societe;


      $prest = new Prestation($id, $description, $societe, $prix);

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
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage())
        );
      } else {

        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert")
        );
      }
    }
  }
});
//get protocole by societe
// ************
Flight::route('GET ' . Constante::$BASE . 'protocoles', function () {
  Flight::protectionPage("public");
  Flight::getAccesControlPublic();
  $req = Flight::request();

  if (!isset($req->query['societe']) || $req->query['societe'] === "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid societe") 
    );
  } else {
    try {
      $type = ""; //all,client,perso
      if (isset($req->query['type'])) $type = $req->query['type'];
      //client par defaut
      if ($type === "") $type = "client";

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
        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during getting protocole")
      );
    }
  }
});

//update protocole by societe
Flight::route('PUT ' . Constante::$BASE . 'protocoleChoisi', function () {
  Flight::getAccesControl();
  $req = Flight::request();

  if (!isset($req->data->societe) || $req->data->societe === "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid societe") 
    );
  } else if (!isset($req->data->protocoleChoisi) || $req->data->protocoleChoisi == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "ProtocoleChoisi not found") 
    );
  } else if (!isset($req->data->delete) || $req->data->delete == "") {
    Flight::json(
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "delete parameter not found") 
    );
  } else {
    try {
      Flight::db()->beginTransaction();
      //si delete
      if ($req->data->delete == "true") {
        Flight::deleteProtocoleSociete($req->data->societe, $req->data->protocoleChoisi, Flight::db());
      } else if ($req->data->delete == "false") {
        //si update
        Flight::updateDureeProtocoleSociete($req->data->societe, $req->data->protocoleChoisi, Flight::db());
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
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage())
        );
      } else {

        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during insert")
        );
      }
    }
  }
});

//get societeDesinfection
//mila hatao cursor based raha bedabe le donnee no sady real time fa zany hoe lasa tsisy mjump amna page secific
//ra limit sy offset de afaka mjump amna page specifique fa ra misy  manampy tampoka no blem,mo ra bdb le donnee de lent b
Flight::route('GET ' . Constante::$BASE . 'societeDesinfect', function () {

  Flight::getAccesControl();
  $req = Flight::request();
  if (isset($req->query['all']) || $req->query['all'] === "true") {
    try {
      $data = Flight::getAllSocieteDesinfection("", Flight::db());

      Flight::json(
        new ApiResponse("succes", Constante::$SUCCES_CODE['200'], $data, "société de desinfection"),
        Constante::$SUCCES_CODE['200']
      );
    } catch (Exception $ex) {

      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error during getting protocole")
      );
    }
  } else if (isset($req->query['page']) && isset($req->query['count'])) {
    if (!intval($req->query['page']) || !intval($req->query['count'])) {
      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "invalid parameter")
      );
    } else {
      $page = intval($req->query['page']);
      $count = intval($req->query['count']);
      $totalrow = Flight::Count("societeDesinfection", "", Flight::db());
      $totalPages = round($totalrow / $count);
      if ($page > $totalPages) {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "there is no more pages")
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
      new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "no parameter found") 
    );
  }
});
/*
  recherche tsotra
  pagination:mamerina ny next,mila mi implementer cache ho anle requette
*/

//q(le query apdirnle olona,afaka tsisy)
//cat=all(par defaut),ou misy(idcategorie)
//lat,lng(afaka tsisy,afaka misy fa tsmaints nombre valide ary tsmaints misy izy roa)
//raha tsisy lat,lng de recherche tsotra tsy eo am manodidina zany
//raha tsis inin mintsy afats lat,lng de mvoka eo dol ny societe eo am manodidina

//raha tena ho tsis dol reo rehetra reo fa categorie=all ihany de  tsy mamoka inin fa eo am accueil

Flight::route('GET ' . Constante::$BASE . 'search', function () {
  Flight::protectionPage("public-private");
  Flight::getAccesControlPublic();
  $req = Flight::request();
  try {


    if (!isset($req->query['cat']) || trim($req->query['cat']) === "") {
      //erreur satry tokony misy categorie fona
      throw new Exception("invalid request, no categorie found", Constante::$ERROR_CODE['400']);
    }
    //raha tsisy afats categorie
    if ((!isset($req->query['q']) || trim($req->query['q']) === "") && !isset($req->query['lat']) && !isset($req->query['lng'])) {
      //tsy mamerina inin rahta categorie=all
      if ($req->query['cat'] === "all") {
        Flight::json(
          new ApiResponse("succes", Constante::$SUCCES_CODE['200'], array(), "protocoles"),
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
        new ApiResponse("error", 400, null, $ex->getMessage())
      );
    } else {

      Flight::json(
        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, "server error, please contact the api provider")
      );
    }
  }
});
