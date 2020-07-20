<?php
class GenericDb
{
  //Important: noms des attributs de classe doivent correspondres aux noms des colonnes de la table
  //getters et setters obligatoire sous forme camelCase, exemple: pour l'attribut nom=>getNom(),setNom()
  //exemple: pour l'attribut nomClient=>getNomClient(),setNomClient()

  /**
   * exemple: GenericDb::find(Personne::class,"personne",array(),"",$con);
   *         =>select * from personne;
   *
   *         GenericDb::find(Personne::class,"personne",array("nom"=>"rabe"),"",$con);
   *         =>select * from personne where nom='rabe';
   *
   *         GenericDb::find(Personne::class,"personne",array("nom"=>"rabe","prenom"=>"kaka"),"",$con);
   *         =>select * from personne where nom='rabe' and prenom='kaka';
   *
   *         GenericDb::find(Personne::class,"personne",array("nom or"=>"rabe","nom"=>"razaka"),"",$con);
   *         =>select * from personne where nom='rabe' or nom='razaka';
   *
   *         GenericDb::find(Personne::class,"personne",array("date > and"=>"2012-03-15","nom"=>"razaka"),"",$con);
   *         =>select * from personne where date >'2012-03-15' and nom='razaka';
   *
   *         GenericDb::find(Personne::class,"personne",array("date > and"=>"2012-03-15","nom"=>"razaka")," orderby nom limit 1",$con);
   *         =>select * from personne where date >'2012-03-15' and nom='razaka' orderby nom limit 1;
   */
  public static function find($class, string $nomTable, array $aprWhere, string $fanampiny, PDO $con)
  {
    //apreWhere est une array cle valeur
    try {
      $requete = "select * from " . $nomTable;
      if ($aprWhere != null && count($aprWhere) != 0) {
        $requete .= GenericDb::getStatementFromArray($aprWhere);
      }
      if ($fanampiny != null && !empty($fanampiny)) $requete .= " " . $fanampiny;
      $result = $con->prepare($requete);

      GenericDb::executeQuery($result, $aprWhere, "select");

      $tabResult = array();

      $reflect = new ReflectionClass($class);
      $feilds  = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PRIVATE);

      while ($resRq = $result->fetch(PDO::FETCH_ASSOC)) {
        $tempObj = $reflect->newInstanceWithoutConstructor();
        for ($j = 0; $j < count($feilds); $j++) {
          $oneMethod = $reflect->getMethod('set' . ucfirst($feilds[$j]->getName()));
          //mampiditra donnee amle objet
          $oneMethod->invoke($tempObj, $resRq[strtolower($feilds[$j]->getName())]);
        }
        $tabResult[] = $tempObj;
      }
      $result->closeCursor();

      return $tabResult;
    } catch (Exception $e) {
      throw $e;
    }
  }
  /**
   *   set $enableCommit to false raha misy insertion maro2 de mila setAutocommit false
   *     supposons class personne{ $age,$id,$nom }
   *     exemple: GenericDb::insert(new Personne(1,2,"rabe"),"personne",false,"",$con);
   *             =>insert into personne (age,id,nom) values(1,2,'rabe');
   */
  public static function insert($object, string $nomTable, bool $enableCommit, PDO $con)
  {
    try {
      if ($enableCommit) $con->beginTransaction();
      $reflect = new ReflectionClass($object);
      $feilds  = $reflect->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PRIVATE);
      $requete = "insert into " . $nomTable . " (";
      for ($i = 0; $i < count($feilds); $i++) {
        if ($i != count($feilds) - 1) $requete .=  strtolower($feilds[$i]->getName()) . ",";
        else $requete .= strtolower($feilds[$i]->getName());
      }
      $requete .= ") values (";

      for ($i = 0; $i < count($feilds); $i++) {
        if ($i != count($feilds) - 1) $requete .= ":" . strtolower($feilds[$i]->getName()) . ",";
        else $requete .= ":" . strtolower($feilds[$i]->getName());
      }
      $requete .= ")";
      $result = $con->prepare($requete);

      $values = GenericDb::getValueArrayFromObject($object, $feilds, $reflect);

      GenericDb::executeQuery($result, $values, "insert");
      if ($enableCommit) $con->commit();
    } catch (Exception $e) {
      if ($enableCommit) $con->rollback();
      throw $e;
    }
  }
  /**
   *   set $enableCommit to false raha misy insertion maro2 de mila setAutocommit false
   *     
   *     exemple: GenericDb::update("personne",array("nom"=>"rasoa")," id=1",false,$con);
   *             =>update personne set nom='rasoa' where id=1;
   *             GenericDb::update("personne",array("nom"=>"rasoa","prenom"=>"coco")," id=1",false,$con);
   *             =>update personne set nom='rasoa',prenom='coco' where id=1;
   */
  public static function update(string $table, array $data, string $where, bool $enableCommit, PDO $con)
  {
    try {
      if ($enableCommit) $con->beginTransaction();

      $requete = "update " . $table . " set ";

      $cle = array_keys($data);
      for ($i = 0; $i < count($cle); $i++) {
        if ($i != (count($cle) - 1)) $requete .= $cle[$i] . " =:" . $cle[$i] . ", ";
        else $requete .= $cle[$i] . " =:" . $cle[$i];
      }

      if ($where != null && !empty($where)) $requete .= " where " . $where;
      $result = $con->prepare($requete);

      GenericDb::executeQuery($result, $data, "insert");
      if ($enableCommit) $con->commit();
    } catch (Exception $e) {
      if ($enableCommit) $con->rollback();
      throw $e;
    }
  }
  private static function executeQuery(PDOStatement &$result, array $array, string $type)
  {
    $params = array();
    $index = 1;
    foreach ($array as $key => $value) {
      $split = explode(" ", trim($key));
      if ($type == "select") $params[$split[0] . $index] = $value;
      elseif ($type == "insert") $params[$split[0]] = $value;
      $index++;
    }
    $result->execute($params);
  }
  public static function getNextVal(string $sequence, PDO $con)
  {
    $requette = "select nextval(:seq) as nb";

    $result = $con->prepare($requette);
    $result->execute([':seq' => $sequence]);

    $nb = "";
    while ($element = $result->fetch(PDO::FETCH_ASSOC)) {
      $nb = $element['nb'];
      break;
    }
    $result->closeCursor();
    return $nb;
  }
  public static function formatNumber(string $seq, int $ordre)
  {
    if (strlen(trim($seq)) > $ordre) {
      throw new Exception("Format impossible !");
    }
    $ret = "";
    for ($i = 0; $i < $ordre - strlen(trim($seq)); $i++) {
      $ret .= "0";
    }
    return $ret . $seq;
  }
  private static function getStatementFromArray(array $array)
  {
    $statement = " where ";
    $cle = array_keys($array);

    $index = 1;
    for ($i = 0; $i < count($cle); $i++) {

      $split = explode(" ", trim($cle[$i]));
      //var_dump($split);
      //raha misy condition akotran = sy and
      if (count($split) == 3) {
        $statement .= $split[0] . " " . $split[1] . " :" . $split[0] . $index;
        //raha mbola tsy le farany
        if ($i != (count($cle) - 1)) {
          $statement .= " " . $split[2] . " ";
        }
      } elseif (count($split) == 2) {
        if ($split[1] != "or" || $split[1] != "and") $statement .= $split[0] . " " . $split[1] . " :" . $split[0] . $index;
        else $statement .= $split[0] . "=:" . $split[0] . $index;
        //raha mbola tsy le farany
        if ($i != (count($cle) - 1)) {
          if ($split[1] != "or") $statement .= " and ";
          else $statement .= " " . $split[1] . " ";
        }
      } else {
        $statement .= $split[0] . "=:" . $split[0] . $index;
        //raha mbola tsy le farany
        if ($i != (count($cle) - 1)) {
          $statement .= " and ";
        }
      }
      $index++;
    }
    return $statement;
  }

  private function replaceKey(&$array, $curkey, $newkey)
  {
    if (array_key_exists($curkey, $array)) {
      $array[$newkey] = $array[$curkey];
      unset($array[$curkey]);
      return true;
    }
    return false;
  }
  private static function getValueArrayFromObject(&$object, &$feilds, &$reflect)
  {
    $values = array();
    for ($j = 0; $j < count($feilds); $j++) {
      $oneMethod = $reflect->getMethod('get' . ucfirst($feilds[$j]->getName()));
      //mampiditra donnee amle objet
      $res = $oneMethod->invoke($object);
      if (is_a($res, 'DateTime')) {
        $res = $res->format('Y-m-d H:i:s');
      }
      $values[strtolower($feilds[$j]->getName())] = $res;
    }
    return $values;
  }
}
