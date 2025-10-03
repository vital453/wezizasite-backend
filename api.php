<?php
/**
 * API pour le formulaire de contact de Team Phénix
 * Version finale utilisant PHPMailer avec authentification SMTP pour une fiabilité maximale.
 */

// On importe les classes de la bibliothèque PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// On charge les fichiers de la bibliothèque qui se trouve dans le dossier 'PHPMailer'
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

// -----------------------------------------------------------------------------
// ÉTAPE 1 : CONFIGURATION DE SÉCURITÉ (CORS)
// -----------------------------------------------------------------------------
header("Access-Control-Allow-Origin: https://www.teamphenix229.com");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Réponse à la requête "preflight" OPTIONS du navigateur
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -----------------------------------------------------------------------------
// ÉTAPE 2 : RÉCUPÉRATION ET VALIDATION DES DONNÉES DU FORMULAIRE
// -----------------------------------------------------------------------------
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

if (empty($data['email']) || empty($data['besoin']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Veuillez fournir une adresse email valide et décrire votre besoin.']);
    exit;
}

// -----------------------------------------------------------------------------
// ÉTAPE 3 : CONNEXION ET ENREGISTREMENT DANS LA BASE DE DONNÉES
// -----------------------------------------------------------------------------
$dbHost = '91.216.107.161';
$dbName = 'teamp2675619';
$dbUser = 'teamp2675619';
$dbPass = 'bsvymz2iyz';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "INSERT INTO demandes (prenom, nom, entreprise, email, telephone, besoin, action_souhaitee) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    $stmt->execute([
        isset($data['prenom']) ? $data['prenom'] : null,
        isset($data['nom']) ? $data['nom'] : null,
        isset($data['entreprise']) ? $data['entreprise'] : null,
        $data['email'],
        isset($data['telephone']) ? $data['telephone'] : null,
        $data['besoin'],
        isset($data['action']) ? $data['action'] : null
    ]);
} catch (PDOException $e) {
    error_log("Erreur BDD: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Impossible d\'enregistrer votre demande.']);
    exit;
}

// -----------------------------------------------------------------------------
// ÉTAPE 4 : ENVOI DE L'EMAIL VIA PHPMailer et SMTP
// -----------------------------------------------------------------------------
$mail = new PHPMailer(true);

try {
    // --- Configuration du serveur SMTP de LWS ---
    // Pour déboguer, décommentez la ligne suivante. Elle affichera TOUTE la conversation avec le serveur SMTP.
    // $mail->SMTPDebug = 2; 
    
    $mail->isSMTP();
    $mail->Host       = 'mail.teamphenix229.com';      // Serveur SMTP sortant (confirmé par votre capture d'écran)
    $mail->SMTPAuth   = true;                          // Activer l'authentification SMTP
    $mail->Username   = 'contact@teamphenix229.com';   // Votre adresse email complète (utilisateur SMTP)
    $mail->Password   = 'fF3@auCmwgbgwWj';    // !! REMPLACEZ CECI PAR LE VRAI MOT DE PASSE de la boite mail !!
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;   // Activer le cryptage SSL
    $mail->Port       = 465;                         // Port TCP pour SSL

    // --- Destinataires ---
    $mail->setFrom('contact@teamphenix229.com', 'Team Phenix Site Web'); // L'expéditeur (doit être l'adresse authentifiée)
    $mail->addAddress('vital@urban-technology.net');     // Destinataire principal
    $mail->addReplyTo($data['email'], ($data['prenom'] ?? '') . ' ' . ($data['nom'] ?? '')); // Pour que "Répondre" aille au client

    // Ajout des copies
    $mail->addCC('mevivital@gmail.com');
    $mail->addCC('mevivital453@gmail.com');
    $mail->addBCC('vital@urban-technology.net');

    // --- Contenu de l'email ---
    $mail->isHTML(false); // On spécifie que l'email est en format texte brut
    $mail->CharSet = 'UTF-8'; // Encodage pour bien gérer les accents

    $subject = "Nouvelle demande via le site : " . ($data['prenom'] ?? '') . " " . ($data['nom'] ?? '');
    
    $emailBody = "Une nouvelle demande a été soumise depuis le site teamphenix229.com :\n\n" .
                 "Action souhaitée: " . ($data['action'] ?? 'N/A') . "\n" .
                 "--------------------------------------------------\n" .
                 "Prénom: " . ($data['prenom'] ?? 'N/A') . "\n" .
                 "Nom: " . ($data['nom'] ?? 'N/A') . "\n" .
                 "Entreprise: " . ($data['entreprise'] ?? 'N/A') . "\n" .
                 "Email: " . ($data['email'] ?? 'N/A') . "\n" .
                 "Téléphone: " . ($data['telephone'] ?? 'N/A') . "\n\n" .
                 "Besoin principal:\n" . ($data['besoin'] ?? 'N/A') . "\n";
                 
    $mail->Subject = $subject;
    $mail->Body    = $emailBody;

    // Envoi de l'email
    $mail->send();
    
    // Si l'envoi réussit, on renvoie une réponse de succès au frontend
    http_response_code(200);
    echo json_encode(['status' => 'success', 'message' => 'Votre demande a bien été envoyée.']);

} catch (Exception $e) {
    // Si PHPMailer lève une exception (échec de connexion, authentification, etc.)
    error_log("PHPMailer Error: {$mail->ErrorInfo}"); // On enregistre l'erreur technique dans les logs du serveur
    
    // On renvoie une réponse d'erreur détaillée mais compréhensible au frontend
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => "L'envoi a échoué. Erreur technique: {$mail->ErrorInfo}"]);
}

// Fin du script
exit();
?>