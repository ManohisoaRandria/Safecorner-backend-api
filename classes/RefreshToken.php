<?php
class RefreshToken
{
    private $id;
    private $token;
    private $etat;
    public function __construct(string $id, string $token, int $etat)
    {
        $this->setId($id);
        $this->setToken($token);
        $this->setEtat($etat);
    }

    public function insert(PDO $con)
    {
        try {
            GenericDb::insert($this, "refreshToken", false, $con);
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function getByToken(PDO $con)
    {
        try {
            $Prestation = GenericDb::find(RefreshToken::class, "refreshToken", array("token" => $this->token, "etat" => $this->etat), "", $con);
            if (count($Prestation) != 0) return $Prestation[0];
            return null;
        } catch (Exception $ex) {
            throw $ex;
        }
    }
    public function invalidate(PDO $con)
    {
        try {
            $Prestation = GenericDb::update("refreshToken", array("etat" => $this->etat), " token='" . $this->token . "'", false, $con);
            return $Prestation;
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


    public function getToken()
    {
        return $this->token;
    }


    public function setToken($token)
    {
        $this->token = $token;
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
