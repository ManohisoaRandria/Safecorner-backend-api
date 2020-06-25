<?php
    class Users implements JsonSerializable{
        private $id;
        private $nom;
        private $mdp;

        function __construct($id,$nom,$mdp){
            $this->setId($id);
            $this->setNom($nom);
            $this->setMdp($mdp);
        }
        public function jsonSerialize()
        {
            return [
                'id' => $this->id,
                'nom' => $this->nom,
                'mdp' => $this->mdp
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
         * Get the value of mdp
         *
         * @return  mixed
         */
        public function getMdp()
        {
            return $this->mdp;
        }

        /**
         * Set the value of mdp
         *
         * @param   mixed  $mdp  
         *
         * @return  self
         */
        public function setMdp($mdp)
        {
            $this->mdp = $mdp;
        }

        //insert users
        public function insert($con){
            try{
                GenericDb::insert($this,'users',false,$con);
            }
            catch(Exception $e){
                throw $e;
            }
        }

        //get User by nom
        public function getByNom(PDO $con){
            try{
                $res = GenericDb::find($this,"users",array("nom"=>$this->getNom()),"",$con);
                return $res;
            }
            catch(Exception $e){
                throw $e;
            }
        }
    }
?>