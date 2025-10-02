<?php
// Active l'affichage de toutes les erreurs PHP. C'est crucial pour le débogage.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>Test d'envoi d'email depuis LWS</h1>";

// --- CONFIGURATION ---
// 1. Mettez ici une de vos adresses personnelles (Gmail, etc.) pour recevoir le test.
$to = "mevivital@gmail.com"; 

// 2. Mettez ici l'adresse email que vous avez VRAIMENT créée sur votre panel LWS.
// C'est l'étape la plus importante !
$from_address = "contact@teamphenix229.com"; // Assurez-vous que cette adresse existe.

// --- PRÉPARATION DE L'EMAIL ---
$subject = "Email de test depuis le serveur teamphenix229.com";
$message = "Bonjour,\n\nSi vous recevez cet email, cela signifie que la fonction mail() de PHP fonctionne correctement sur votre hébergement LWS.\n\nServeur : " . $_SERVER['SERVER_NAME'];
$headers = "From: " . $from_address . "\r\n" .
           "Reply-To: " . $from_address . "\r\n" .
           "X-Mailer: PHP/" . phpversion();

// --- TENTATIVE D'ENVOI ---
echo "<p>Tentative d'envoi de l'email à : <strong>" . $to . "</strong></p>";
echo "<p>Depuis l'adresse : <strong>" . $from_address . "</strong></p>";

if (mail($to, $subject, $message, $headers)) {
    echo "<h2><font color='green'>SUCCÈS :</font> La fonction mail() a retourné VRAI.</h2>";
    echo "<p>L'email a été accepté pour envoi par le serveur. Vérifiez votre boîte de réception (et vos spams) dans les prochaines minutes.</p>";
} else {
    echo "<h2><font color='red'>ÉCHEC :</font> La fonction mail() a retourné FAUX.</h2>";
    echo "<p>Le serveur a refusé d'envoyer l'email. Cela indique un problème de configuration sur le serveur LWS. Contactez leur support technique.</p>";
}
?>