<?php
class SocieteDelete
{
    private $id;
    private $idSociete;
   

    function __construct($id,$idSociete)
    {
        $this->setId($id);
        $this->setidSociete($idSociete);
    }

    public function delete(PDO $con)
    {
        try {
            GenericDb::insert($this, "societeDelete", false, $con);
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

   
    public function getIdSociete()
    {
        return $this->idSociete;
    }

    
    public function setIdSociete($idSociete)
    {
        $this->idSociete = $idSociete;
    }
}