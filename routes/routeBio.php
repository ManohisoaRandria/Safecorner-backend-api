<?php

Flight::route('POST|OPTIONS ' . Constante::$BASE . 'societe', function () {
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
                if (Flight::validationNom('societe', 'nom', $req->data->nom, $con)) {
                    throw new Exception("This name already exists.", Constante::$ERROR_CODE['400']);
                }
                //Verification existance de email
                if (Flight::validationNom('societe', 'email', $req->data->email, $con)) {
                    throw new Exception("This email already exists.", Constante::$ERROR_CODE['400']);
                }
                //verification type variable lat et long
                if (!is_numeric($req->data->coordLat) || !is_numeric($req->data->coordLong)) {
                    throw new Exception("Variable type latitude and longitude no numeric.", Constante::$ERROR_CODE['400']);
                }
                //Données
                $id = 'SOC' . GenericDb::formatNumber(GenericDb::getNextVal("seq_protocole", $con), Constante::$ID_COUNT);
                $idCategoriteSociete = $req->data->idCategorieSociete;
                $nom = $req->data->nom;
                $email = $req->data->email;
                $lieu = $req->data->lieu;
                $description = $req->data->description;
                $tel = $req->data->tel;
                $coordonnee = 'SRID=4326;POINT(%.8f %.8f)';
                $coordonnee = sprintf($coordonnee, $req->data->coordLat, $req->data->coordLong);
                $date = new DateTime();
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

Flight::route('POST|OPTIONS ' . Constante::$BASE . 'addProtocoleChoisi', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('POST|OPTIONS ' . Constante::$BASE . 'categorieSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('POST|OPTIONS ' . Constante::$BASE . 'categorieProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('POST|OPTIONS ' . Constante::$BASE . 'historiqueDescente', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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
        } else if (!isset($req->data->nombreProtocole) || $req->data->nombreProtocole == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "Number of protocols not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                $con->beginTransaction();
                //Verification: existance Societe
                if (!Flight::validationNom("societe", "id", $req->data->idSociete, $con)) {
                    throw new Exception("This societe does not exist.", Constante::$ERROR_CODE['400']);
                }
                //Donnees
                $id = 'HTDE' . GenericDb::formatNumber(GenericDb::getNextVal("seq_historiquedescente", $con), Constante::$ID_COUNT);
                $idSociete = $req->data->idSociete;
                $description = $req->data->description;
                $points = Flight::calculePoint($idSociete, $req->data->nombreProtocole, $con);
                $date = new DateTime();
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'outProtocoleSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
        $req = Flight::request();
        if (!isset($req->query->idSociete) || $req->query->idSociete == "") {
            Flight::json(
                new ApiResponse("error", Constante::$ERROR_CODE['400'], null, "societe not found"),
                Constante::$ERROR_CODE['400']
            );
        } else {
            $con = Flight::db();
            try {
                //Verification: existance Societe
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con)) {
                    throw new Exception("This societe does not exist.", Constante::$ERROR_CODE['400']);
                }
                //Donnee
                $idSociete = $req->query->idSociete;
                //Action: prendre les protocole que le societe ne fait pas
                $res = Flight::getOutProtocoleBySociete($idSociete, $con);
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'oneSocieteDesinfection', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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
                if (!Flight::validationNom("societedesinfection", "id", $req->query->idSocieteDesinfection, $con)) {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'allSociete', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'allProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'categorieSociete', function () {
    Flight::getAccesControlPublic();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'categorieProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueChangementProtocole', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con)) {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueChangementProtocoleToDay', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con)) {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueDescente', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con)) {
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

Flight::route('GET|OPTIONS ' . Constante::$BASE . 'historiqueDescenteToDay', function () {
    Flight::getAccesControl();
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        Flight::json(
          "OK",
          200
        );
    } else {
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
                if (!Flight::validationNom("societe", "id", $req->query->idSociete, $con)) {
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
