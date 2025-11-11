<?php
/**
 * Modèle SessionMatch
 * Gère les sessions de match créées quand des joueurs sont matchés
 * Respecte les principes de la programmation orientée objet
 */

require_once __DIR__ . '/../config/database.php';

class SessionMatchModel {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Créer une nouvelle session de match
     * @param int $idJeu
     * @param array $participants Tableau d'IDs utilisateurs
     * @param string $lienSession Lien généré (Discord, chat, etc.)
     * @return bool|int Retourne l'ID de la session ou false en cas d'erreur
     */
    public function creerSession($idJeu, $participants, $lienSession) {
        try {
            $participantsJson = json_encode($participants);
            
            $stmt = $this->db->prepare("
                INSERT INTO SessionMatch (id_jeu, lien_session, date_creation, participants, statut)
                VALUES (:id_jeu, :lien_session, NOW(), :participants, 'active')
            ");
            
            $stmt->execute([
                ':id_jeu' => $idJeu,
                ':lien_session' => $lienSession,
                ':participants' => $participantsJson
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur SessionMatchModel::creerSession: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer une session par son ID
     * @param int $idSession
     * @return array|false
     */
    public function getSession($idSession) {
        try {
            $stmt = $this->db->prepare("
                SELECT s.*, j.nom as nom_jeu
                FROM SessionMatch s
                INNER JOIN jeux j ON s.id_jeu = j.id_jeu
                WHERE s.id_session = :id_session
            ");
            $stmt->execute([':id_session' => $idSession]);
            
            $session = $stmt->fetch();
            if ($session) {
                $session['participants'] = json_decode($session['participants'], true);
            }
            
            return $session;
        } catch (PDOException $e) {
            error_log("Erreur SessionMatchModel::getSession: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les sessions d'un utilisateur
     * @param int $idUtilisateur
     * @return array
     */
    public function getSessionsUtilisateur($idUtilisateur) {
        try {
            // Récupérer toutes les sessions actives et filtrer en PHP
            // (JSON_CONTAINS peut ne pas être disponible selon la version MySQL)
            $stmt = $this->db->prepare("
                SELECT s.*, j.nom as nom_jeu
                FROM SessionMatch s
                INNER JOIN jeux j ON s.id_jeu = j.id_jeu
                WHERE s.statut = 'active'
                ORDER BY s.date_creation DESC
            ");
            $stmt->execute();
            
            $sessions = $stmt->fetchAll();
            $userSessions = [];
            
            foreach ($sessions as $session) {
                $participants = json_decode($session['participants'], true);
                if (is_array($participants) && in_array($idUtilisateur, $participants)) {
                    $session['participants'] = $participants;
                    $userSessions[] = $session;
                }
            }
            
            return $userSessions;
        } catch (PDOException $e) {
            error_log("Erreur SessionMatchModel::getSessionsUtilisateur: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Récupérer toutes les sessions (pour l'admin)
     * @param string $statut Filtrer par statut (optionnel)
     * @return array
     */
    public function getAllSessions($statut = null) {
        try {
            $sql = "
                SELECT s.*, j.nom as nom_jeu
                FROM SessionMatch s
                INNER JOIN jeux j ON s.id_jeu = j.id_jeu
            ";
            
            if ($statut !== null) {
                $sql .= " WHERE s.statut = :statut";
            }
            
            $sql .= " ORDER BY s.date_creation DESC";
            
            $stmt = $this->db->prepare($sql);
            
            if ($statut !== null) {
                $stmt->execute([':statut' => $statut]);
            } else {
                $stmt->execute();
            }
            
            $sessions = $stmt->fetchAll();
            foreach ($sessions as &$session) {
                $session['participants'] = json_decode($session['participants'], true);
            }
            
            return $sessions;
        } catch (PDOException $e) {
            error_log("Erreur SessionMatchModel::getAllSessions: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Mettre à jour le statut d'une session
     * @param int $idSession
     * @param string $statut ('active', 'terminee', 'expiree')
     * @return bool
     */
    public function updateStatut($idSession, $statut) {
        try {
            $stmt = $this->db->prepare("
                UPDATE SessionMatch 
                SET statut = :statut 
                WHERE id_session = :id_session
            ");
            
            return $stmt->execute([
                ':id_session' => $idSession,
                ':statut' => $statut
            ]);
        } catch (PDOException $e) {
            error_log("Erreur SessionMatchModel::updateStatut: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une session
     * @param int $idSession
     * @return bool
     */
    public function supprimerSession($idSession) {
        try {
            $stmt = $this->db->prepare("DELETE FROM SessionMatch WHERE id_session = :id_session");
            return $stmt->execute([':id_session' => $idSession]);
        } catch (PDOException $e) {
            error_log("Erreur SessionMatchModel::supprimerSession: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Générer un lien de session unique
     * @return string
     */
    public function genererLienSession() {
        // Générer un UUID ou un lien unique
        // Pour l'exemple, on génère un lien Discord-like ou un UUID
        $uuid = sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
        
        // Optionnel: générer un lien Discord ou autre service
        // Pour l'exemple, on retourne un lien générique
        return "https://nextgen.match/session/" . $uuid;
    }
}

?>

