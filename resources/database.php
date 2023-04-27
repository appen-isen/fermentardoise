<?php
    /**
     * PHP Version 8.2.1
     */
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require_once 'config.php';
    require_once 'library/exceptions.php';

    /**
     * Collection of methods to communicate with the database.
     */
    class Database {
        protected $PDO;

        /**
         * Connect to the PostgreSQL database.
         * 
         * @throws PDOException Error thrown if the connection
         *                      to the database failed.
         */
        public function __construct(){
            $this->PDO = new PDO(
                'pgsql:host=' . DB_SERVER . ';port=' . DB_PORT . ';dbname=' . DB_NAME,
                DB_USER,
                DB_PASSWORD
            );
        }   
        
        /**
         * Gets the password hash of a user.
         * 
         * @param string $eamil
         * 
         * @return ?string if the password hash exists.
         */
        public function getUserPasswordHash(string $email): ?string{
            $email = strtolower($email);

            $request = 'SELECT password_hash from users where email = :email';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':email', $email);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_OBJ);

            if (!$result) {
                return NULL;
            }
    
            return $result->password_hash;
        }

        /**
         * Verifies the user credentials.
         * 
         * @param string $email
         * @param string $password
         * 
         * @return bool
         */
        public function verifyUserCredentials(string $email, string $password): bool {
            $password_hash = $this->getUserPasswordHash($email);

            return !empty($password_hash) && password_verify($password, $password_hash);
        }

        /**
         * Verifies the user access token.
         * 
         * @param string $access_token
         * 
         * @return bool
         */
        public function verifyUserAccessToken(string $access_token): bool {
            $request = 'SELECT * from users where access_token = :access_token';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':access_token', $access_token);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_OBJ);

            return !empty($result);
        }

        /**
         * Connects the user by returning its unique id if the 
         * credentials are valid.
         * 
         * @param string $email
         * @param string $password
         * @param int $session_expire (optional) The lifetime of the session cookie in seconds.
         * 
         * @throws AuthenticationException If the authentication failed.
         */
        public function connectUser(string $email, string $password, int $session_expire = 0): bool {
            // test if the credentials are correct
            if (!$this->verifyUserCredentials($email, $password)) {
                throw new AuthenticationException();
            }

            // make email lowercase in case the user used uppercase letters
            $email = strtolower($email);

            // create a unique token used to identify the user
            $access_token = hash('sha256', $email . $password . microtime(true));

            // Set session hash on the user
            $request = 'UPDATE users SET access_token = :access_token
                            WHERE email = :email';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':email', $email);
            $statement->bindParam(':access_token', $access_token);
            $success = $statement->execute();

            // Throw an exception if the update failed
            if (!$success) {
                throw new Exception('Failed to connect user.');
            }

            if ($session_expire > 0) {
                $session_expire = time() + $session_expire;
            }

            // set the session cookie
            return setcookie(
                ACCESS_TOKEN_NAME,
                $access_token,
                $session_expire,
                "/"
            );
        }

        /**
         * Create an access token if the credentials are valid
         * the functionality is the same as connectUser but 
         * this function is related to AJAX request
         * 
         * @param string $email
         * @param string $password
         * 
         * @return string the access_token
         */
        public function getUserAccessToken(string $email, string $password): ?string {
            if (!$this->verifyUserCredentials($email, $password)) {
                return NULL;
            }

            $email = strtolower($email);

            $access_token = hash('sha256', $email . $password . time());

            //Set the access token to the user
            $request = 'UPDATE users set access_token = :access_token where email = :email';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':access_token', $access_token);
            $statement->bindParam(':email', $email);
            $statement->execute();

            return $access_token;
        }


        /**
         * Disconnects the user by deleting the access token.
         * 
         * @param string $access_token 
         * 
         * @throws AuthenticationException If the access token is invalid.
         */
        public function disconnectUser($access_token): bool{
            if (!$this->verifyUserAccessToken($access_token)) {
                throw new AuthenticationException();
            }

            // remove access token from the user
            $request = 'UPDATE users SET access_token = NULL
                            WHERE access_token = :access_token';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':access_token', $access_token);
            $success = $statement->execute();

            // delete the session cookie
            unset($_COOKIE[ACCESS_TOKEN_NAME]); 
            setcookie(ACCESS_TOKEN_NAME, '', -1, '/');
            return $success;
        }

        /**
         * Gets the user email
         * 
         * @param string $access_token
         * 
         * @return ?string 
         */
        public function getEmail(string $access_token): ?string{
            if (!$this->verifyUserAccessToken($access_token)) {
                throw new AuthenticationException();
            }

            $request = 'SELECT email from users where access_token = :access_token';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':access_token', $access_token);
            $statement->execute();

            $result = $statement->fetch(PDO::FETCH_OBJ);
            
            return $result->email;
        }

        public function test(){
            if (!isset($_COOKIE[ACCESS_TOKEN_NAME])) {
                return false;
            }

            $access_token = $_COOKIE[ACCESS_TOKEN_NAME];

            if (!$this->verifyUserAccessToken($access_token)) {
                throw new AuthenticationException();
            }

            $request = 'SELECT * from users where access_token = :access_token';

            $statement = $this->PDO->prepare($request);
            $statement->bindParam(':access_token', $access_token);
            $statement->execute();

            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            
            return $result;
        }
    }
?>