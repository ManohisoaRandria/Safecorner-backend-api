<?php
// insert Societe
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'societe', function () {
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
        } else if (!isset($req->data->coordLat) || $req->data->coordLat == "" || !isset($req->data->coordLong) || $req->data->coordLong == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid coordonnee"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->idCategorieSociete) || $req->data->idCategorieSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Categorie not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //Verification existance de nom
                if (Flight::validationNom('societe', 'nom', $req->data->nom, $con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("This name already exists.", Constante::$ERROR_CODE['400']);
                }
                //Verification existance de email
                if (Flight::validationNom('societe', 'email', $req->data->email, $con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("This email already exists.", Constante::$ERROR_CODE['400']);
                }
                //verification type variable lat et long
                if (!is_numeric($req->data->coordLat) || !is_numeric($req->data->coordLong)) {
                    throw new Exception("Variable type latitude and longitude no numeric.", Constante::$ERROR_CODE['400']);
                }
                //verification de l'existance du categorie societe
                if (!Flight::validationNom('categoriesociete', 'id', $req->data->idCategorieSociete, $con)) {
                    throw new Exception("Categorie societe not found.", Constante::$ERROR_CODE['400']);
                }
                //Données
                $id = 'SOC' . GenericDb::formatNumber(GenericDb::getNextVal("seq_societe", $con), Constante::$ID_COUNT);
                $idCategoriteSociete = $req->data->idCategorieSociete;
                $nom = $req->data->nom;
                $email = $req->data->email;
                $lieu = $req->data->lieu;
                $description = $req->data->description;
                $tel = $req->data->tel;
                $coordonnee = 'SRID=4326;POINT(%.8f %.8f)';
                $coordonnee = sprintf($coordonnee, $req->data->coordLat, $req->data->coordLong);
                $date = new DateTime("now",new DateTimeZone('Africa/Nairobi'));
                $res = new Societe($id, $nom, $idCategoriteSociete, $description, $lieu, $date, $email, $tel, $coordonnee);
                //insertion
                $res->insert($con);
                $con->commit();

                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Societe inserted"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                $con->rollBack();
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// ajout protocole societe
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'addProtocoleChoisi', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->data->idSociete) || $req->data->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->idCategorieProtocole) || $req->data->idCategorieProtocole == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "CategorieProtocole not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->protocoleChoisi) || $req->data->protocoleChoisi == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "ProtocoleChoisi not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //verification:
                //Donnee 
                $idSociete = $req->data->idSociete;
                $idCategorieProtocole = $req->data->idCategorieProtocole;
                $protocoleChoisi = $req->data->protocoleChoisi;
                $societe = new Societe($idSociete, null, null, null, null, null, null, null, null);
                $societe = $societe->getById($con);
                //Action: ajoute se qui manque et supprime se qui ne sont pas dans le $protocoleChoisi
                $nbWarn = Flight::addProtocoleChoisi($societe, $idCategorieProtocole, $protocoleChoisi, $con);
                //resultat
                if ($nbWarn > 0) {
                    Flight::json(
                        new ApiResponse("succes", Constante::$SUCCES_CODE['201'], null, "ProtocoleChoisi add but " . $nbWarn . " already exist"),
                        Constante::$SUCCES_CODE['201']
                    );
                } else {
                    Flight::json(
                        new ApiResponse("succes", Constante::$SUCCES_CODE['201'], null, "ProtocoleChoisi add"),
                        Constante::$SUCCES_CODE['201']
                    );
                }
                $con->commit();
            } catch (Exception $e) {
                $con->rollback();
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
      }
});
// insert categorie societe
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'categorieSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->data->description) || $req->data->description == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //verification existance description
                if (Flight::validationNom('categorieSociete', 'description', $req->data->description, $con)) {
                    throw new Exception("This description already exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnees
                $id = 'CS' . GenericDb::formatNumber(GenericDb::getNextVal("seq_categoriesociete", $con), Constante::$ID_COUNT);
                $description = $req->data->description;
                //Insertion 
                $res = new CategorieSociete($id, $description);
                $res->insert($con);
                $con->commit();
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Categorie societe inserted"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                $con->rollBack();
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// insert categorie protocole
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'categorieProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->data->description) || $req->data->description == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //verification existance description
                if (Flight::validationNom('categorieprotocole', 'description', $req->data->description, $con)) {
                    throw new Exception("This description already exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnees
                $id = 'CTP' . GenericDb::formatNumber(GenericDb::getNextVal("seq_categorieprotocole", $con), Constante::$ID_COUNT);
                $description = $req->data->description;
                //Insertion 
                $res = new CategorieProtocole($id, $description);
                $res->insert($con);
                $con->commit();
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Categorie societe inserted"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                $con->rollBack();
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// insert historique descente
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'historiqueDescente', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->data->idSociete) || $req->data->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->description) || $req->data->description == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid description"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->nombreProtocole) ||!is_numeric( $req->data->nombreProtocole )) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Number of protocols not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //Verification: existance Societe
                if (!Flight::validationNom("societe", "id", $req->data->idSociete,$con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("This societe does not exist.", Constante::$ERROR_CODE['400']);
                }
                //Donnees
                $id = 'HTDE' . GenericDb::formatNumber(GenericDb::getNextVal("seq_historiquedescente", $con), Constante::$ID_COUNT);
                $idSociete = $req->data->idSociete;
                $description = $req->data->description;
                $points = Flight::calculePoint($idSociete, $req->data->nombreProtocole, $con);
                $date = new DateTime("now",new DateTimeZone('Africa/Nairobi'));
                //Insertion 
                $res = new HistoriqueDescente($id, $idSociete, $description, $points, $date, Constante::$DESCENTE_VALIDE);
                $res->insert($con);
                $con->commit();
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "historique descente inserted"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                $con->rollBack();
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get maka an ze protocole tsy ao amin'ilay Societe
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'outProtocoleSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->query->idSociete) || $req->query->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "societe not found"),
                Constante::$ERROR_CODE['400']
            );
        }else if (!isset($req->query->idCategorieProtocole) || $req->query->idCategorieProtocole == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Categorie protocole not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Verification: existance Societe
                if (!Flight::validationNom("societe", "id", $req->query->idSociete,$con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("This societe does not exist.", Constante::$ERROR_CODE['400']);
                }
                //Verification: existance categorie protocole
                if (!Flight::validationNom("categorieProtocole", "id", $req->query->idCategorieProtocole, $con)) {
                    throw new Exception("This categorie does not exist.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSociete = $req->query->idSociete;
                $idCategorieProtocole = $req->query->idCategorieProtocole;
                //Action: prendre les protocole que le societe ne fait pas
                $res = Flight::getOutProtocoleBySociete($idSociete,$idCategorieProtocole, $con);
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// login
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'user/login', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        $req = Flight::request();
        if (!isset($req->data->nom) || $req->data->nom == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid name"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->mdp) || $req->data->mdp == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid password"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Donnee
                $nom = $req->data->nom;
                $mdp = $req->data->mdp;
                //Action: login
                $res = Flight::signIn($nom, $mdp, $con);
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes login"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() != 500 && $e->getCode() != 503) {
                    Flight::json(
                        new ApiResponse("error", $e->getCode(), null, $e->getMessage()),
                        $e->getCode()
                    );
                } else {
                    Flight::json(
                        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, $e->getMessage()),
                        Constante::$ERROR_CODE['500']
                    );
                }
            } finally {
                $con = null;
            }
        }
    }
});
// inscription
Flight::route('POST|OPTIONS ' . Constante::$BASE . 'user/registration', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        $req = Flight::request();
        if (!isset($req->data->nom) || $req->data->nom == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid name."),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->mdp) || $req->data->mdp == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid password."),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Vaerification: existance nom
                if (Flight::validationNom("users", "nom", $req->data->nom, $con)) {
                    throw new Exception("This name is already taken.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $nom = $req->data->nom;
                $mdp = $req->data->mdp;
                //Action: login
                $res = Flight::signUp($nom, $mdp, $con);
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes registration"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
                    Flight::json(
                        new ApiResponse("error", $e->getCode(), null, $e->getMessage()),
                        $e->getCode()
                    );
                } else {
                    Flight::json(
                        new ApiResponse("error", Constante::$ERROR_CODE['500'], null, $e->getMessage()),
                        Constante::$ERROR_CODE['500']
                    );
                }
            } finally {
                $con = null;
            }
        }
    }
});
// get maka societeDesinfection iray misraka amin'ny prestationany
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'oneSocieteDesinfection', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("public-private");
        $req = Flight::request();
        if (!isset($req->query->idSocieteDesinfection) || $req->query->idSocieteDesinfection == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe desinfection not found."),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Vaerification: existance societe
                if (!Flight::validationNom("societedesinfection", "id", $req->query->idSocieteDesinfection,$con," and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
                    throw new Exception("Societe desinfection not exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSocieteDesinfection = $req->query->idSocieteDesinfection;
                //Action: login
                $temp = new SocieteDesinfection($idSocieteDesinfection, null, null, null, null, null, null, null);
                $societeDesinfection = $temp->getById($con);
                $prestations = $temp->getPrestation($con);
                $res = [
                    "societeDesinfection" => $societeDesinfection,
                    "prestation" => $prestations
                ];
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get maka an societe rehetra misy pagination
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'allSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->query->all) || ($req->query->all != "true" && $req->query->all != "false")) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "type of get societe not specify(all:true or false)"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->page) || !is_numeric($req->query->page)) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid page or not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->limit) || !is_numeric($req->query->limit)) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid limit or not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Donnee
                $all = $req->query->all;
                $page = $req->query->page;
                $limit = $req->query->limit;
                //Action: login
                $res = Flight::getAllSociete($page, $limit, $all, $con);
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get maka an protocole rehetra misy pagination
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'allProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->query->all) || ($req->query->all != "true" && $req->query->all != "false")) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "type of get protocole not specify(all:true or false)"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->page) || !is_numeric($req->query->page)) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid page or not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->limit) || !is_numeric($req->query->limit)) {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid limit or not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Donnee
                $all = $req->query->all;
                $page = $req->query->page;
                $limit = $req->query->limit;
                //Action: login
                $res = Flight::getAllProtocole($page, $limit, $all, $con);
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get categorie societe
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'categorieSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("public-private");
        $req = Flight::request();
        $con = Flight::db();
        try {
            //Action: prendre les categories de societe
            $res = Flight::getCategorieSociete($con);
            //resultat
            Flight::json(
                new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                Constante::$SUCCES_CODE['201']
            );
        } catch (Exception $e) {
            if ($e->getCode() == 400) {
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
        } finally {
            $con = null;
        }
    }
});
// get categorie protocole
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'categorieProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $con = Flight::db();
        try {
            //Action: prendre les categories de protocole
            $res = Flight::getCategorieProtocole($con);
            //resultat
            Flight::json(
                new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                Constante::$SUCCES_CODE['201']
            );
        } catch (Exception $e) {
            if ($e->getCode() == 400) {
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
        } finally {
            $con = null;
        }
    }
});
// get maka historique an le changement an le protocole
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueChangementProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        $con = Flight::db();
        if (!isset($req->query->idSociete) || $req->query->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->mois) || !is_numeric($req->query->mois) || $req->query->mois == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid mois"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->annee) || !is_numeric($req->query->annee) || $req->query->annee == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid annee"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            try {
                //Vaerification: existance societe
                if (!Flight::validationNom("societe", "id", $req->query->idSociete,$con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("Societe not exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSociete = $req->query->idSociete;
                $mois = $req->query->mois;
                $annee = $req->query->annee;
                //Action: prendre les HistoriqueChangementprotocole de la societe par mois/annee 
                $temp = new Societe($idSociete, null, null, null, null, null, null, null, null);
                $Societe = $temp->getById($con);
                $historiqueChangementProtocoles = $Societe->getHistoriqueChangementProtocoleDetail($mois, $annee, $con);
                $res = [
                    "societe" => $Societe,
                    "historiqueChangementProtocole" => $historiqueChangementProtocoles
                ];
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get maka historique an le changement an le protocole androany
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueChangementProtocoleToDay', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        $con = Flight::db();
        if (!isset($req->query->idSociete) || $req->query->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            try {
                //Vaerification: existance societe
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("Societe not exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSociete = $req->query->idSociete;
                //Action: prendre les HistoriqueChangementprotocole de la societe par mois/annee 
                $temp = new Societe($idSociete, null, null, null, null, null, null, null, null);
                $Societe = $temp->getById($con);
                $historiqueChangementProtocoles = $Societe->getHistoriqueChangementProtocoleDetailToDay($con);
                $res = [
                    "societe" => $Societe,
                    "historiqueChangementProtocole" => $historiqueChangementProtocoles
                ];
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get maka historique an le descente
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueDescente', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        $con = Flight::db();
        if (!isset($req->query->idSociete) || $req->query->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->mois) || !is_numeric($req->query->mois) || $req->query->mois == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid mois"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->query->annee) || !is_numeric($req->query->annee) || $req->query->annee == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid annee"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            try {
                //Vaerification: existance societe
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("Societe not exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSociete = $req->query->idSociete;
                $mois = $req->query->mois;
                $annee = $req->query->annee;
                //Action: prendre les HistoriqueChangementprotocole de la societe par mois/annee 
                $temp = new Societe($idSociete, null, null, null, null, null, null, null, null);
                $Societe = $temp->getById($con);
                $historiqueDescente = $Societe->getHistoriqueDescenete($mois, $annee, $con);
                $res = [
                    "societe" => $Societe,
                    "historiqueDescente" => $historiqueDescente
                ];
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// get maka historique an le descente androany
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueDescenteToDay', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        $con = Flight::db();
        if (!isset($req->query->idSociete) || $req->query->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            try {
                //Vaerification: existance societe
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("Societe not exists.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSociete = $req->query->idSociete;
                //Action: prendre les HistoriqueChangementprotocole de la societe par mois/annee 
                $temp = new Societe($idSociete, null, null, null, null, null, null, null, null);
                $Societe = $temp->getById($con);
                $historiqueDescente = $Societe->getHistoriqueDescenteToDay($con);
                $res = [
                    "societe" => $Societe,
                    "historiqueDescente" => $historiqueDescente
                ];
                //resultat
                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Succes"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
// update societe
Flight::route('PUT|OPTIONS ' . Constante::$BASE . 'societe', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        Flight::protectionPage("private");
        $req = Flight::request();
        if (!isset($req->data->id) || $req->data->id == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->nom) || $req->data->nom == "") {
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
        } else if (!isset($req->data->coordLat) || $req->data->coordLat == "" || !isset($req->data->coordLong) || $req->data->coordLong == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid coordonnee"),
                Constante::$ERROR_CODE['400']
            );
        } else if (!isset($req->data->idCategorieSociete) || $req->data->idCategorieSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Categorie not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //Verification existance de id
                if (!Flight::validationNom('societe', 'id', $req->data->id, $con," and id not in (select idSociete from societedelete)")) {
                    throw new Exception("Societe not found.", Constante::$ERROR_CODE['400']);
                }
                //Verification existance de nom
                if (Flight::validationNom('societe', 'nom', $req->data->nom, $con," and id != '".$req->data->id."' and id not in (select idSociete from societedelete)")) {
                    throw new Exception("This name already taken.", Constante::$ERROR_CODE['400']);
                }
                //Verification existance de email
                if (Flight::validationNom('societe', 'email', $req->data->email, $con," and id != '".$req->data->id."' and id not in (select idSociete from societedelete)")) {
                    throw new Exception("This email already taken.", Constante::$ERROR_CODE['400']);
                }
                //verification type variable lat et long
                if (!is_numeric($req->data->coordLat) || !is_numeric($req->data->coordLong)) {
                    throw new Exception("Variable type latitude and longitude no numeric.", Constante::$ERROR_CODE['400']);
                }
                //verification de l'existance du categorie societe
                if (!Flight::validationNom('categoriesociete', 'id', $req->data->idCategorieSociete, $con)) {
                    throw new Exception("Categorie societe not found.", Constante::$ERROR_CODE['400']);
                }
                //Données
                $id = $req->data->id;
                $idCategoriteSociete = $req->data->idCategorieSociete;
                $nom = $req->data->nom;
                $email = $req->data->email;
                $lieu = $req->data->lieu;
                $description = $req->data->description;
                $tel = $req->data->tel;
                $coordonnee = 'SRID=4326;POINT(%.8f %.8f)';
                $coordonnee = sprintf($coordonnee, $req->data->coordLat, $req->data->coordLong);
                $res = new Societe($id, $nom, $idCategoriteSociete, $description, $lieu,null, $email, $tel, $coordonnee);
                //insertion
                $res->update($con);
                $con->commit();

                Flight::json(
                    new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $res, "Updated societe"),
                    Constante::$SUCCES_CODE['201']
                );
            } catch (Exception $e) {
                $con->rollBack();
                if ($e->getCode() == 400) {
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
            } finally {
                $con = null;
            }
        }
    }
});
//update societe desinfection
Flight::route('PUT|OPTIONS ' . Constante::$BASE . 'societeDesinfect', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      Flight::json(
        "OK",
        200
      );
    } else {
      Flight::protectionPage("private");
      $req = Flight::request();
      if (!isset($req->data->id) || $req->data->id == "") {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Societe desinfection not found"),
          Constante::$ERROR_CODE['400']
        );
      }else if (!isset($req->data->nom) || $req->data->nom == "") {
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
        $con = Flight::db();
        try {
         $con->beginTransaction();
          $id = $req->data->id;
          $nom = $req->data->nom;
          $email = $req->data->email;
          $lieu = $req->data->lieu;
          $description = $req->data->description;
          $tel = $req->data->tel;
          $coordonnee = 'SRID=4326;POINT(%.8f %.8f)';
          $coordonnee = sprintf($coordonnee, $req->data->coordLat, $req->data->coordLong);
  
          $societedes = new SocieteDesinfection(
            $id,
            $nom,
            $description,
            $email,
            $tel,
            $lieu,
            null,
            $coordonnee
          );
          //if there is a duplicate name
          if (!Flight::validationNom('societeDesinfection', 'id',$id, $con," and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
            throw new Exception("Societe desinfection not found.", Constante::$ERROR_CODE['400']);
          } else if (Flight::validationNom('societeDesinfection', 'nom', $nom, $con,"  and id != '".$id."' and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
            throw new Exception("found duplicate nom", Constante::$ERROR_CODE['400']);
          } else {
            $societedes->update($con);
            $con->commit();
            Flight::json(
              new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $societedes, "Updated societe de desinfection"),
              Constante::$SUCCES_CODE['201']
            );
          }
        } catch (Exception $ex) {
          $con->rollBack();
          if ($ex->getCode() == 400) {
            Flight::json(
              new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
              Constante::$ERROR_CODE['400']
            );
          } else {
            Flight::json(
              new ApiResponse("error", Constante::$ERROR_CODE['500'], null, $ex->getMessage()),
              Constante::$ERROR_CODE['500']
            );
          }
        }
      }
    }
});
//Update prestation
Flight::route('PUT|OPTIONS ' . Constante::$BASE . 'prestation', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      Flight::json(
        "OK",
        200
      );
    } else {
      Flight::protectionPage("private");
      $req = Flight::request();
      if (!isset($req->data->id) || $req->data->id == "") {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Prestation not found."),
          Constante::$ERROR_CODE['400']
        );
      } else if (!isset($req->data->prix) || $req->data->prix == "" || !is_numeric($req->data->prix)) {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Invalid prix"),
          Constante::$ERROR_CODE['400']
        );
      } else if (!isset($req->data->nom) || $req->data->nom == "") {
        Flight::json(
          new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "nom invalid"),
          Constante::$ERROR_CODE['400']
        );
      }else if (!isset($req->data->idSocieteDesinfection) || $req->data->idSocieteDesinfection == "") {
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
        $con = Flight::db();
        try {
          $con->beginTransaction();
          //Verification existance de id
          if (!Flight::validationNom('prestation','id',$req->data->id,$con," and etat ='".Constante::$PRESTATION_ACTIVE."'")) {
            throw new Exception("Prestation not found.", Constante::$ERROR_CODE['400']);
          }
          //Verification existance de idSociete
          if (!Flight::validationNom('societedesinfection', 'id', $req->data->idSocieteDesinfection, $con," and id not in (select idSocieteDesinfection from societedesinfectiondelete)")) {
            throw new Exception("Societe desinfection not found.", Constante::$ERROR_CODE['400']);
          }
          $id = $req->data->id;
          $description = $req->data->description;
          $prix = $req->data->prix;
          $societe = $req->data->idSocieteDesinfection;
          $nom = $req->data->nom;
          $prest = new Prestation($id,$nom ,$description, $societe, $prix,Constante::$PRESTATION_ACTIVE);
  
          $prest->update($con);
          $con->commit();
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['201'], $prest, "prestaiton for societe:" . $societe . " updated"),
            Constante::$SUCCES_CODE['201']
          );
        } catch (Exception $ex) {
            $con->rollBack();
          if ($ex->getCode() == 400) {
            Flight::json(
              new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
              Constante::$ERROR_CODE['400']
            );
          } else {
  
            Flight::json(
              new ApiResponse("error", Constante::$ERROR_CODE['500'], null,$ex->getMessage()),
              Constante::$ERROR_CODE['500']
            );
          }
        }
      }
    }
});
// get nombreData
Flight::route('GET|OPTIONS ' . Constante::$BASE . 'numberData', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
      Flight::json(
        "OK",
        200
      );
    } else {
        Flight::protectionPage("private");
        $con = Flight::db();
        try {
          $res = Flight::getNumberData($con);
          Flight::json(
            new ApiResponse("succes", Constante::$SUCCES_CODE['200'],$res,"get success"),
            Constante::$SUCCES_CODE['200']
          );
        } catch (Exception $ex) {
          if ($ex->getCode() == 400) {
            Flight::json(
              new ApiResponse("error", Constante::$ERROR_CODE['400'], null, $ex->getMessage()),
              Constante::$ERROR_CODE['400']
            );
          } else {
            Flight::json(
              new ApiResponse("error", Constante::$ERROR_CODE['500'], null,$ex->getMessage()),
              Constante::$ERROR_CODE['500']
            );
          }
        }
    }
});