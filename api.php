<?php
/**
 * API pour le formulaire de contact de Team Phénix
 * Version finale optimisée : répond au client dès que la donnée est sauvegardée.
 */

// -----------------------------------------------------------------------------
// ÉTAPE 1 : CONFIGURATION DE SÉCURITÉ (CORS)
// -----------------------------------------------------------------------------
// Autorise le site frontend à communiquer avec ce backend.
header("Access-Control-Allow-Origin: https://teamphenix229.com"); 
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

// Le navigateur envoie une requête "preflight" (OPTIONS) pour vérifier les permissions.
// Il est nécessaire d'y répondre correctement pour que la requête POST puisse suivre.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// -----------------------------------------------------------------------------
// ÉTAPE 2 : RÉCUPÉRATION ET VALIDATION DES DONNÉES
// -----------------------------------------------------------------------------
// On récupère le corps de la requête (qui est en format JSON)
$inputJSON = file_get_contents('php://input');
$data = json_decode($inputJSON, true);

// Validation des données : on s'assure que les champs requis sont présents et valides.
if (empty($data['email']) || empty($data['besoin']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // 400 Bad Request
    echo json_encode([
        'status' => 'error',
        'message' => 'Veuillez fournir une adresse email valide et décrire votre besoin.'
    ]);
    exit;
}

// -----------------------------------------------------------------------------
// ÉTAPE 3 : CONNEXION ET ENREGISTREMENT DANS LA BASE DE DONNÉES (ACTION CRITIQUE)
// -----------------------------------------------------------------------------
$dbHost = 'localhost';
$dbName = 'teamp2675619';
$dbUser = 'teamp2675619';
$dbPass = 'bsvymz2iyz';

try {
    // Connexion à la base de données via PDO
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Requête préparée pour insérer les données en toute sécurité (prévient les injections SQL)
    $sql = "INSERT INTO demandes (prenom, nom, entreprise, email, telephone, besoin, action_souhaitee) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    // Exécution de la requête avec les données reçues
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
    // Si la connexion ou l'insertion échoue, c'est une erreur serveur critique.
    // On peut enregistrer l'erreur technique pour le développeur (invisible pour l'utilisateur).
    error_log("Erreur BDD: " . $e->getMessage());
    
    // On envoie une réponse d'erreur générique à l'utilisateur.
    http_response_code(500); // 500 Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Une erreur interne est survenue. Impossible d\'enregistrer votre demande.'
    ]);
    exit;
}

// -----------------------------------------------------------------------------
// SUCCÈS : LA DEMANDE EST SAUVEGARDÉE. ON PEUT RÉPONDRE AU CLIENT.
// -----------------------------------------------------------------------------
// On envoie la réponse de succès au navigateur de l'utilisateur.
// L'interface sur le site affichera alors le message de confirmation.
http_response_code(200); // 200 OK
echo json_encode([
    'status' => 'success',
    'message' => 'Votre demande a bien été reçue. Nous vous recontacterons bientôt.'
]);

// Le script continue son exécution sur le serveur pour les tâches secondaires.

// -----------------------------------------------------------------------------
// ÉTAPE 4 : ENVOI DE L'EMAIL DE NOTIFICATION (ACTION SECONDAIRE)
// -----------------------------------------------------------------------------
$recipientEmail = "mevivital@gmail.com"; 

// On nettoie les données pour l'email (mesure de sécurité)
$prenom = isset($data['prenom']) ? filter_var($data['prenom'], FILTER_SANITIZE_STRING) : 'Non fourni';
$nom = isset($data['nom']) ? filter_var($data['nom'], FILTER_SANITIZE_STRING) : '';
$entreprise = isset($data['entreprise']) ? filter_var($data['entreprise'], FILTER_SANITIZE_STRING) : 'Non fournie';
$email = filter_var($data['email'], FILTER_SANITIZE_EMAIL);
$telephone = isset($data['telephone']) ? filter_var($data['telephone'], FILTER_SANITIZE_STRING) : 'Non fourni';
$besoin = filter_var($data['besoin'], FILTER_SANITIZE_STRING);
$action = isset($data['action']) ? filter_var($data['action'], FILTER_SANITIZE_STRING) : 'Action non spécifiée';

// Construction de l'email
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
$headers = "From: no-reply@teamphenix229.com\r\n" .
           "Reply-To: " . $email . "\r\n" .
           "Content-Type: text/plain; charset=UTF-8\r\n";

// On tente d'envoyer l'email.
if (!mail($recipientEmail, $subject, $emailBody, $headers)) {
    // Si l'envoi échoue, on l'enregistre dans les logs du serveur.
    // L'utilisateur ne verra rien car il a déjà reçu sa réponse de succès.
    error_log("Alerte: Échec de l'envoi de l'email de notification pour la soumission de " . $email);
}

// Fin du script.
exit();

?>