<?php
/**
 * Contrôleur MatchmakingController
 * Gère les requêtes liées au matchmaking
 * Respecte le pattern MVC
 */

require_once __DIR__ . '/../models/AttenteMatchModel.php';
require_once __DIR__ . '/../models/SessionMatchModel.php';
require_once __DIR__ . '/../services/EmailService.php';
require_once __DIR__ . '/../services/MatchService.php';

class MatchmakingController {
    private $attenteModel;
    private $sessionModel;
    private $emailService;
    private $matchService;
    
    public function __construct() {
        $this->attenteModel = new AttenteMatchModel();
        $this->sessionModel = new SessionMatchModel();
        $this->emailService = new EmailService();
        $this->matchService = new MatchService();
    }
    
    /**
     * Ajouter un utilisateur à la file d'attente (Find a Match)
     * @return void (envoie une réponse JSON)
     */
    public function ajouterAttente() {
        header('Content-Type: application/json');
        
        try {
            // Récupérer les données POST
            $data = json_decode(file_get_contents('php://input'), true);
            
            // Validation
            if (!isset($data['id_utilisateur']) || !isset($data['id_jeu'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Données manquantes: id_utilisateur et id_jeu requis'
                ]);
                return;
            }
            
            $idUtilisateur = (int)$data['id_utilisateur'];
            $idJeu = (int)$data['id_jeu'];
            
            // Validation des données
            if ($idUtilisateur <= 0 || $idJeu <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'IDs invalides'
                ]);
                return;
            }
            
            // Vérifier que l'utilisateur a acheté le jeu
            if (!$this->attenteModel->utilisateurAcheteJeu($idUtilisateur, $idJeu)) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'Vous devez avoir acheté ce jeu pour trouver un match'
                ]);
                return;
            }
            
            // Ajouter à la file d'attente
            $idAttente = $this->attenteModel->ajouterAttente($idUtilisateur, $idJeu);
            
            if ($idAttente === false) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Vous êtes déjà en attente pour ce jeu'
                ]);
                return;
            }
            
            // Vérifier si un match est possible
            $resultat = $this->matchService->verifierMatchs($idJeu);
            
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Vous avez été ajouté à la file d\'attente',
                'id_attente' => $idAttente,
                'match_immediat' => ($resultat['matchs_crees'] > 0)
            ]);
            
        } catch (Exception $e) {
            error_log("Erreur MatchmakingController::ajouterAttente: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Récupérer le statut d'attente d'un utilisateur
     * @return void (envoie une réponse JSON)
     */
    public function getStatutAttente() {
        header('Content-Type: application/json');
        
        try {
            $idUtilisateur = isset($_GET['id_utilisateur']) ? (int)$_GET['id_utilisateur'] : 0;
            $idJeu = isset($_GET['id_jeu']) ? (int)$_GET['id_jeu'] : 0;
            
            if ($idUtilisateur === 0 || $idJeu === 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Paramètres manquants'
                ]);
                return;
            }
            
            // Vérifier les sessions actives de l'utilisateur pour ce jeu
            $sessions = $this->sessionModel->getSessionsUtilisateur($idUtilisateur);
            $sessionJeu = null;
            
            foreach ($sessions as $session) {
                if ($session['id_jeu'] == $idJeu && $session['statut'] == 'active') {
                    $sessionJeu = $session;
                    break;
                }
            }
            
            if ($sessionJeu) {
                echo json_encode([
                    'success' => true,
                    'matched' => true,
                    'session' => $sessionJeu
                ]);
            } else {
                echo json_encode([
                    'success' => true,
                    'matched' => false,
                    'message' => 'En attente d\'un match...'
                ]);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Récupérer les jeux achetés par un utilisateur (pour afficher le bouton Find a Match)
     * @return void (envoie une réponse JSON)
     */
    public function getJeuxAchetes() {
        header('Content-Type: application/json');
        
        try {
            $idUtilisateur = isset($_GET['id_utilisateur']) ? (int)$_GET['id_utilisateur'] : 0;
            
            if ($idUtilisateur === 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID utilisateur requis'
                ]);
                return;
            }
            
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance()->getConnection();
            
            $stmt = $db->prepare("
                SELECT DISTINCT j.id_jeu, j.nom, j.image_url, j.categorie, j.prix
                FROM commandes c
                INNER JOIN jeux j ON c.id_jeu = j.id_jeu
                WHERE c.id_utilisateur = :id_utilisateur
                AND c.statut IN ('confirmee', 'livree')
                ORDER BY c.date_commande DESC
            ");
            $stmt->execute([':id_utilisateur' => $idUtilisateur]);
            
            $jeux = $stmt->fetchAll();
            
            echo json_encode([
                'success' => true,
                'jeux' => $jeux
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
}

?>

