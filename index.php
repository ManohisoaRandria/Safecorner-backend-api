<?php
require_once('vendor/autoload.php');

//indispensable
require 'outil/GenericDb.php';
require 'outil/auth.php';
require 'outil/Constante.php';
require 'outil/ApiResponse.php';

//import ny classes vo namboarina le dossier imports
require 'imports/impBio.php';
require 'imports/impNgam.php';

//metier
require 'metier/metierBio.php';
require 'metier/metierNgam.php';

// //routes
require 'routes/routeNgam.php';
require 'routes/routeBio.php';


// $dbopts = parse_url(getenv('DATABASE_URL'));
// Flight::register('db', 'PDO', array(, , , ,));

Flight::register('db', 'PDO', array(
  'pgsql:host=' . $dbopts["host"] . ';port=' . $dbopts["port"] . ';dbname=' . ltrim($dbopts["path"], '/') . '',
  '' . $dbopts["user"] . '',
  '' . $dbopts["pass"] . ''
), function ($db) {
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
});

// Flight::register('db', 'Connect', array('localhost','5432','volako_db','volako_user','1234'));
// Flight::register('db', 'PDO', array('pgsql:host=localhost;port=5432;dbname=safecorner_db', 'postgres', 'm1234'), function ($db) {
//   $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// });


Flight::start();
