<?php
/**
 * Script de test pour l'envoi d'email via PHPMailer avec authentification SMTP.
 * Ce script permet de diagnostiquer les problèmes de connexion au serveur de messagerie.
 */

// Affiche toutes les erreurs pour un débogage facile.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// On importe les classes de la bibliothèque PHPMailer.
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// On charge les fichiers nécessaires de la bibliothèque.
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Test de PHPMailer avec SMTP</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1, h2 { color: #333; }
        p, li { color: #555; }
        blockquote { background: #f0f0f0; border-left: 5px solid #ccc; padding: 10px; font-family: monospace; white-space: pre-wrap; }
    </style>
</head>
<body>

<h1>Test d'envoi d'email avec PHPMailer/SMTP</h1>
<hr>

<?php

// --- VOS PARAMÈTRES SMTP ---
// (Ces informations proviennent de votre panel LWS)
$smtpHost = 'mail.teamphenix229.com';
$smtpUsername = 'contact@teamphenix229.com';
$smtpPassword = 'fF3@auCmwgbgwWj'; // !! REMPLACEZ CECI PAR LE VRAI MOT DE PASSE !!
$smtpPort = 465;

// --- DESTINATAIRE DU TEST ---
// Mettez une adresse à laquelle vous avez accès pour vérifier la réception.
$to = 'mevivital@gmail.com';


// On crée une nouvelle instance de PHPMailer. Le `true` active les exceptions en cas d'erreur.
$mail = new PHPMailer(true);

try {
    // --- Configuration du serveur ---
    echo "<p>1. Configuration de PHPMailer...</p>";

    // Décommentez la ligne suivante pour un diagnostic très détaillé de la connexion SMTP.
    // $mail->SMTPDebug = 2;

    $mail->isSMTP();
    $mail->Host       = $smtpHost;
    $mail->SMTPAuth   = true;
    $mail->Username   = $smtpUsername;
    $mail->Password   = $smtpPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = $smtpPort;

    // --- Configuration des adresses ---
    echo "<p>2. Configuration des adresses (Expéditeur / Destinataire)...</p>";
    $mail->setFrom($smtpUsername, 'Test Technique LWS');
    $mail->addAddress($to);

    // --- Configuration du contenu de l'email ---
     echo "<p>3. Configuration du contenu de l'email...</p>";
    $mail->isHTML(false); // Email en format texte
    $mail->CharSet = 'UTF-8';
    $mail->Subject = 'Email de test - PHPMailer/SMTP';
    $mail->Body    = "Bonjour,\n\nCeci est un email de test envoyé via PHPMailer avec authentification SMTP.\n\nSi vous recevez ce message, votre configuration est parfaite !";

    // --- Envoi de l'email ---
    echo "<p><strong>4. Tentative d'envoi de l'email...</strong></p>";
    $mail->send();

    // --- Message de succès ---
    echo "<h2><font color='green'>SUCCÈS !</font></h2>";
    echo "<p>L'email a été envoyé avec succès à <strong>" . htmlspecialchars($to) . "</strong>.</p>";
    echo "<p>Votre configuration SMTP (serveur, identifiant, mot de passe, port) est correcte.</p>";
    echo "<p>Vérifiez votre boîte de réception (et vos spams).</p>";

} catch (Exception $e) {
    // --- Message d'erreur ---
    echo "<h2><font color='red'>ÉCHEC !</font></h2>";
    echo "<p>L'email n'a pas pu être envoyé. PHPMailer a généré l'erreur suivante :</p>";
    echo "<blockquote><strong>" . htmlspecialchars($mail->ErrorInfo) . "</strong></blockquote>";
    echo "<p><strong>Actions à vérifier :</strong></p>";
    echo "<ul>";
    echo "<li>Le mot de passe SMTP est-il correct dans ce fichier de test ?</li>";
    echo "<li>Le nom du serveur SMTP (<code>" . htmlspecialchars($smtpHost) . "</code>) est-il correct ?</li>";
    echo "<li>Le port SMTP (<code>" . htmlspecialchars($smtpPort) . "</code>) est-il correct ?</li>";
    echo "</ul>";
}
?>

</body>
</html>