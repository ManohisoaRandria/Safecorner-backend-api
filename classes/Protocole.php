<?php
class Protocole implements JsonSerializable
{
    private $id;
    private $nom;
    private $description;
    private $dateCreation;

    function __construct($id, $nom, $description, $dateCreation)
    {
        $this->setId($id);
        $this->setNom($nom);
        $this->setDescription($description);
        $this->setDateCreation($dateCreation);
    }
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'description' => $this->description,
            'dateCreation' => $this->dateCreation
        ];
    }
    public function insert(PDO $con)
    {
        try {
            GenericDb::insert($this, "protocole", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function getByNom(PDO $con)
    {
        try {
            $oneProtocole = GenericDb::find(Protocole::class, "protocole", array("nom" => $this->nom), "", $con);
            return $oneProtocole;
        } catch (Exception $ex) {
            throw $ex;
        }
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

    public function getNom()
    {
        return $this->nom;
    }


    public function setNom($nom)
    {
        $this->nom = $nom;
    }
}
