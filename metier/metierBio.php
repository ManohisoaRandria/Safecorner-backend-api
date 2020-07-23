<?php

use phpDocumentor\Reflection\DocBlock\Tags\Generic;

Flight::map('validationNom', function(string $table,string $colone,$value,$addWhere = "",PDO $con){
    $res = false;
    try{
        $sql = $con->prepare("select * from ".$table." where lower(".$colone.") = lower('".$value."')".$addWhere,array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $sql->execute();
        $result = $sql->fetchAll(PDO::FETCH_ASSOC);
        if(count($result) > 0){
            $res = true;
        }
    }
    catch(Exception $e){
        throw $e;
    }
    return $res;
});

Flight::map('addProtocoleChoisi', function($societe,$idCategorieProtocole,$data,PDO $con){
   try{
        $protocoleSociete = $societe->getAllProtocolesChoisi($idCategorieProtocole,$con);
        //verification,insert ProtocoleChoisi et insert HistoriqueChangementProtocole:add
        $test = true;//pour savoir si le protocoleChoisi existe deja
        $compter = 0;//pour savoir si eu des conflit
        //Initialisation de l'insertion de historiqueDescente pour mettre le point de la societe
        if(Flight::Count("protocoleChoisi","idsociete = '".$societe->getId()."'",$con) === 0){
            $idHistoD = 'HTDE'.GenericDb::formatNumber(GenericDb::getNextVal("seq_historiquedescente",$con), Constante::$ID_COUNT);
            $idSociete = $societe->getId();
            $description = "Initialisation de point de la societe";
            $points = 10.00;
            $date = new DateTime();
            //Insertion 
            $res = new HistoriqueDescente($idHistoD,$idSociete,$description,$points,$date,Constante::$DESCENTE_VALIDE);
            $res->insert($con);
        }
        for($i = 0; $i<Count($data); $i++){
            for($j=0; $j<Count($protocoleSociete);$j++){
                if($protocoleSociete[$j]->getIdProtocole() == $data[$i]['idProtocole']){
                    //si le protocoleChoisi existe mais etat non_active. on change en etat active
                    if($protocoleSociete[$j]->getEtat() == Constante::$PROTOCOLE_NON_ACTIVE){
                        $id = $protocoleSociete[$j]->getId();
                        $protocoleChoisi = new ProtocoleChoisi($id,null,null,null,null,null,Constante::$PROTOCOLE_ACTIVE);
                        $protocoleChoisi->update($con);
                        //insertion HistoriqueChangementProtocole
                        $historiqueChangementProtocole = new HistoriqueChangementProtocole(
                            $id,
                            new DateTime(),
                            Constante::$HISTORIQUE_PROTOCOLE_ADD);
                        $historiqueChangementProtocole->insert($con);    
                    }
                    else if($protocoleSociete[$j]->getEtat() == Constante::$PROTOCOLE_ACTIVE){
                        $compter++;
                    }
                    $test = false;//pour indique que le protocoleChoisi exist deja
                    break;
                }
            }
            if($test){//si le protocole n'existe pas encore. insertion
                //insertion protocoleChoisi
                $id = $id = 'PRTC'.GenericDb::formatNumber(GenericDb::getNextVal("seq_protocolechoisi", $con), Constante::$ID_COUNT);
                $protocoleChoisi = new ProtocoleChoisi(
                    $id,
                    $societe->getId(),
                    $idCategorieProtocole,
                    $data[$i]['idProtocole'],
                    new DateTime(),
                    $data[$i]['duree'],
                    Constante::$PROTOCOLE_ACTIVE);
                $protocoleChoisi->insert($con);
                //insertion HistoriqueChangementProtocole
                $historiqueChangementProtocole = new HistoriqueChangementProtocole(
                    $id,
                    new DateTime(),
                    1);
                $historiqueChangementProtocole->insert($con);
            }
            $test = true;
        }
        return $compter;
   }
   catch(Exception $e){
    throw $e;
   }
});

Flight::map('deleteProtocoleChoisi', function($societe,$idCategorieProtocole,$data,PDO $con){
    try{
        $protocoleSociete = $societe->getAllProtocolesChoisi($idCategorieProtocole,$con);
        //verification,update ProtocoleChoisi et insert HistoriqueChangementProtocole:delete
        $test = true;
        for($i=0; $i<Count($protocoleSociete); $i++){
            for($j=0; $j<Count($data); $j++){
                if($protocoleSociete[$i]->getIdProtocole() == $data[$j]['idProtocole']){
                    $test = false;
                    break;
                }
            }
            if($test){
                $id = $protocoleSociete[$i]->getId();
                //update etat protocoleChoisi
                $protocoleChoisi = new ProtocoleChoisi($id,null,null,null,null,null,Constante::$PROTOCOLE_NON_ACTIVE);
                $protocoleChoisi->update($con);
                //insert HistoriqueChangementProtocole
                $historiqueChangementProtocole = new HistoriqueChangementProtocole(
                    $id,
                    new DateTime(),
                    10);
                $historiqueChangementProtocole->insert($con);
            }
            $test = true;
        }
    }
    catch(Exception $e){
     throw $e;
    }
 });

 Flight::map('getOutProtocoleBySociete', function($idSociete,$idCategorieProtocole,PDO $con){
     try{
        $temp = new Protocole(null,null,null,null);
        $afterWhere = " where id not in (select idprotocole from protocolechoisi where idsociete='%s' and idcategorieprotocole='%s' and etat = '%s')";
        $afterWhere = sprintf($afterWhere,$idSociete,$idCategorieProtocole,Constante::$PROTOCOLE_ACTIVE);
        return GenericDb::find($temp,'protocole',array(),$afterWhere,$con);
     }
     catch(Exception $e){
         throw $e;
     }
 });

 Flight::map('calculePoint',function($idSociete,$nombreCheck,PDO $con){
    $res = 0;
    try{
        //verification: existance societe
        if(!Flight::validationNom("societe","id",$idSociete,$con)){
            throw new Exception("This societe does not exist.",Constante::$ERROR_CODE['400']);
        }
        //Donnee
        $societe = new Societe($idSociete,null,null,null,null,null,null,null,null);
        $societe = $societe->getById($con);
        $nbProtocoleSociete = $societe->getCountProtocole($con);
        //action: calcule la note
        $note = 10*$nombreCheck/$nbProtocoleSociete;
        //resultat
        $res = round($note,2);
    }
    catch(Exception $e){
        throw $e;
    }
    return $res;
 });

 Flight::map('Count',function(string $table,string $afterWhere,PDO $con){
    try{
        $res = 0;
        $sql = "select count(*) as nb from %s";
        $sql = sprintf($sql,$table);
        if($afterWhere != ""){
            $sql = $sql." Where %s"; 
            $sql = sprintf($sql,$afterWhere);
        }
        $exec = $con->prepare($sql);
        $exec->execute();
        $result = $exec->fetchAll(PDO::FETCH_ASSOC);
        if(Count($result) > 1){
            throw new Exception("Error in server: count table",Constante::$ERROR_CODE['500']);
        }
        foreach($result as $data){
            $res = $data['nb'];
            break;
        }
        return $res;
    }
    catch(Exception $e){
        throw $e;
    }
 });
Flight::map('societeNearby',function($coordLat,$coordLong,$KM,PDO $con){  
    $lesSociete = array();
    try{
        //cotrolle valeur coordonnee
        if(!is_numeric($coordLong)||!is_numeric($coordLat)||!is_numeric($KM)){
            throw new Exception("CoordLat or CoordLong or km not numeric",Constante::$ERROR_CODE['400']);
        }
        //action: prendre les societe proche de [$coordLat,$coordLong] a $KM kilometre
            $sql = "SELECT id,nom,idcategoriesociete,description,lieu,datecreation,email,tel,ST_AsGeoJSON(coordonnee) as coordonnee
            FROM
                (SELECT *,
                    ST_Intersects(ST_Buffer(ST_GeomFromGeoJSON('{\"type\":\"Point\",\"coordinates\":[%f,%f]}'),%f,'quad_segs=8'),
                    coordonnee) as etat 
                FROM societe) as societe WHERE etat = 't'";
            $sql = sprintf($sql,$coordLat,$coordLong,$KM);

            $res = $con->query($sql);
            $res->setFetchMode(PDO::FETCH_ASSOC);
            while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
                $lesSociete[] = new Societe(
                    $donnees['id'],
                    $donnees['nom'],
                    $donnees['idcategoriesociete'],
                    $donnees['description'],
                    $donnees['lieu'],
                    $donnees['datecreation'],
                    $donnees['email'],
                    $donnees['tel'],
                    $donnees['coordonnee']
                );
            }
            $res->closeCursor();
    }
    catch(Exception $e){
        throw $e;
    }
    return $lesSociete;
});

Flight::map('getAllProtocole',function(int $page/*num page*/,int $limitProtocole/*limit a afficher*/,string $all,PDO $con){  
    $protocoles = array();
    try{
        ///requette principale
        $sql = "SELECT * FROM protocole";
        if($all == "false"){
            //controlle valeur
            if(!is_numeric($page)||$page === 0){
                throw new Exception("Invalid page",Constante::$ERROR_CODE['400']);
            }
            if(!is_numeric($limitProtocole)){
                throw new Exception("Invalid number protocole",Constante::$ERROR_CODE['400']);
            }
            //prendre le nombre de protocole existante
            $nbProtocole = Flight::Count("protocole","",$con);
            $nbPage = round($nbProtocole / $limitProtocole);
            $offset = ($page * $limitProtocole) - $limitProtocole;
            //si il y a des reste de protocole 
            $nbResteProtocole = $nbProtocole - ($limitProtocole * $nbPage);
            if($nbResteProtocole != 0){
                $nbPage++;
            }
            //control page
            if($page > $nbPage){
                throw new Exception("there is no more pages",Constante::$ERROR_CODE['400']);
            }
            $sql = $sql." order by id LIMIT %f OFFSET %f";
            $sql = sprintf($sql,$limitProtocole,$offset);
        }

        //Executer la requette
        $res = $con->query($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
            $protocoles[] = new Protocole(
                $donnees['id'],
                $donnees['nom'],
                $donnees['description'],
                $donnees['datecreation']
            );
        }
        $res->closeCursor();
    }
    catch(Exception $e){
        throw $e;
    }
    return $protocoles;
});

//Utilisation pagination
Flight::map('getAllSociete',function(int $page/*num page*/,int $limitSociete/*limit a afficher*/,string $all,PDO $con){  
    $societes = array();
    try{
        //requette principale
        $sql = "SELECT id,nom,idCategoriesociete,description,lieu,dateCreation,email,tel,
            ST_AsGeoJSON(coordonnee) as coordonnee FROM societe";
        if($all == "false"){
            //controlle valeur
            if(!is_numeric($page)||$page === 0){
                throw new Exception("Invalid page",Constante::$ERROR_CODE['400']);
            }
            if(!is_numeric($limitSociete)){
                throw new Exception("Invalid number societe",Constante::$ERROR_CODE['400']);
            }
            //prendre le nombre de societe existante
            $nbSociete = Flight::Count("societe","",$con);
            $nbPage = round($nbSociete / $limitSociete);
            $offset = ($page * $limitSociete) - $limitSociete;
            //si il y a des reste de societe 
            $nbResteSociete = $nbSociete - ($limitSociete * $nbPage);
            if($nbResteSociete != 0){
                $nbPage++;
            }
            //control page
            if($page > $nbPage){
                throw new Exception("there is no more pages",Constante::$ERROR_CODE['400']);
            }
            $sql = $sql." order by id LIMIT %f OFFSET %f";
            $sql = sprintf($sql,$limitSociete,$offset);
        }

        //Executer la requette
        $res = $con->query($sql);
        $res->setFetchMode(PDO::FETCH_ASSOC);
        while ($donnees = $res->fetch(PDO::FETCH_ASSOC)) {
            $societes[] = new Societe(
                $donnees['id'],
                $donnees['nom'],
                $donnees['idcategoriesociete'],
                $donnees['description'],
                $donnees['lieu'],
                $donnees['datecreation'],
                $donnees['email'],
                $donnees['tel'],
                $donnees['coordonnee']
            );
        }
        $res->closeCursor();
    }
    catch(Exception $e){
        throw $e;
    }
    return $societes;
});

Flight::map('getCategorieSociete',function(PDO $con){  
    $res = array();
    try{
        //action: prendre tous les categories de societe
        $res = GenericDb::find(CategorieSociete::class, "categoriesociete",array(),"", $con);
        return $res;
    }
    catch(Exception $e){
        throw $e;
    }
    return $res;
});

Flight::map('getCategorieProtocole',function(PDO $con){  
    $res = array();
    try{
        //action:
        //action: prendre tous les categories de societe
        $res = GenericDb::find(CategorieProtocole::class, "categorieProtocole",array(),"", $con);
        return $res;
    }
    catch(Exception $e){
        throw $e;
    }
    return $res;
});