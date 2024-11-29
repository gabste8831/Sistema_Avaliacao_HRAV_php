<?php
$host = 'localhost'; 
$dbname = 'sistema_avaliacao';
$user = 'postgres'; 
$password = 'postgres'; 

try {

    $conn = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
    die(); 
}
?>
