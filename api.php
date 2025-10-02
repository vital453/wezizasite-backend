<?php
/**
 * API pour le formulaire de contact de Team Phénix
 */

// -----------------------------------------------------------------------------
// ÉTAPE 1 : CONFIGURATION DE SÉCURITÉ (CORS)
// -----------------------------------------------------------------------------
// Ceci est CRUCIAL. Il autorise votre site 'teamphenix229.com' (le frontend)
// à envoyer des requêtes à ce script sur 'backend.teamphenix229.com'.
// Sans cela, le navigateur bloquera la requête pour des raisons de sécurité.
header("Access-Control-Allow-Origin: https://teamphenix229.com"); 
header("Access-Control-Allow-Methods: POST, OPTIONS"); // On autorise uniquement les requêtes POST et OPTIONS
header("Access-Control-Allow-Headers: Content-Type"); // On autorise l'en-tête Content-Type
header("Content-Type: application/json"); // On indique que notre réponse sera en format JSON

// Le navigateur envoie une requête "preflight" OPTIONS avant le POST. Il faut y répondre.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -----------------------------------------------------------------------------
// ÉTAPE 2 : RÉCUPÉRATION ET VALIDATION DES DONNÉES
// -----------------------------------------------------------------------------
// On récupère le corps de la requête qui est en format JSON
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true); // On le décode en tableau PHP

// Validation simple : on vérifie que les champs essentiels ne sont pas vides
if (empty($data['email']) || empty($data['besoin']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    // Si les données sont invalides, on renvoie une erreur 400 (Bad Request)
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Veuillez fournir une adresse email valide et décrire votre besoin.'
    ]);
    exit; // On arrête le script
}

// -----------------------------------------------------------------------------
// ÉTAPE 3 : TRAITEMENT (ENVOI D'EMAIL)
// -----------------------------------------------------------------------------
// !! MODIFIEZ CETTE LIGNE AVEC VOTRE VRAIE ADRESSE EMAIL !!
$recipientEmail = "VOTRE_ADRESSE_EMAIL@exemple.com"; 

// On nettoie les données pour éviter les injections de code dans les emails
$prenom = isset($data['prenom']) ? filter_var($data['prenom'], FILTER_SANITIZE_STRING) : 'Non fourni';
$nom = isset($data['nom']) ? filter_var($data['nom'], FILTER_SANITIZE_STRING) : '';
$entreprise = isset($data['entreprise']) ? filter_var($data['entreprise'], FILTER_SANITIZE_STRING) : 'Non fournie';
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$telephone = isset($data['telephone']) ? filter_var($data['telephone'], FILTER_SANITIZE_STRING) : 'Non fourni';
$besoin = filter_var($data['besoin'], FILTER_SANITIZE_STRING);
$action = isset($data['action']) ? filter_var($data['action'], FILTER_SANITIZE_STRING) : 'Action non spécifiée';

// Sujet de l'email
$subject = "Nouvelle demande de démo via le site : " . $prenom . " " . $nom;

// Corps de l'email
$emailBody = "Une nouvelle demande a été soumise depuis le site teamphenix229.com :\n\n";
$emailBody .= "Action souhaitée: " . $action . "\n";
$emailBody .= "--------------------------------------------------\n";
$emailBody .= "Prénom: " . $prenom . "\n";
$emailBody .= "Nom: " . $nom . "\n";
$emailBody .= "Entreprise: " . $entreprise . "\n";
$emailBody .= "Email: " . $email . "\n";
$emailBody .= "Téléphone: " . $telephone . "\n\n";
$emailBody .= "Besoin principal:\n" . $besoin . "\n";

// En-têtes de l'email
$headers = "From: no-reply@teamphenix229.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// Envoi de l'email
if (mail($recipientEmail, $subject, $emailBody, $headers)) {
    // Si l'envoi a réussi, on renvoie un succès 200 (OK)
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'message' => 'Votre demande a bien été envoyée. Nous vous recontacterons bientôt.'
    ]);
} else {
    // Si l'envoi a échoué, on renvoie une erreur 500 (Internal Server Error)
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Une erreur est survenue lors de l\'envoi de votre message. Veuillez réessayer.'
    ]);
}

?>