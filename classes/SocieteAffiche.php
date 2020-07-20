<?php


class SocieteAffiche implements JsonSerializable
{
    private $id;
    private $nom;
    private $idCategorie;
    private $description;
    private $lieu;
    private $email;
    private $tel;
    private $coordonnee;
    private $points;

    public function __construct(
        $id,
        $nom,
        $idCategorieSociete,
        $description,
        $lieu,
        $email,
        $tel,
        $coordonnee,
        $points
    ) {
        $this->setId($id);
        $this->setNom($nom);
        $this->setIdCategorie($idCategorieSociete);
        $this->setDescription($description);
        $this->setLieu($lieu);
        $this->setEmail($email);
        $this->setTel($tel);
        $this->setCoordonnee($coordonnee);
        $this->setPoints($points);
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'idCategorieSociete' => $this->idCategorie,
            'description' => $this->description,
            'lieux' => $this->lieu,
            'email' => $this->email,
            'tel' => $this->tel,
            'coordonne' => $this->coordonnee,
            'points' => $this->points
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
     * Get the value of idCategorieSociete
     *
     * @return  mixed
     */
    public function getIdCategorie()
    {
        return $this->idCategorie;
    }

    /**
     * Set the value of idCategorieSociete
     *
     * @param   mixed  $idCategorieSociete  
     *
     * @return  self
     */
    public function setIdCategorie($idCategorie)
    {
        $this->idCategorie = $idCategorie;
    }

    /**
     * Get the value of nom
     *
     * @return  mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set the value of nom
     *
     * @param   mixed  $nom  
     *
     * @return  self
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * Get the value of descritpion
     *
     * @return  mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of descritpion
     *
     * @param   mixed  $descritpion  
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the value of lieu
     *
     * @return  mixed
     */
    public function getLieu()
    {
        return $this->lieu;
    }

    /**
     * Set the value of lieu
     *
     * @param   mixed  $lieu  
     *
     * @return  self
     */
    public function setLieu($lieu)
    {
        $this->lieu = $lieu;
    }


    /**
     * Get the value of email
     *
     * @return  mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @param   mixed  $email  
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get the value of tel
     *
     * @return  mixed
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set the value of tel
     *
     * @param   mixed  $tel  
     *
     * @return  self
     */
    public function setTel($tel)
    {
        $this->tel = $tel;
    }

    /**
     * Get the value of coordonnee
     *
     * @return  mixed
     */
    public function getCoordonnee()
    {
        return $this->coordonnee;
    }

    /**
     * Set the value of coordonnee
     *
     * @param   mixed  $coordonnee  
     *
     * @return  self
     */
    public function setCoordonnee($coordonnee)
    {
        $this->coordonnee = $coordonnee;
    }


    public function getPoints()
    {
        return $this->points;
    }


    public function setPoints($points)
    {
        $this->points = $points;
    }
}
