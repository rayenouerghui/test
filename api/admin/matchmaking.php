<?php
/**
 * API Endpoint pour l'admin du matchmaking (BackOffice)
 * Route: /api/admin/matchmaking.php?action=...
 */

require_once __DIR__ . '/../../controllers/AdminMatchmakingController.php';

// Headers CORS (si nécessaire)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Gérer les requêtes OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// TODO: Vérifier l'authentification admin ici
// session_start();
// if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
//     http_response_code(401);
//     echo json_encode(['success' => false, 'message' => 'Non autorisé']);
//     exit;
// }

$action = isset($_GET['action']) ? $_GET['action'] : '';
$controller = new AdminMatchmakingController();

switch ($action) {
    case 'get_attentes':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->getAttentes();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'get_sessions':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->getSessions();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'verifier_matchs':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->verifierMatchs();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'supprimer_attente':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->supprimerAttente();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'supprimer_session':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->supprimerSession();
        } else {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
        }
        break;
        
    case 'nettoyer_attentes':
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $controller->nettoyerAttentes();
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


