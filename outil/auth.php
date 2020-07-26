<?php

use \Firebase\JWT\JWT;

Flight::map('signIn', function ($nom, $mdp, PDO $con) {
    try {
        $log = new Login($nom, $mdp);
        $user = $log->login($con);
        //verifier si efa nlog mbetsaka
        $test = GenericDb::find(RefreshToken::class, 'refreshToken', array("id" => $user->getId(), "etat" => Constante::$REFRESH_TOKEN_VALIDE), "", $con);
        if (count($test) >= Constante::$MAX_LOGIN_SESSION_PER_USER) throw new Exception("not allowed to connect, please logout on another sessions", Constante::$ERROR_CODE['403']);

        $ac = Flight::getAccesToken($user->getId(), $user->getNom());
        $rf = Flight::getRefreshToken($user->getId(), $user->getNom());
        //encrypt referesh token
        $rfEnc = Flight::encrypt($rf, Constante::$REFRESH_ENCRYPTION_KEY);
        //insert refresh token into db
        $id =  $user->getId();
        $rt = new RefreshToken($id, sha1($rfEnc), Constante::$REFRESH_TOKEN_VALIDE);
        $rt->insert($con);

        //averina izy roa
        $data = array(
            Constante::$ACCES_TOKEN_NAME => $ac,
            Constante::$REFRESH_TOKEN_NAME =>  $rfEnc
        );
        return $data;
    } catch (Exception $e) {
        throw $e;
    }
});
Flight::map('signUp', function (string $nom, string $mdp, PDO $con) {
    try {
        $id = 'USR' . GenericDb::formatNumber(GenericDb::getNextVal("seq_users", $con), Constante::$ID_COUNT);
        $mdp = password_hash($mdp, PASSWORD_BCRYPT);
        $user = new Users($id, $nom, $mdp);
        $user->insert($con);
        return $user;
    } catch (Exception $e) {
        throw $e;
    }
});
//refresh token no alefa eto
Flight::map('logOut', function ($token, $con) {
    try {
        //invalidate
        //echo sha1($token);
        $rt = new RefreshToken("", sha1($token), Constante::$REFRESH_TOKEN_VALIDE);
        $rt = $rt->getByToken($con);
        if ($rt != null) {
            $rt->setEtat(Constante::$REFRESH_TOKEN_REVOKED);
            $rt->invalidate($con);
            return "loged out";
        } else {
            throw new Exception("no login for this users koi");
        }
    } catch (Exception $ex) {
        throw $ex;
    }
});
Flight::map('getToken', function (string $iduser) {
    $date = new DateTime("now", new DateTimeZone('Africa/Nairobi'));
    date_add($date, date_interval_create_from_date_string('2 minutes'));
    $token = "%s%s";
    $token = sprintf($token, $date->format("Y-m-d H:i:s"), $iduser);
    $token = sha1($token);
    return $token;
});
Flight::map('encrypt', function (string $encString, string $encryption_key) {
    // Content-Type: application/x-www-form-urlencoded;charset=UTF-8

    // Store the cipher method 
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method 
    // $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for encryption 
    $encryption_iv = '1234567891011121';

    // Use openssl_encrypt() function to encrypt the data 
    $encryption = openssl_encrypt(
        $encString,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $encryption;
});
Flight::map('decrypt', function (string $encString, string $encryption_key) {
    // Store the cipher method 
    $ciphering = "AES-128-CTR";

    // Use OpenSSl Encryption method 
    // $iv_length = openssl_cipher_iv_length($ciphering);
    $options = 0;

    // Non-NULL Initialization Vector for encryption 
    $encryption_iv = '1234567891011121';
    // Use openssl_decrypt() function to decrypt the data 
    $decryption = openssl_decrypt(
        $encString,
        $ciphering,
        $encryption_key,
        $options,
        $encryption_iv
    );
    return $decryption;
});
Flight::map('getAccesToken', function (string $iduser, string $name) {
    $secretKey = Constante::$ACCES_TOKEN_KEY;
    $date = new DateTime("now", new DateTimeZone('Africa/Nairobi'));
    date_add($date, date_interval_create_from_date_string('1 minutes'));
    $id = "access-%s";
    $token = sprintf($id, $date->format("Y-m-d H:i:s"));
    $tokenId  = base64_encode($token);
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + Constante::$ACCES_TOKEN_EXPIRE_TIME;
    $serverName = Constante::$SERVER_NAME; // Retrieve the server name from config file
    $data = [
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => [                  // Data related to the signer user
            'id'   => $iduser,
            'nom' => $name
        ]

    ];
    $jwt = JWT::encode(
        $data,      //Data to be encoded in the JWT
        $secretKey, // The signing key
        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
    );
    return $jwt;
});
Flight::map('getRefreshToken', function (string $iduser, string $name) {
    $secretKey = Constante::$REFRESH_TOKEN_KEY;
    $date = new DateTime("now", new DateTimeZone('Africa/Nairobi'));
    date_add($date, date_interval_create_from_date_string('1 minutes'));
    $id = "refresh-%s";
    $token = sprintf($id, $date->format("Y-m-d H:i:s"));
    $tokenId  = base64_encode($token);
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $expire     = $notBefore + Constante::$REFRESH_TOKEN_EXPIRE_TIME;
    $serverName = Constante::$SERVER_NAME; // Retrieve the server name from config file
    $data = [
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before
        'exp'  => $expire,           // Expire
        'data' => [                  // Data related to the signer user
            'id'   => $iduser,
            'nom' => $name
        ]

    ];
    $jwt = JWT::encode(
        $data,      //Data to be encoded in the JWT
        $secretKey, // The signing key
        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
    );
    return $jwt;
});
//token for mobile users,endeless token, indray mgenerer ihany
Flight::map('getMobileToken', function (string $uniqueMobileId) {
    $secretKey = Constante::$MOBILE_TOKEN_KEY;
    $date = new DateTime("now", new DateTimeZone('Africa/Nairobi'));
    date_add($date, date_interval_create_from_date_string('1 minutes'));
    $id = "mobile-%s";
    $token = sprintf($id, $date->format("Y-m-d H:i:s"));
    $tokenId  = base64_encode($token);
    $issuedAt   = time();
    $notBefore  = $issuedAt;
    $serverName = Constante::$SERVER_NAME; // Retrieve the server name from config file
    $data = [
        'iat'  => $issuedAt,         // Issued at: time when the token was generated
        'jti'  => $tokenId,          // Json Token Id: an unique identifier for the token
        'iss'  => $serverName,       // Issuer
        'nbf'  => $notBefore,        // Not before 
        'data' => [                  // Data related to the signer user
            'id'   => $uniqueMobileId
        ]

    ];
    $jwt = JWT::encode(
        $data,      //Data to be encoded in the JWT
        $secretKey, // The signing key
        'HS512'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
    );
    return $jwt;
});
//raha refresh token no alefa ato de le izy crypté
Flight::map('verifyToken', function (string $token, string $type, PDO $con = null) {
    try {
        $key = "";
        $tokenVerif = $token;
        if ($type === "ac") $key = Constante::$ACCES_TOKEN_KEY;
        else if ($type === "mobil") $key = Constante::$MOBILE_TOKEN_KEY;
        //raha refresh token de decodena aloha
        else if ($type === "rt") {
            $key = Constante::$REFRESH_TOKEN_KEY;
            $tokenVerif = Flight::decrypt($tokenVerif, Constante::$REFRESH_ENCRYPTION_KEY);
            //verifierna sode efa tsy valide tsony, zany hoe efa nbootena tam serveur
            $rt = new RefreshToken("", sha1($token), Constante::$REFRESH_TOKEN_VALIDE);
            $rt = $rt->getByToken($con);
            if ($rt == null) throw new Exception("no login, you might have been booted from the server", Constante::$ERROR_CODE['400']);
        }
        $retour = JWT::decode($tokenVerif, $key, array('HS512'));
        return $retour;
    } catch (Exception $th) {
        if ($type === "rt") {
            // //raha refresh token misy blem de averina fona fa hatao logoout iny aveo
            // try {
            //     $con->beginTransaction();
            //     //invalidate
            //     //echo sha1($token);
            //     $rt = new RefreshToken("", sha1($token), Constante::$REFRESH_TOKEN_VALIDE);
            //     $rt = $rt->getByToken($con);
            //     if ($rt != null) {
            //         $rt->setEtat(Constante::$REFRESH_TOKEN_REVOKED);
            //         $rt->invalidate($con);
            //         throw new Exception("refresh token invalid,you've been kicked out of server", Constante::$ERROR_CODE['401']);
            //     } else {
            //         throw new Exception("no login for this users dd", Constante::$ERROR_CODE['400']);
            //     }
            // } catch (Exception $ex) {
            //     throw $ex;
            // } finally {
            //     $con->commit();
            // }
            return 'lany fa mandeh fona';
        } else {
            throw new Exception($th->getMessage(), Constante::$ERROR_CODE['401']);
        }
    }
});
Flight::map('refreshAccessToken', function (PDO $con) {
    try {
        $token = Flight::getTokenHeader("rt");
        if (empty($token)) throw new Exception("refresh token not found");
        //verifiena ao am bdd aloha sode efa loged out
        $rt = new RefreshToken("", sha1($token), Constante::$REFRESH_TOKEN_VALIDE);
        $rt = $rt->getByToken($con);
        if ($rt == null) throw new Exception("no login for this users dd", Constante::$ERROR_CODE['400']);


        $data = Flight::verifyToken($token, "rt", $con);


        //eto maka anle data
        $ac = Flight::getAccesToken($data->data->id, $data->data->nom);
        return $ac;
    } catch (Exception $th) {
        throw new Exception($th->getMessage(), Constante::$ERROR_CODE['401']);
    }
});

Flight::map('getHeader', function (string $type) {
    $headers = null;
    $type = preg_replace('/-/', '_', $type);

    if (isset($_SERVER[trim($type)])) {
        $headers = trim($_SERVER[strtolower(trim($type))]);
    } else if (isset($_SERVER['HTTP_' . strtoupper(trim($type))])) { //Nginx or fast CGI
        $headers = trim($_SERVER["HTTP_" . strtoupper(trim($type))]);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
        $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
        //print_r($requestHeaders);
        if (isset($requestHeaders[strtolower(trim($type))])) {
            $headers = trim($requestHeaders[trim($type)]);
        }
    }
    return $headers;
});
/**
 * get access token from header
 * */
//type ac,rt
Flight::map('getTokenHeader', function (string $type) {
    $headers = null;
    if ($type === "rt") $headers = Flight::getHeader(Constante::$REFRESH_TOKEN_NAME);
    else if ($type === "mobil") $headers = Flight::getHeader(Constante::$MOBILE_TOKEN_NAME);
    else if ($type === "ac") $headers = Flight::getHeader(Constante::$ACCES_TOKEN_NAME);
    // HEADER: Get the access token from the header
    if (!empty($headers)) {
        return trim($headers);
    }
    return $headers;
});
//Content-Type,Connection,Accept
Flight::map('getAccesControl', function () {
    // $httpOrigin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : null;
    // $httpOrigin = $_SERVER['HTTP_ORIGIN'];
    // if ($httpOrigin == 'https://safe-corner.herokuapp.com') {
    //     header('Access-Control-Allow-Origin: https://safe-corner.herokuapp.com');
    // } else if ($httpOrigin == 'http://localhost:4200') {
    //     header('Access-Control-Allow-Origin: http://localhost:4200');
    // }
    header('Access-Control-Allow-Origin: https://safe-corner.herokuapp.com');
    header('Access-Control-Allow-Headers: sc-access-token,sc-init,sc-refresh-token,Content-Type');
    header('Access-Control-Allow-Credentials: true');
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Methods: GET,PUT,POST,DELETE,OPTIONS');
});

// Flight::map('getAccesControlPublic', function () {
//     header('Access-Control-Allow-Headers:sc-mobile-token,sc-init,sc-access-token,Content-Type');
//     header('Access-Control-Allow-Origin: *');
//     header('Content-Type: application/json; charset=utf-8');
//     header('Access-Control-Allow-Methods: GET,OPTIONS');
// });

Flight::map('protectionPage', function ($Pagetype) {
    try {
        if ($Pagetype === "public-private") {
            $token = Flight::getTokenHeader("mobil");
            if (empty($token)) {
                $token = Flight::getTokenHeader("ac");
                if (empty($token)) throw new Exception("token missing", Constante::$ERROR_CODE['401']);
                $res = Flight::verifyToken($token, "ac");
                return $res;
            } else {
                $res = Flight::verifyToken($token, "mobil");
                return $res;
            }
        } else if ($Pagetype === "logout") {
            $token = Flight::getTokenHeader("rt");
            $verificationType = "rt";
            if (empty($token))  throw new Exception("token missing", Constante::$ERROR_CODE['401']);
            $res = Flight::verifyToken($token, $verificationType);
            return $token;
        } else {
            $verificationType = "ac";
            if ($Pagetype === "public") $verificationType = "mobil";
            $token = Flight::getTokenHeader($verificationType);
            if (empty($token)) throw new Exception("token missing", Constante::$ERROR_CODE['401']);
            $res = Flight::verifyToken($token, $verificationType);
            return $res;
        }
    } catch (Exception $ex) {
        if ($ex->getCode() != 500) {
            // echo $ex->getCode();
            // Flight::stop();

            Flight::halt($ex->getCode(), json_encode(new ApiResponse("error", $ex->getCode(), null, $ex->getMessage())));
        } else {
            // Flight::stop();

            Flight::halt($ex->getCode(), json_encode(new ApiResponse("error", $ex->getCode(), null, "server error please contact api providers")));
        }
        throw $ex;
    }
});
Flight::map('initMobileApp', function (PDO $con) {
    try {
        $headerInit = Flight::getHeader(Constante::$MOBILE_INIT);
        if (empty($headerInit)) throw new Exception("cannot init", Constante::$ERROR_CODE['400']);
        //mamorona token
        if ($headerInit === "") throw new Exception("cannot init", Constante::$ERROR_CODE['400']);
        $res = Flight::getMobileToken($headerInit);
        //inserena anaty base
        $tkmb = new TokenMobile($headerInit, Constante::$REFRESH_TOKEN_VALIDE);
        $tkmb->insert($con);
        return $res;
    } catch (Exception $ex) {
        if ($ex->getCode() != 400) {
            throw new Exception("cannot init on non unique id", Constante::$ERROR_CODE['400']);
        } else {
            throw $ex;
        }
    }
});
