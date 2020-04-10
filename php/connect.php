<?php
//connect.php
//MYSQL DB Details
define('MYSQL_USER', 'root');
define('MYSQL_PASSWORD', '');
define('MYSQL_HOST', 'localhost:3308');
define('MYSQL_DATABASE', 'twitter_database');

//PDO Options
$pdoOptions = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false) ;

//Make Connection to MYSQL
try {
    $pdo = new PDO(
        "mysql:host=" . MYSQL_HOST . ";dbname=" . MYSQL_DATABASE,
        MYSQL_USER,
        MYSQL_PASSWORD,
        $pdoOptions
    );
}
catch (PDOException $e) { print $e->getMessage();

}

?>