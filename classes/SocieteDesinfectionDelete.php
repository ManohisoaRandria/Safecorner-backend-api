<?php
class SocieteDesinfectionDelete
{
    private $id;
    private $idSocieteDesinfection;
   

    function __construct($id,$idSocieteDesinfection)
    {
        $this->setId($id);
        $this->setIdSocieteDesinfection($idSocieteDesinfection);
    }
    public function delete(PDO $con)
    {
        try {
            GenericDb::insert($this, "societeDesinfectionDelete", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
   
    public function getId()
    {
        return $this->id;
    }

    
    public function setId($id)
    {
        $this->id = $id;
    }

   
    public function getIdSocieteDesinfection()
    {
        return $this->idSocieteDesinfection;
    }

    
    public function setIdSocieteDesinfection($idSocieteDesinfection)
    {
        $this->idSocieteDesinfection = $idSocieteDesinfection;
    }
}