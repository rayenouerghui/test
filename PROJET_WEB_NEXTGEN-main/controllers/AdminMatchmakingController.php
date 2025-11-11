<?php
/**
 * Contrôleur AdminMatchmakingController
 * Gère les requêtes admin pour le matchmaking
 * Respecte le pattern MVC
 */

require_once __DIR__ . '/../models/AttenteMatchModel.php';
require_once __DIR__ . '/../models/SessionMatchModel.php';
require_once __DIR__ . '/../services/MatchService.php';

class AdminMatchmakingController {
    private $attenteModel;
    private $sessionModel;
    private $matchService;
    
    public function __construct() {
        $this->attenteModel = new AttenteMatchModel();
        $this->sessionModel = new SessionMatchModel();
        $this->matchService = new MatchService();
    }
    
    /**
     * Récupérer toutes les attentes actives
     * @return void (envoie une réponse JSON)
     */
    public function getAttentes() {
        header('Content-Type: application/json');
        
        try {
            $attentes = $this->attenteModel->getAllAttentesActives();
            
            // Grouper par jeu
            $attentesParJeu = [];
            foreach ($attentes as $attente) {
                $idJeu = $attente['id_jeu'];
                if (!isset($attentesParJeu[$idJeu])) {
                    $attentesParJeu[$idJeu] = [
                        'id_jeu' => $idJeu,
                        'nom_jeu' => $attente['nom_jeu'],
                        'attentes' => []
                    ];
                }
                $attentesParJeu[$idJeu]['attentes'][] = $attente;
            }
            
            echo json_encode([
                'success' => true,
                'attentes' => array_values($attentesParJeu)
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Récupérer toutes les sessions
     * @return void (envoie une réponse JSON)
     */
    public function getSessions() {
        header('Content-Type: application/json');
        
        try {
            $statut = isset($_GET['statut']) ? $_GET['statut'] : null;
            $sessions = $this->sessionModel->getAllSessions($statut);
            
            echo json_encode([
                'success' => true,
                'sessions' => $sessions
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Forcer la vérification de matchs pour un jeu
     * @return void (envoie une réponse JSON)
     */
    public function verifierMatchs() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $idJeu = isset($data['id_jeu']) ? (int)$data['id_jeu'] : 0;
            
            if ($idJeu === 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID jeu requis'
                ]);
                return;
            }
            
            $resultat = $this->matchService->verifierMatchs($idJeu);
            
            echo json_encode([
                'success' => true,
                'message' => 'Vérification effectuée',
                'matchs_crees' => $resultat['matchs_crees']
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Erreur serveur: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Supprimer une attente
     * @return void (envoie une réponse JSON)
     */
    public function supprimerAttente() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $idAttente = isset($data['id_attente']) ? (int)$data['id_attente'] : 0;
            
            if ($idAttente === 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID attente requis'
                ]);
                return;
            }
            
            $success = $this->attenteModel->supprimerAttente($idAttente);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Attente supprimée'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression'
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
     * Supprimer une session
     * @return void (envoie une réponse JSON)
     */
    public function supprimerSession() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $idSession = isset($data['id_session']) ? (int)$data['id_session'] : 0;
            
            if ($idSession === 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID session requis'
                ]);
                return;
            }
            
            $success = $this->sessionModel->supprimerSession($idSession);
            
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Session supprimée'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => 'Erreur lors de la suppression'
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
     * Nettoyer les anciennes attentes
     * @return void (envoie une réponse JSON)
     */
    public function nettoyerAttentes() {
        header('Content-Type: application/json');
        
        try {
            $jours = isset($_GET['jours']) ? (int)$_GET['jours'] : 7;
            $supprimees = $this->attenteModel->nettoyerAnciennesAttentes($jours);
            
            echo json_encode([
                'success' => true,
                'message' => "$supprimees attentes supprimées",
                'supprimees' => $supprimees
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


