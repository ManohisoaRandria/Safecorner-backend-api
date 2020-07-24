<?php
class ProtocoleDelete
{
    private $idProtocole;
   

    function __construct($idProtocole)
    {
        $this->setIdProtocole($idProtocole);
    }

    public function delete(PDO $con)
    {
        try {
            GenericDb::insert($this, "protocoleDelete", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }

   
    public function getIdProtocole()
    {
        return $this->idProtocole;
    }

    
    public function setIdProtocole($idProtocole)
    {
        $this->idProtocole = $idProtocole;
    }
}
