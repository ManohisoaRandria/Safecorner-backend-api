<?php

 class Login
 {
   private $nom;
   private $mdp;
   //private $userData;

   function __construct($nom,$mdp)
   {
    //  $this->nom=trim($nom);
    //  $this->mdp=trim($mdp);
        $this->nom=$nom;
        $this->mdp=$mdp;
   }

   public function login($con)
   {  
    $res = null;
     try{
        $user=new Users(null,$this->nom,null);
        $tab=$user->getByNom($con);
        if(count($tab)>0){
            if(password_verify($this->mdp ,$tab[0]->getMdp())){
                $res=$tab[0];
            }else{
                throw new Exception("Incorrect password.",Constante::$ERROR_CODE['400']);
            }
        }else{
            throw new Exception("Incorrect email.",Constante::$ERROR_CODE['400']);
        }
     }
     catch(Exception $e){
        throw $e;
     }
     return $res;
   }
//   public function getTokenByUser($con,$idUsers)
//   {
//     $requette="select token from UsersToken where id= :idUsers order by expiration desc limit 1";

//     $result=$con->prepare($requette);
//     $result->execute([':idUsers' => $idUsers]);

//     $tab=array();
//     while($element=$result->fetch(PDO::FETCH_ASSOC)){
//         $tab[]= $element['token'];
//     }
//     $result->closeCursor();
//     if(count($tab)!=0){
//       return $tab[0];
//     }
//     return null;
//   }
//   public function getToken()
//   {
//     $date = new DateTime();
//     date_add($date, date_interval_create_from_date_string('2 minutes'));
//     $token="%s%s";
//     $token=sprintf($token,$date->format("Y-m-d H:i:s"),$this->userData->nom);
//     $token=sha1($token);
//     $array = array('userName' => $this->userData->nom,'token'=> 'tk'.$token);
//     return $array;
//   }
}
?>