<?php
// Fichier : config.php

// Paramètres de la base de données
$db_host = 'localhost';
$db_name = 'ticket_system'; // Le nom de la base de données que vous avez créée
$db_user = 'root';          // Votre nom d'utilisateur pour la BDD
$db_pass = '';              // Votre mot de passe pour la BDD

// DSN (Data Source Name)
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8";

try {
    // Créer une instance de PDO (PHP Data Objects)
    $pdo = new PDO($dsn, $db_user, $db_pass);
    // Définir le mode d'erreur de PDO sur Exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // En cas d'erreur de connexion, on arrête tout et on affiche un message
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>