<?php

class SocieteDesinfection implements JsonSerializable
{
    private $id;
    private $nom;
    private $description;
    private $email;
    private $tel;
    private $lieu;
    private $dateCreation;
    private $coordonnee;

    function __construct($id, $nom, $description, $email, $tel, $lieu, $dateCreation, $coordonnee)
    {
        $this->setId($id);
        $this->setNom($nom);
        $this->setDescription($description);
        $this->setEmail($email);
        $this->setTel($tel);
        $this->setLieu($lieu);
        $this->setDateCreation($dateCreation);
        $this->setCoordonnee($coordonnee);
    }

    public function insert(PDO $con)
    {
        try {
            $sql = "INSERT INTO SocieteDesinfection(id,nom,description,email,tel,lieu,datecreation,coordonnee)
             VALUES(?,?,?,?,?,?,?,ST_GeomFromGeoJSON(?))";
            $result = $con->prepare($sql);
            $date = $this->getDateCreation()->format('Y-m-d H:i:s');
            $result->execute([
                $this->getId(), $this->getNom(), $this->getDescription(),
                $this->getEmail(), $this->getTel(), $this->getLieu(), $date, $this->getCoordonnee()
            ]);
            //GenericDb::insert($this,"societe",false,$con);
            //$con->commit();
        } catch (Exception $e) {
            //$con->rollback();
            throw $e;
        }
    }
    public function getPrestation(PDO $con)
    {
        try {
            $prestations = GenericDb::find(Prestation::class, "prestation", array("idSocieteDesinfection" => $this->id), "", $con);
            return $prestations;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function getById(PDO $con)
    { 
        $societeDesinfection = null;
        try {
            $sql = "SELECT id,nom,description,lieu,dateCreation,email,tel,
                ST_AsGeoJSON(coordonnee) as coordonnee FROM societedesinfection where id = '%s' ";
            $sql = sprintf($sql, $this->id);

            $res = $con->query($sql);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
                $societeDesinfection = new SocieteDesinfection(
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
        } catch (Exception $ex) {
            throw $ex;
        }
        return $societeDesinfection;
    }
    public function getByNom(PDO $con)
    {
        $societeDesinfection = null;
        try {
            $sql = "SELECT id,nom,description,lieu,dateCreation,email,tel,
                ST_AsGeoJSON(coordonnee) as coordonnee FROM societedesinfection where nom = '%s' ";
            $sql = sprintf($sql, $this->nom);

            $res = $con->query($sql);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
                $societeDesinfection = new SocieteDesinfection(
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
        } catch (Exception $ex) {
            throw $ex;
        }
        return $societeDesinfection;
    }
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'email' => $this->email,
            'tel' => $this->tel,
            'lieux' => $this->lieu,
            'dateCreation' => $this->dateCreation,
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
     * Get the value of description
     *
     * @return  mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param   mixed  $description  
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;
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
}
