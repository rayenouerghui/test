<?php
/**
 * Script Cron pour vérifier les matchs automatiquement
 * À exécuter périodiquement (ex: toutes les 5 minutes)
 * Usage: php cron/check_matches.php
 */

require_once __DIR__ . '/../services/MatchService.php';

// Désactiver la limite de temps d'exécution pour les scripts cron
set_time_limit(0);

try {
    $matchService = new MatchService();
    $resultat = $matchService->verifierTousLesMatchs();
    
    echo "[" . date('Y-m-d H:i:s') . "] Vérification des matchs terminée.\n";
    echo "Matchs créés: " . $resultat['matchs_crees'] . "\n";
    
    if (isset($resultat['erreur'])) {
        echo "Erreur: " . $resultat['erreur'] . "\n";
    }
    
} catch (Exception $e) {
    echo "[" . date('Y-m-d H:i:s') . "] Erreur: " . $e->getMessage() . "\n";
    exit(1);
}

?>


