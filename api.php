<?php
/**
 * API pour le formulaire de contact de Team Phénix
 * Version finale optimisée avec gestion des copies (Cc) et copies cachées (Bcc).
 */

// -----------------------------------------------------------------------------
// ÉTAPE 1 : CONFIGURATION DE SÉCURITÉ (CORS)
// -----------------------------------------------------------------------------
header("Access-Control-Allow-Origin: https://www.teamphenix229.com"); // Version corrigée avec www.
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -----------------------------------------------------------------------------
// ÉTAPE 2 : RÉCUPÉRATION ET VALIDATION DES DONNÉES
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
$dbHost = '91.216.107.161'; // Version corrigée avec la bonne IP
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
    // Enregistre l'erreur dans les logs du serveur (invisible pour l'utilisateur)
    error_log("Erreur BDD: " . $e->getMessage());
    
    // Envoie une réponse d'erreur générique à l'utilisateur.
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Une erreur interne est survenue. Impossible d\'enregistrer votre demande.']);
    exit;
}

// -----------------------------------------------------------------------------
// SUCCÈS : LA DEMANDE EST SAUVEGARDÉE. ON PEUT RÉPONDRE AU CLIENT.
// -----------------------------------------------------------------------------
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'message' => 'Votre demande a bien été reçue. Nous vous recontacterons bientôt.'
]);

// -----------------------------------------------------------------------------
// ÉTAPE 4 : ENVOI DE L'EMAIL DE NOTIFICATION
// -----------------------------------------------------------------------------
$recipientEmail = "vital@urban-technology.net"; 
// $recipientEmail = "ogoudjobidonald@gmail.com"; 
$copy_to = "mevivital@gmail.com, mevivital453@gmail.com"; // MODIFIEZ ICI : emails en copie visible
$blind_copy_to = "vital@urban-technology.net";               // MODIFIEZ ICI : email en copie cachée

// On nettoie les données pour l'email
$prenom = isset($data['prenom']) ? filter_var($data['prenom'], FILTER_SANITIZE_STRING) : 'Non fourni';
$nom = isset($data['nom']) ? filter_var($data['nom'], FILTER_SANITIZE_STRING) : '';
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
// ... (les autres variables ne changent pas)
$entreprise = isset($data['entreprise']) ? filter_var($data['entreprise'], FILTER_SANITIZE_STRING) : 'Non fournie';
$telephone = isset($data['telephone']) ? filter_var($data['telephone'], FILTER_SANITIZE_STRING) : 'Non fourni';
$besoin = filter_var($data['besoin'], FILTER_SANITIZE_STRING);
$action = isset($data['action']) ? filter_var($data['action'], FILTER_SANITIZE_STRING) : 'Action non spécifiée';


// Construction du sujet et du corps de l'email (ne change pas)
$subject = "Nouvelle demande via le site : " . $prenom . " " . $nom;
$emailBody = "Une nouvelle demande a été soumise depuis le site teamphenix229.com :\n\n" .
             "Action souhaitée: " . $action . "\n" .
             "--------------------------------------------------\n" .
             "Prénom: " . $prenom . "\n" .
             "Nom: " . $nom . "\n" .
             "Entreprise: " . $entreprise . "\n" .
             "Email: " . $email . "\n" .
             "Téléphone: " . $telephone . "\n\n" .
             "Besoin principal:\n" . $besoin . "\n";

// Construction des en-têtes avec Cc et Bcc
$headers = "From: contact@teamphenix229.com\r\n";
$headers .= "Reply-To: " . $email . "\r\n";
// if (!empty($copy_to)) {
//     $headers .= "Cc: " . $copy_to . "\r\n";
// }
// if (!empty($blind_copy_to)) {
//     $headers .= "Bcc: " . $blind_copy_to . "\r\n";
// }
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

// On envoie l'email.
if (!mail($recipientEmail, $subject, $emailBody, $headers)) {
    // Si l'envoi échoue, on l'enregistre dans les logs du serveur.
    error_log("Alerte: Échec de l'envoi de l'email de notification pour la soumission de " . $email);
}

// Fin du script.
exit();

?>