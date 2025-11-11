<?php
/**
 * API Endpoint pour le matchmaking (FrontOffice)
 * Route: /api/matchmaking.php?action=...
 */

require_once __DIR__ . '/../controllers/MatchmakingController.php';

// Headers CORS (si nécessaire)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$controller = new MatchmakingController();

switch ($action) {
    case 'ajouter_attente':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->ajouterAttente();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'statut_attente':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->getStatutAttente();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'jeux_achetes':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->getJeuxAchetes();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
        break;
}

?>


