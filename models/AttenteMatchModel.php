<?php
/**
 * Modèle AttenteMatch
 * Gère les utilisateurs en attente de match pour un jeu
 * Respecte les principes de la programmation orientée objet
 */

require_once __DIR__ . '/../config/database.php';

class AttenteMatchModel {
    private $db;
    
    public function __construct() {
        $database = Database::getInstance();
        $this->db = $database->getConnection();
    }
    
    /**
     * Ajouter un utilisateur à la file d'attente
     * @param int $idUtilisateur
     * @param int $idJeu
     * @return bool|int Retourne l'ID de l'attente ou false en cas d'erreur
     */
    public function ajouterAttente($idUtilisateur, $idJeu) {
        try {
            // Vérifier si l'utilisateur est déjà en attente pour ce jeu (non matched)
            $stmt = $this->db->prepare("
                SELECT id_attente FROM AttenteMatch 
                WHERE id_utilisateur = :id_utilisateur 
                AND id_jeu = :id_jeu 
                AND matched = FALSE
            ");
            $stmt->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':id_jeu' => $idJeu
            ]);
            
            if ($stmt->fetch()) {
                // L'utilisateur est déjà en attente
                return false;
            }
            
            // Ajouter à la file d'attente
            $stmt = $this->db->prepare("
                INSERT INTO AttenteMatch (id_utilisateur, id_jeu, date_ajout, matched)
                VALUES (:id_utilisateur, :id_jeu, NOW(), FALSE)
            ");
            
            $stmt->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':id_jeu' => $idJeu
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::ajouterAttente: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer les utilisateurs en attente pour un jeu (non matched)
     * @param int $idJeu
     * @param int $limit Nombre minimum de joueurs nécessaires (par défaut 2)
     * @return array
     */
    public function getAttentesParJeu($idJeu, $limit = 2) {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.email, u.nom, u.prenom, j.nom as nom_jeu
                FROM AttenteMatch a
                INNER JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
                INNER JOIN jeux j ON a.id_jeu = j.id_jeu
                WHERE a.id_jeu = :id_jeu 
                AND a.matched = FALSE
                ORDER BY a.date_ajout ASC
                LIMIT :limit
            ");
            $stmt->bindValue(':id_jeu', $idJeu, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::getAttentesParJeu: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Marquer des attentes comme matched
     * @param array $idsAttente Tableau d'IDs d'attentes
     * @return bool
     */
    public function marquerCommeMatched($idsAttente) {
        try {
            if (empty($idsAttente)) {
                return false;
            }
            
            $placeholders = implode(',', array_fill(0, count($idsAttente), '?'));
            $stmt = $this->db->prepare("
                UPDATE AttenteMatch 
                SET matched = TRUE 
                WHERE id_attente IN ($placeholders)
            ");
            
            return $stmt->execute($idsAttente);
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::marquerCommeMatched: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Vérifier si un utilisateur a acheté un jeu
     * @param int $idUtilisateur
     * @param int $idJeu
     * @return bool
     */
    public function utilisateurAcheteJeu($idUtilisateur, $idJeu) {
        try {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) as count
                FROM commandes
                WHERE id_utilisateur = :id_utilisateur
                AND id_jeu = :id_jeu
                AND statut IN ('confirmee', 'livree')
            ");
            $stmt->execute([
                ':id_utilisateur' => $idUtilisateur,
                ':id_jeu' => $idJeu
            ]);
            
            $result = $stmt->fetch();
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::utilisateurAcheteJeu: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupérer toutes les attentes actives (pour l'admin)
     * @return array
     */
    public function getAllAttentesActives() {
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.email, u.nom, u.prenom, j.nom as nom_jeu
                FROM AttenteMatch a
                INNER JOIN utilisateurs u ON a.id_utilisateur = u.id_utilisateur
                INNER JOIN jeux j ON a.id_jeu = j.id_jeu
                WHERE a.matched = FALSE
                ORDER BY a.date_ajout DESC
            ");
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::getAllAttentesActives: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Supprimer les anciennes attentes (nettoyage)
     * @param int $joursAncien Nombre de jours (par défaut 7)
     * @return int Nombre d'attentes supprimées
     */
    public function nettoyerAnciennesAttentes($joursAncien = 7) {
        try {
            $stmt = $this->db->prepare("
                DELETE FROM AttenteMatch
                WHERE matched = TRUE
                AND date_ajout < DATE_SUB(NOW(), INTERVAL :jours DAY)
            ");
            $stmt->execute([':jours' => $joursAncien]);
            
            return $stmt->rowCount();
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::nettoyerAnciennesAttentes: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Supprimer une attente spécifique
     * @param int $idAttente
     * @return bool
     */
    public function supprimerAttente($idAttente) {
        try {
            $stmt = $this->db->prepare("DELETE FROM AttenteMatch WHERE id_attente = :id_attente");
            return $stmt->execute([':id_attente' => $idAttente]);
        } catch (PDOException $e) {
            error_log("Erreur AttenteMatchModel::supprimerAttente: " . $e->getMessage());
            return false;
        }
    }
}

?>


