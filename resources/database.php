<?php
    /**
     * PHP Version 8.2.1
     */
    
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
    }
?>