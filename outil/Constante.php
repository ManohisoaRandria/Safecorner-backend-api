<?php
class Constante
{
    //     GET: 200 OK
    // POST: 201 Created
    // PUT: 200 OK
    // PATCH: 200 OK
    // DELETE: 204 No Content
    //     400 Bad Request – This means that client-side input fails validation.
    // 401 Unauthorized – This means the user isn’t not authorized to access a resource. 
    // It usually returns when the user isn’t authenticated.
    // 403 Forbidden – This means the user is authenticated, but it’s not allowed to access a resource.
    // 404 Not Found – This indicates that a resource is not found.
    // 500 Internal server error – This is a generic server error. It probably shouldn’t be thrown explicitly.
    // 502 Bad Gateway – This indicates an invalid response from an upstream server.
    // 503 Service Unavailable – This indicates that something unexpected happened on server 
    // side (It can be anything like server overload, some parts of the system failed, etc.).
    // If an error occurs in the global catch blog, the stracktrace should be logged and not returned as response.
    public static $SUCCES_CODE = array(
        '200' => 200,
        '201' => 201,
        '204' => 204
    );
    public static $ERROR_CODE = array(
        '400' => 400,
        '401' => 401,
        '403' => 403,
        '404' => 404,
        '500' => 500,
        '502' => 502,
        '503' => 503,
    );
    public static $ID_COUNT = 4;
    public static $PROTOCOLE_ACTIVE = 1;
    public static $PROTOCOLE_NON_ACTIVE = 10;
    public static $DESCENTE_VALIDE = 1;
    public static $DESCENTE_ANNULER = 10;
    public static $HISTORIQUE_PROTOCOLE_ADD = 1;
    public static $HISTORIQUE_PROTOCOLE_DELETE = 10;
    public static $MAX_PAGINATION_PER_PAGE = 20;
    public static $MAX_LOGIN_SESSION_PER_USER = 2;
    public static $BASE = '/api/v1/';
    public static $ACCES_TOKEN_EXPIRE_TIME = 300 /*5 minutes*/; //seconde
    public static $REFRESH_TOKEN_EXPIRE_TIME = 86400 /*1  jour*/; //seconde
    public static $REFRESH_TOKEN_VALIDE = 1;
    public static $REFRESH_TOKEN_REVOKED = 10;
    public static $SEARCH_RADIUS = 1170; //en metre avec marge 80 m
    public static $REFRESH_TOKEN_KEY = "keyBackOfficeRFT-api";
    public static $REFRESH_ENCRYPTION_KEY = "keyBackOfficeRFTenc-api";
    public static $ACCES_TOKEN_KEY = "keyBackOfficeACT-api";
    public static $MOBILE_TOKEN_KEY = "keyFrontOfficeACT-api";
    public static $ACCES_TOKEN_NAME = "sc-access-token";
    public static $MOBILE_TOKEN_NAME = "sc-mobile-token";
    public static $MOBILE_INIT = "sc-init";
    public static $REFRESH_TOKEN_NAME = "sc-refresh-token";
    public static $SERVER_NAME = "http://localhost/safecorne";
}
