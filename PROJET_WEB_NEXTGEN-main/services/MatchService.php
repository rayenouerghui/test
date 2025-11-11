<?php
/**
 * Service MatchService
 * Gère la logique métier du matchmaking
 */

require_once __DIR__ . '/../models/AttenteMatchModel.php';
require_once __DIR__ . '/../models/SessionMatchModel.php';
require_once __DIR__ . '/EmailService.php';

class MatchService {
    private $attenteModel;
    private $sessionModel;
    private $emailService;
    private $minJoueurs = 2; // Nombre minimum de joueurs pour créer un match
    
    public function __construct() {
        $this->attenteModel = new AttenteMatchModel();
        $this->sessionModel = new SessionMatchModel();
        $this->emailService = new EmailService();
    }
    
    /**
     * Vérifier les matchs possibles pour un jeu
     * @param int $idJeu
     * @return array Résultat avec le nombre de matchs créés
     */
    public function verifierMatchs($idJeu) {
        $matchsCrees = 0;
        
        try {
            // Récupérer les attentes non matchées pour ce jeu
            $attentes = $this->attenteModel->getAttentesParJeu($idJeu, 10); // Limite à 10 pour éviter les trop gros groupes
            
            if (count($attentes) < $this->minJoueurs) {
                return ['matchs_crees' => 0, 'message' => 'Pas assez de joueurs en attente'];
            }
            
            // Grouper les joueurs par lots de 2 (ou plus si nécessaire)
            $groupes = array_chunk($attentes, $this->minJoueurs);
            
            foreach ($groupes as $groupe) {
                if (count($groupe) >= $this->minJoueurs) {
                    // Créer une session de match
                    $participants = array_map(function($attente) {
                        return (int)$attente['id_utilisateur'];
                    }, $groupe);
                    
                    // Générer un lien de session
                    $lienSession = $this->sessionModel->genererLienSession();
                    
                    // Créer la session
                    $idSession = $this->sessionModel->creerSession($idJeu, $participants, $lienSession);
                    
                    if ($idSession) {
                        // Marquer les attentes comme matched
                        $idsAttente = array_map(function($attente) {
                            return (int)$attente['id_attente'];
                        }, $groupe);
                        
                        $this->attenteModel->marquerCommeMatched($idsAttente);
                        
                        // Envoyer des emails aux participants
                        $this->envoyerEmailsMatch($idSession, $participants, $lienSession, $idJeu);
                        
                        $matchsCrees++;
                    }
                }
            }
            
            return ['matchs_crees' => $matchsCrees];
            
        } catch (Exception $e) {
            error_log("Erreur MatchService::verifierMatchs: " . $e->getMessage());
            return ['matchs_crees' => 0, 'erreur' => $e->getMessage()];
        }
    }
    
    /**
     * Envoyer des emails aux participants d'un match
     * @param int $idSession
     * @param array $participants IDs des utilisateurs
     * @param string $lienSession
     * @param int $idJeu
     * @return void
     */
    private function envoyerEmailsMatch($idSession, $participants, $lienSession, $idJeu) {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance()->getConnection();
            
            // Récupérer les informations du jeu
            $stmt = $db->prepare("SELECT nom FROM jeux WHERE id_jeu = :id_jeu");
            $stmt->execute([':id_jeu' => $idJeu]);
            $jeu = $stmt->fetch();
            $nomJeu = $jeu ? $jeu['nom'] : 'le jeu';
            
            // Récupérer les emails des participants
            $placeholders = implode(',', array_fill(0, count($participants), '?'));
            $stmt = $db->prepare("
                SELECT id_utilisateur, email, nom, prenom 
                FROM utilisateurs 
                WHERE id_utilisateur IN ($placeholders)
            ");
            $stmt->execute($participants);
            $utilisateurs = $stmt->fetchAll();
            
            // Envoyer un email à chaque participant
            foreach ($utilisateurs as $utilisateur) {
                $this->emailService->envoyerEmailMatch(
                    $utilisateur['email'],
                    $utilisateur['prenom'] . ' ' . $utilisateur['nom'],
                    $nomJeu,
                    $lienSession,
                    $idSession
                );
            }
            
        } catch (Exception $e) {
            error_log("Erreur MatchService::envoyerEmailsMatch: " . $e->getMessage());
        }
    }
    
    /**
     * Vérifier tous les jeux pour des matchs possibles
     * (Utile pour un cron job)
     * @return array
     */
    public function verifierTousLesMatchs() {
        try {
            require_once __DIR__ . '/../config/database.php';
            $db = Database::getInstance()->getConnection();
            
            // Récupérer tous les jeux qui ont des attentes actives
            $stmt = $db->prepare("
                SELECT DISTINCT id_jeu 
                FROM AttenteMatch 
                WHERE matched = FALSE
            ");
            $stmt->execute();
            $jeux = $stmt->fetchAll();
            
            $totalMatchs = 0;
            foreach ($jeux as $jeu) {
                $resultat = $this->verifierMatchs($jeu['id_jeu']);
                $totalMatchs += $resultat['matchs_crees'];
            }
            
            return ['matchs_crees' => $totalMatchs];
            
        } catch (Exception $e) {
            error_log("Erreur MatchService::verifierTousLesMatchs: " . $e->getMessage());
            return ['matchs_crees' => 0, 'erreur' => $e->getMessage()];
        }
    }
}

?>


