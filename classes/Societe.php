<?php


class Societe implements JsonSerializable
{
    private $id;
    private $nom;
    private $idCategorieSociete;
    private $description;
    private $lieu;
    private $dateCreation;
    private $email;
    private $tel;
    private $coordonnee;

    public function __construct($id, $nom, $idCategorieSociete, $description, $lieu, $dateCreation, $email, $tel, $coordonnee)
    {
        $this->setId($id);
        $this->setNom($nom);
        $this->setIdCategorieSociete($idCategorieSociete);
        $this->setDescription($description);
        $this->setLieu($lieu);
        $this->setDateCreation($dateCreation);
        $this->setEmail($email);
        $this->setTel($tel);
        $this->setCoordonnee($coordonnee);
    }

    public function getById(PDO $con)
    {
        try {
            $societe = null;
            $sql = "SELECT id,nom,idCategoriesociete,description,lieu,dateCreation,email,tel,
                ST_AsGeoJSON(coordonnee) as coordonnee FROM societe where id = '%s' ";
            $sql = sprintf($sql, $this->id);

            $res = $con->query($sql);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
                $societe = new Societe(
                    $donnees['id'],
                    $donnees['nom'],
                    $donnees['idcategoriesociete'],
                    $donnees['description'],
                    $donnees['lieu'],
                    $donnees['datecreation'],
                    $donnees['email'],
                    $donnees['tel'],
                    $donnees['coordonnee']
                );
            }
            $res->closeCursor();
            return $societe;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'idCategorieSociete' => $this->idCategorieSociete,
            'description' => $this->description,
            'lieux' => $this->lieu,
            'dateCreation' => $this->dateCreation,
            'email' => $this->email,
            'tel' => $this->tel,
            'coordonne' => $this->coordonnee
        ];
    }
    /**
     * Get the value of id
     *
     * @return  mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @param   mixed  $id  
     *
     * @return  self
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Get the value of idCategorieSociete
     *
     * @return  mixed
     */
    public function getIdCategorieSociete()
    {
        return $this->idCategorieSociete;
    }

    /**
     * Set the value of idCategorieSociete
     *
     * @param   mixed  $idCategorieSociete  
     *
     * @return  self
     */
    public function setIdCategorieSociete($idCategorieSociete)
    {
        $this->idCategorieSociete = $idCategorieSociete;
    }

    /**
     * Get the value of nom
     *
     * @return  mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     *
     * @param   mixed  $nom  
     *
     * @return  self
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Get the value of descritpion
     *
     * @return  mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of descritpion
     *
     * @param   mixed  $descritpion  
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the value of lieu
     *
     * @return  mixed
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set the value of lieu
     *
     * @param   mixed  $lieu  
     *
     * @return  self
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
    }

    /**
     * Get the value of dateCreation
     *
     * @return  mixed
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
    }

    /**
     * Set the value of dateCreation
     *
     * @param   mixed  $dateCreation  
     *
     * @return  self
     */
    public function setDateCreation($dateCreation)
    {
        $this->dateCreation = $dateCreation;
    }

    /**
     * Get the value of email
     *
     * @return  mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param   mixed  $email  
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get the value of tel
     *
     * @return  mixed
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set the value of tel
     *
     * @param   mixed  $tel  
     *
     * @return  self
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
    }

    /**
     * Get the value of coordonnee
     *
     * @return  mixed
     */
    public function getCoordonnee()
    {
        return $this->coordonnee;
    }

    /**
     * Set the value of coordonnee
     *
     * @param   mixed  $coordonnee  
     *
     * @return  self
     */
    public function setCoordonnee($coordonnee)
    {
        $this->coordonnee = $coordonnee;
    }

    //insert societe
    public function insert(PDO $con)
    {
        try {
            $sql = "INSERT INTO societe(id,nom,idcategoriesociete,description,lieu,datecreation,email,tel,coordonnee) VALUES(?,?,?,?,?,?,?,?,ST_GeomFromText(?))";
            $result = $con->prepare($sql);
            $date = $this->getDateCreation()->format('Y-m-d H:i:s');
            $result->execute([$this->getId(), $this->getNom(), $this->getIdCategorieSociete(), $this->getDescription(), $this->getLieu(), $date, $this->getEmail(), $this->getTel(), $this->getCoordonnee()]);
            //GenericDb::insert($this,"societe",false,$con);
            //$con->commit();
        } catch (Exception $e) {
            //$con->rollback();
            throw $e;
        }
    }
    //get all protocole
    public function getAllProtocolesChoisi($categorieProtocole, $con)
    {
        $res = null;
        try {
            $temp = new ProtocoleChoisi(null, null, null, null, null, null, null);
            $res = GenericDb::find($temp, 'protocoleChoisi', array('idsociete' => $this->getId(), 'idcategorieprotocole' => $categorieProtocole), "", $con);
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }

    //getCountProtocoleSociete
    public function getCountProtocole($con)
    {
        try {
            $res = 0;
            $sql = "select count(*) as nb from protocolechoisi where idsociete = ? and etat='1'";
            $exec = $con->prepare($sql);
            $exec->execute([$this->getId()]);
            $result = $exec->fetchAll(PDO::FETCH_ASSOC);
            if (Count($result) > 1) {
                throw new Exception("Error in server: count protocole societe", Constante::$ERROR_CODE['500']);
            }
            foreach ($result as $data) {
                $res = $data['nb'];
                break;
            }
            return $res;
        } catch (Exception $e) {
            throw $e;
        }
    }

    //getHistoriqueChangementProtocoleDetail To Day
    public function getHistoriqueChangementProtocoleDetailToDay(PDO $con)
    {
        $res = array();
        try {
            //conrolle valeur
            if ($this->id == "" || $this->id == null) {
                throw new Exception("Societe not found.", Constante::$ERROR_CODE['400']);
            }
            $nowDate = new DateTime();
            $nowString = $nowDate->format('Y-m-d');
            $afterWhere = " where idsociete='%s' and 
                datechangement >= '%s 00:00:00' and 
                datechangement <= '%s 23:59:59' 
                order by datechangement desc;";
            $afterWhere = sprintf($afterWhere, $this->id, $nowString, $nowString);

            $res = GenericDb::find(
                HistoriqueChangementProtocoleDetail::class,
                'HistoriqueChangementProtocoleDetail',
                array(),
                $afterWhere,
                $con
            );
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }

    //getHistoriqueChangementProtocoleDetail
    public function getHistoriqueChangementProtocoleDetail(int $mois, int $annee, PDO $con)
    {
        $res = array();
        try {
            //conrolle valeur
            if ($this->id == "" || $this->id == null) {
                throw new Exception("Societe not found.", Constante::$ERROR_CODE['400']);
            } else if ($mois < 1 || $mois > 12) {
                throw new Exception("mois must from 1 to 12", Constante::$ERROR_CODE['400']);
            } else if ($annee < 1900) {
                throw new Exception("annee < 1900", Constante::$ERROR_CODE['400']);
            }
            $afterWhere = "Where 
                idsociete = '%s' and
                EXTRACT(YEAR FROM datechangement) = %f and 
                EXTRACT(MONTH FROM datechangement) = %f 
                order by datechangement desc";
            $afterWhere = sprintf($afterWhere, $this->id, $annee, $mois);
            $res = GenericDb::find(
                HistoriqueChangementProtocoleDetail::class,
                'HistoriqueChangementProtocoleDetail',
                array(),
                $afterWhere,
                $con
            );
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }

    //getHistoriqueDescente
    public function getHistoriqueDescenete(int $mois, int $annee, PDO $con)
    {
        $res = array();
        try {
            //conrolle valeur
            if ($this->id == "" || $this->id == null) {
                throw new Exception("Societe not found.", Constante::$ERROR_CODE['400']);
            } else if ($mois < 1 || $mois > 12) {
                throw new Exception("mois must from 1 to 12", Constante::$ERROR_CODE['400']);
            } else if ($annee < 1900) {
                throw new Exception("annee < 1900", Constante::$ERROR_CODE['400']);
            }
            $afterWhere = "Where 
                idsociete = '%s' and
                EXTRACT(YEAR FROM datecreation) = %f and 
                EXTRACT(MONTH FROM datecreation) = %f 
                order by datecreation desc";
            $afterWhere = sprintf($afterWhere, $this->id, $annee, $mois);
            $res = GenericDb::find(
                HistoriqueDescente::class,
                'historiqueDescente',
                array(),
                $afterWhere,
                $con
            );
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }

    //getHistoriqueDescenete To Day
    public function getHistoriqueDescenteToDay(PDO $con)
    {
        $res = array();
        try {
            //conrolle valeur
            if ($this->id == "" || $this->id == null) {
                throw new Exception("Societe not found.", Constante::$ERROR_CODE['400']);
            }
            $nowDate = new DateTime();
            $nowString = $nowDate->format('Y-m-d');
            $afterWhere = " where idsociete='%s' and 
                datecreation >= '%s 00:00:00' and 
                datecreation <= '%s 23:59:59' 
                order by datecreation desc;";
            $afterWhere = sprintf($afterWhere, $this->id, $nowString, $nowString);

            $res = GenericDb::find(
                HistoriqueDescente::class,
                'historiqueDescente',
                array(),
                $afterWhere,
                $con
            );
        } catch (Exception $e) {
            throw $e;
        }
        return $res;
    }
}
