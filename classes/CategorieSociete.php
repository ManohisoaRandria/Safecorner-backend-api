<?php

class CategorieSociete implements JsonSerializable {
    private $id;
    private $description;

    public function __construct($id,$description){
        $this->setId($id);
        $this->setDescription($description);
    }
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'description' => $this->description
        ];
    }
    public function update(PDO $con)
    {
        try {
            GenericDb::update("categorieSociete",array(
                "description"=>$this->description
            )," id='".$this->id."'",false,$con);
        } catch (Exception $ex) {
            throw $ex;
        }
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
     * Get the value of description
     *
     * @return  mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @param   mixed  $description  
     *
     * @return  self
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    //insert categorie societe
    public function insert($con){
        try{
            GenericDb::insert($this,'categoriesociete',false,$con);
        }
        catch(Exception $e){
            throw $e;
        }
    }
}
?>