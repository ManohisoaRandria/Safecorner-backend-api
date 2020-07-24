<?php
class CategorieSocieteDelete
{
    private $idCategorieSociete;
   

    function __construct($idCategorieSociete)
    {
        $this->setIdCategorieSociete($idCategorieSociete);
    }

    public function delete(PDO $con)
    {
        try {
            GenericDb::insert($this, "categorieSocieteDelete", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
   
    public function getIdCategorieSociete()
    {
        return $this->idCategorieSociete;
    }

    
    public function setIdCategorieSociete($idCategorieSociete)
    {
        $this->idCategorieSociete = $idCategorieSociete;
    }
}
