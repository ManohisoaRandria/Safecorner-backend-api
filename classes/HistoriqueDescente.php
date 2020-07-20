<?php 
class HistoriqueDescente implements JsonSerializable
{
    private $id;
    private $idSociete;
    private $description;
    private $points;
    private $dateCreation;
    private $etat;

    function __construct($id, $idSociete, $description, $points, $dateCreation, $etat)
    {
        $this->setId($id);
        $this->setIdSociete($idSociete);
        $this->setDescription($description);
        $this->setDateCreation($dateCreation);
        $this->setPoints($points);
        $this->setEtat($etat);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'idSociete' => $this->idSociete,
            'description' => $this->description,
            'points' => $this->points,
            'dateCreation' => $this->dateCreation,
            'etat' => $this->etat
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
     * Get the value of idSociete
     *
     * @return  mixed
     */
    public function getIdSociete()
    {
        return $this->idSociete;
    }

    /**
     * Set the value of idSociete
     *
     * @param   mixed  $idSociete  
     *
     * @return  self
     */
    public function setIdSociete($idSociete)
    {
        $this->idSociete = $idSociete;
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


    public function getPoints()
    {
        return $this->points;
    }


    public function setPoints($points)
    {
        $this->points = $points;
    }


    public function getEtat()
    {
        return $this->etat;
    }


    public function setEtat($etat)
    {
        $this->etat = $etat;
    }

    //insert historique descente
    public function insert($con){
        try{
            GenericDb::insert($this,'historiquedescente',false,$con);
        }
        catch(Exception $e){
            throw $e;
        }
    }
}
