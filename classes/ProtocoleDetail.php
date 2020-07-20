<?php
class ProtocoleDetail
{

    private $id;
    private $idsociete;
    private $idcategorieprotocole;
    private $idprotocole;
    private $datecreation;
    private $duree;
    private $etat;
    private $nomprotocole;
    private $descriptionprotocole;
    private $datecreationprotocole;
    private $descriptioncategprotocole;
    public function __construct(
        $id,
        $idsociete,
        $idcategorieprotocole,
        $idprotocole,
        $datecreation,
        $duree,
        $etat,
        $nomprotocole,
        $descriptionprotocole,
        $datecreationprotocole,
        $descriptioncategprotocole
    ) {
        $this->setId($id);
        $this->setIdsociete($idsociete);
        $this->setIdcategorieprotocole($idcategorieprotocole);
        $this->setIdprotocole($idprotocole);
        $this->setDatecreation($datecreation);
        $this->setDuree($duree);
        $this->setEtat($etat);
        $this->setNomprotocole($nomprotocole);
        $this->setDescriptionprotocole($descriptionprotocole);
        $this->setDatecreationprotocole($datecreationprotocole);
        $this->setDescriptioncategprotocole($descriptioncategprotocole);
    }


    public function getId()
    {
        return $this->id;
    }


    public function setId($id)
    {
        $this->id = $id;
    }


    public function getIdsociete()
    {
        return $this->idsociete;
    }


    public function setIdsociete($idsociete)
    {
        $this->idsociete = $idsociete;
    }


    public function getIdcategorieprotocole()
    {
        return $this->idcategorieprotocole;
    }


    public function setIdcategorieprotocole($idcategorieprotocole)
    {
        $this->idcategorieprotocole = $idcategorieprotocole;
    }


    public function getIdprotocole()
    {
        return $this->idprotocole;
    }


    public function setIdprotocole($idprotocole)
    {
        $this->idprotocole = $idprotocole;
    }


    public function getDatecreation()
    {
        return $this->datecreation;
    }


    public function setDatecreation($datecreation)
    {
        $this->datecreation = $datecreation;
    }


    public function getDuree()
    {
        return $this->duree;
    }


    public function setDuree($duree)
    {
        $this->duree = $duree;
    }


    public function getEtat()
    {
        return $this->etat;
    }


    public function setEtat($etat)
    {
        $this->etat = $etat;
    }


    public function getNomprotocole()
    {
        return $this->nomprotocole;
    }


    public function setNomprotocole($nomprotocole)
    {
        $this->nomprotocole = $nomprotocole;
    }


    public function getDescriptionprotocole()
    {
        return $this->descriptionprotocole;
    }


    public function setDescriptionprotocole($descriptionprotocole)
    {
        $this->descriptionprotocole = $descriptionprotocole;
    }


    public function getDatecreationprotocole()
    {
        return $this->datecreationprotocole;
    }


    public function setDatecreationprotocole($datecreationprotocole)
    {
        $this->datecreationprotocole = $datecreationprotocole;
    }


    public function getDescriptioncategprotocole()
    {
        return $this->descriptioncategprotocole;
    }


    public function setDescriptioncategprotocole($descriptioncategprotocole)
    {
        $this->descriptioncategprotocole = $descriptioncategprotocole;
    }
}
