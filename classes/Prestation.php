<?php
class Prestation implements JsonSerializable
{
    private $id;
    private $description;
    private $idSocieteDesinfection;
    private $prix;
    private $etat;

    function __construct($id, $description, $idSocieteDesinfection, $prix,$etat)
    {
        $this->setId($id);
        $this->setDescription($description);
        $this->setIdSocieteDesinfection($idSocieteDesinfection);
        $this->setPrix($prix);
        $this->setEtat($etat);
    }
    public function insert(PDO $con)
    {
        try {
            GenericDb::insert($this, "prestation", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function update(PDO $con)
    {
        try {
            GenericDb::update("prestation",array(
                "description"=>$this->description,
                "prix"=>$this->prix
            )," id='".$this->id."'",false,$con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function getById(PDO $con)
    {
        try {
            $Prestation = GenericDb::find(Prestation::class, "prestation", array("id" => $this->id), "", $con);
            return $Prestation;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'idSocieteDesinfection' => $this->idSocieteDesinfection,
            'prix' => $this->prix
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
     * Get the value of idSocieteDesinfection
     *
     * @return  mixed
     */
    public function getIdSocieteDesinfection()
    {
        return $this->idSocieteDesinfection;
    }

    /**
     * Set the value of idSocieteDesinfection
     *
     * @param   mixed  $idSocieteDesinfection  
     *
     * @return  self
     */
    public function setIdSocieteDesinfection($idSocieteDesinfection)
    {
        $this->idSocieteDesinfection = $idSocieteDesinfection;
    }


    public function getPrix()
    {
        return $this->prix;
    }


    public function setPrix($prix)
    {
        $this->prix = $prix;
    }

    /**
     * Get the value of etat
     *
     * @return  mixed
     */
    public function getEtat()
    {
        return $this->etat;
    }

    /**
     * Set the value of etat
     *
     * @param   mixed  $etat  
     *
     * @return  self
     */
    public function setEtat($etat)
    {
        $this->etat = $etat;
    }
}
