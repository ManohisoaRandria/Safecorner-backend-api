<?php
class ProtocoleChoisi{
    private $id;
    private $idSociete;
    private $idCategorieProtocole;
    private $idProtocole;
    private $dateCreation;
    private $duree;
    private $etat;

    function __construct($id,$idSociete,$idCategorieProtocole,$idProtocole,$dateCreation,$duree,$etat){
        $this->setId($id);
        $this->setIdSociete($idSociete);
        $this->setIdCategorieProtocole($idCategorieProtocole);
        $this->setIdProtocole($idProtocole);
        $this->setDateCreation($dateCreation);
        $this->setDuree($duree);
        $this->setEtat($etat);
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

    //insert protocoleChoisi
    public function insert($con){
        try{
            GenericDb::insert($this,'protocolechoisi',false,$con);
        }
        catch(Exception $e){
            throw $e;
        }
    }

    //update protocoleChoisi
    /*Mettre en null se qui ne change pas
    */
    public function update(PDO $con){
        try{
            $edit = array();
            if($this->getIdSociete() != null){ $edit['idsociete'] = $this->getIdSociete(); }
            if($this->getIdCategorieProtocole() != null){ $edit['idcategorieprotocole'] = $this->getIdCategorieProtocole(); }
            if($this->getIdProtocole() != null){ $edit['idprotocole'] = $this->getIdProtocole(); }
            if($this->getDateCreation() != null){ $edit['datecreation'] = $this->getDateCreation(); }
            if($this->getDuree() != null){ $edit['duree'] = $this->getDuree(); }
            if($this->getEtat() != null){ $edit['etat'] = $this->getEtat(); }
            GenericDb::update('protocoleChoisi',$edit," id='".$this->getId()."'",false,$con);
        }
        catch(Exception $e){
            throw $e;
        }
    }
}
?>