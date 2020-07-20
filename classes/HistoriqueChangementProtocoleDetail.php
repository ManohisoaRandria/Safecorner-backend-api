<?php

class HistoriqueChangementProtocoleDetail implements JsonSerializable {
    private $idProtocoleChoisi;
    private $idSociete;
    private $idCategorieProtocole;
    private $idProtocole;
    private $duree;
    private $etat;
    private $dateCreation;
    private $dateChangement;
    private $action;

    public function __construct(
        $idProtocoleChoisi,
        $idSociete,
        $idCategorieProtocole,
        $idProtocole,
        $duree,
        $etat,
        $dateCreation,
        $dateChangement,
        $action
    ){
        $this->setIdSociete($idSociete);
        $this->setIdCategorieProtocole($idCategorieProtocole);
        $this->setIdProtocole($idProtocole);
        $this->setDateCreation($dateCreation);
        $this->setDuree($duree);
        $this->setEtat($etat);
        $this->setIdProtocoleChoisi($idProtocoleChoisi);
        $this->setDateChangement($dateChangement);
        $this->setAction($action);
    }
    
    public function jsonSerialize()
    {
        return [
            'idProtocoleChoisi' => $this->idProtocoleChoisi,
            'idSociete' => $this->idSociete,
            'idCategorieProtocole' => $this->idCategorieProtocole,
            'idProtocole' => $this->idProtocole,
            'duree' => $this->duree,
            'etat' => $this->etat,
            'dateCreation' => $this->dateCreation,
            'dateChangement' => $this->dateChangement,
            'action' => $this->action
        ];
    }

    /**
     * Get the value of idProtocoleChoisi
     *
     * @return  mixed
     */
    public function getIdProtocoleChoisi()
    {
        return $this->idProtocoleChoisi;
    }

    /**
     * Set the value of idProtocoleChoisi
     *
     * @param   mixed  $idProtocoleChoisi  
     *
     * @return  self
     */
    public function setIdProtocoleChoisi($idProtocoleChoisi)
    {
        $this->idProtocoleChoisi = $idProtocoleChoisi;
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
     * Get the value of idCategorieProtocole
     *
     * @return  mixed
     */
    public function getIdCategorieProtocole()
    {
        return $this->idCategorieProtocole;
    }

    /**
     * Set the value of idCategorieProtocole
     *
     * @param   mixed  $idCategorieProtocole  
     *
     * @return  self
     */
    public function setIdCategorieProtocole($idCategorieProtocole)
    {
        $this->idCategorieProtocole = $idCategorieProtocole;
    }

    /**
     * Get the value of idProtocole
     *
     * @return  mixed
     */
    public function getIdProtocole()
    {
        return $this->idProtocole;
    }

    /**
     * Set the value of idProtocole
     *
     * @param   mixed  $idProtocole  
     *
     * @return  self
     */
    public function setIdProtocole($idProtocole)
    {
        $this->idProtocole = $idProtocole;
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

    /**
     * Get the value of duree
     *
     * @return  mixed
     */
    public function getDuree()
    {
        return $this->duree;
    }

    /**
     * Set the value of duree
     *
     * @param   mixed  $duree  
     *
     * @return  self
     */
    public function setDuree($duree)
    {
        $this->duree = $duree;
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

    /**
     * Get the value of dateChangement
     *
     * @return  mixed
     */
    public function getDateChangement()
    {
        return $this->dateChangement;
    }

    /**
     * Set the value of dateChangement
     *
     * @param   mixed  $dateChangement  
     *
     * @return  self
     */
    public function setDateChangement($dateChangement)
    {
        $this->dateChangement = $dateChangement;
    }

    /**
     * Get the value of action
     *
     * @return  mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Set the value of action
     *
     * @param   mixed  $action  
     *
     * @return  self
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

}

?>