<?php

    class HistoriqueChangementProtocole{
        private $idProtocoleChoisi;
        private $dateChangement;
        private $action;

        function __construct($idProtocoleChoisi,$dateChangement,$action){
            $this->setIdProtocoleChoisi($idProtocoleChoisi);
            $this->setDateChangement($dateChangement);
            $this->setAction($action);
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

        //insert historique changement protocole
        public function insert($con){
            try{
                GenericDb::insert($this,'historiquechangementprotocole',false,$con);
            }
            catch(Exception $e){
                throw $e;
            }
        }
    }

?>