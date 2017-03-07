<?php

class Database
{
    private $db_host = "localhost";
    private $db_name = "lovecanvas";
    private $db_user = "root";
    private $db_pass = "TwofRag83";
    private $dbh;
    private $error;


    public function __construct(){
        // Set DSN
        $dsn = 'mysql:host=' . $this->db_host . ';dbname=' . $this->db_name;
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT    => true,
            PDO::ATTR_ERRMODE       => PDO::ERRMODE_EXCEPTION
        );
        // Create a new PDO instanace
        try {
            $this->db = new \pdo($dsn, $this->db_user, $this->db_pass, $options);
        } catch (\pdoexception $e) {
            echo "database error: " . $e->getmessage();
            die();
        }
        $this->db->query('set names utf8');
    }
}
?>
