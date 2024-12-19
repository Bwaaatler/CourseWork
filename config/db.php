<?php
$host = 'localhost';
$dbname = 'course';
$usernamefordb = 'root'; 
$passwordfordb = ''; 
$port = 3307; 

try {

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $usernamefordb, $passwordfordb);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения: " . $e->getMessage());
}
?>