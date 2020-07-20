<?php
class TokenMobile
{
    private $identifier;
    private $etat;

    public function __construct(string $identifier, int $etat)
    {
        $this->setIdentifier($identifier);
        $this->setEtat($etat);
    }
    public function insert(PDO $con)
    {
        try {
            GenericDb::insert($this, "TokenMobile", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function getIdentifier()
    {
        return $this->identifier;
    }


    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }


    public function getEtat()
    {
        return $this->etat;
    }


    public function setEtat($etat)
    {
        $this->etat = $etat;
    }
}
