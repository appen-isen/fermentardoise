<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once 'resources/config.php';
    require_once 'resources/database.php';
    require_once LIBRARY_PATH . '/redirect.php';
    require_once LIBRARY_PATH . '/exceptions.php';

    $pathInfo = explode('/', trim($_SERVER['PATH_INFO'], '/\\'));

    header('content-type: application/json; charset=utf-8');

    $db = new Database();

    function getAuthorizationToken(): ?string{
    
        $headers = getallheaders();
    
        $authorization = $headers['Authorization'];
    
        if (!isset($authorization)) {
            APIErrors::invalidHeader();
        }
    
        $authorization = explode(' ', trim($authorization), 2)[1];
    
        if (empty($authorization)) {
            APIErrors::invalidGrant();
        }
        return $authorization;
    }

    class APIErrors{
    
        public static function invalidGrant()
        {
            http_response_code(400);
            die(json_encode(array(
                'error' => 'invalid_grant',
                'error_description' => 'The authorization code is invalid or expired.'
            )));
        }
    
        public static function invalidHeader()
        {
            http_response_code(400);
            die(json_encode(array(
                'error' => 'invalid_header',
                'error_description' => 'The request is missing the Authorization header or the Authorization header is invalid.'
            )));
        }
    
        public static function invalidRequest()
        {
            http_response_code(400);
            die(json_encode(array(
                'error' => 'invalid_request',
                'error_description' => 'The request is missing a parameter, uses an unsupported parameter, uses an invalid parameter or repeats a parameter.'
            )));
        }
    
        public static function invalidCredential()
        {
            http_response_code(400);
            die(json_encode(array(
                'error' => 'invalid_credential',
                'error_description' => 'The request has error(s) in the credentials gave.'
            )));
        }
    
        public static function internalError()
        {
            http_response_code(500);
            die();
        }
    }

    switch ($pathInfo[0] . $_SERVER['REQUEST_METHOD']) {
        case 'login' . 'POST':
            $email = $_POST['email'];
            $password = $_POST['pwd'];
    
            if (!isset($email) || !isset($password)) {
                APIErrors::invalidRequest();
            }

            try {
                $result = $db->connectUser($email, $password, time()+14400);
            } catch (AuthenticationException $_) {
                APIErrors::invalidGrant();
            }

            http_response_code(200);
            die(json_encode(array(
                /*'access_token' => $access_token,
                'created_at' => time(),
                'token_type' => 'bearer'*/
                'pass' => 'OK'
            )));

        case 'logout' . 'POST':
            $authorization = getAuthorizationToken();
    
            try {
                $db->disconnectUser($authorization);
            } catch (AuthenticationException $_) {
                APIErrors::invalidGrant();
            }
    
            http_response_code(200);
            die(json_encode(array(
                'message' => 'Authorization code delete successfully.'
            )));
        case 'getEmail' . 'POST':
            $authorization = getAuthorizationToken();
            try {
                $result = $db->getEmail($authorization);
            } catch (AuthenticationException $_) {
                APIErrors::invalidGrant();
            }
    
            http_response_code(200);
            die(json_encode($result));

        case 'test' . 'GET' :
            http_response_code(200);
            die(json_encode(test()));
        default:
		http_response_code(404);
		die();
    }
?>