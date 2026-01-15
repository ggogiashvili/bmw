<?php
/**
 * მონაცემთა ბაზის კონფიგურაცია
 * 
 * ამ ფაილში:
 * 1. მითითებულია მონაცემთა ბაზის პარამეტრები
 * 2. იქმნება PDO კავშირი MySQL-თან
 * 3. დაყენებულია exception-ების რეჟიმი
 * 4. დაყენებულია default fetch mode associative array-ზე
 */
$host = 'localhost';
$db_name = 'bmw_db';
$username = 'root'; 
$password = '';     

try {
    // PDO კავშირის შექმნა
    $pdo = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);

    // PDO-სთვის exception-ების რეჟიმის დაყენება (შეცდომებისთვის)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Default fetch mode-ის დაყენება associative array-ზე
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>