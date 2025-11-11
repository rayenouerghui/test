/**
 * JavaScript pour le module Matchmaking
 * Gère l'interface utilisateur et les appels API
 * Validation JavaScript (pas HTML5)
 */

// Configuration
const API_BASE_URL = '../api/matchmaking.php';
const CURRENT_USER_ID = 1; // TODO: Récupérer depuis la session PHP

class MatchmakingManager {
    constructor() {
        this.userId = CURRENT_USER_ID;
        this.init();
    }
    
    init() {
        // Charger les jeux achetés et les sessions au chargement de la page
        if (document.getElementById('matchmaking')) {
            this.chargerJeuxAchetes();
            this.chargerMesSessions();
            
            // Vérifier le statut périodiquement (toutes les 30 secondes)
            setInterval(() => {
                this.verifierStatutMatchs();
            }, 30000);
        }
    }
    
    /**
     * Charger les jeux achetés par l'utilisateur
     */
    async chargerJeuxAchetes() {
        const container = document.getElementById('jeuxAchetesList');
        const loading = document.getElementById('matchmakingLoading');
        
        if (!container) return;
        
        try {
            loading.style.display = 'block';
            container.innerHTML = '';
            
            const response = await fetch(`${API_BASE_URL}?action=jeux_achetes&id_utilisateur=${this.userId}`);
            const data = await response.json();
            
            if (data.success && data.jeux && data.jeux.length > 0) {
                data.jeux.forEach(jeu => {
                    const card = this.creerCarteJeu(jeu);
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = '<p style="color: #6b7280; text-align: center; padding: 20px;">Aucun jeu acheté. Achetez un jeu pour pouvoir trouver un match !</p>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des jeux:', error);
            this.afficherMessage('Erreur lors du chargement des jeux.', 'error');
        } finally {
            loading.style.display = 'none';
        }
    }
    
    /**
     * Créer une carte de jeu avec le bouton Find a Match
     */
    creerCarteJeu(jeu) {
        const card = document.createElement('div');
        card.className = 'game-match-card';
        card.dataset.jeuId = jeu.id_jeu;
        
        card.innerHTML = `
            <img src="${jeu.image_url || 'https://via.placeholder.com/300x200?text=' + encodeURIComponent(jeu.nom)}" alt="${jeu.nom}">
            <h4>${this.escapeHtml(jeu.nom)}</h4>
            <p class="game-category">${this.escapeHtml(jeu.categorie || 'Non catégorisé')}</p>
            <div class="match-status waiting" id="status-${jeu.id_jeu}" style="display: none;">
                ⏳ En attente d'un match...
            </div>
            <button class="btn btn-primary find-match-btn" data-jeu-id="${jeu.id_jeu}" data-jeu-nom="${this.escapeHtml(jeu.nom)}">
                🎮 Find a Match
            </button>
        `;
        
        // Ajouter l'événement click au bouton
        const btn = card.querySelector('.find-match-btn');
        btn.addEventListener('click', () => this.trouverMatch(jeu.id_jeu, jeu.nom));
        
        return card;
    }
    
    /**
     * Trouver un match pour un jeu
     */
    async trouverMatch(idJeu, nomJeu) {
        // Validation JavaScript (pas HTML5)
        if (!this.validerDonneesMatch(idJeu)) {
            return;
        }
        
        const btn = document.querySelector(`.find-match-btn[data-jeu-id="${idJeu}"]`);
        const statusDiv = document.getElementById(`status-${idJeu}`);
        
        try {
            // Désactiver le bouton et afficher le loading
            btn.disabled = true;
            btn.innerHTML = '⏳ Recherche...';
            
            const response = await fetch(`${API_BASE_URL}?action=ajouter_attente`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_utilisateur: this.userId,
                    id_jeu: idJeu
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.afficherMessage(`Vous avez été ajouté à la file d'attente pour "${nomJeu}". Nous vous notifierons quand un match sera trouvé !`, 'success');
                
                // Afficher le statut d'attente
                if (statusDiv) {
                    statusDiv.style.display = 'block';
                }
                
                btn.innerHTML = '✅ En attente...';
                
                // Vérifier le statut après un délai
                setTimeout(() => {
                    this.verifierStatutMatch(idJeu);
                }, 2000);
            } else {
                this.afficherMessage(data.message || 'Erreur lors de l\'ajout à la file d\'attente.', 'error');
                btn.disabled = false;
                btn.innerHTML = '🎮 Find a Match';
            }
        } catch (error) {
            console.error('Erreur lors de la recherche de match:', error);
            this.afficherMessage('Erreur lors de la recherche de match. Veuillez réessayer.', 'error');
            btn.disabled = false;
            btn.innerHTML = '🎮 Find a Match';
        }
    }
    
    /**
     * Vérifier le statut d'un match pour un jeu
     */
    async verifierStatutMatch(idJeu) {
        try {
            const response = await fetch(`${API_BASE_URL}?action=statut_attente&id_utilisateur=${this.userId}&id_jeu=${idJeu}`);
            const data = await response.json();
            
            if (data.success && data.matched && data.session) {
                // Match trouvé !
                this.afficherMessage(`🎉 Match trouvé pour ce jeu ! Vérifiez vos emails ou vos sessions actives.`, 'success');
                
                const statusDiv = document.getElementById(`status-${idJeu}`);
                if (statusDiv) {
                    statusDiv.className = 'match-status matched';
                    statusDiv.innerHTML = '✅ Match trouvé !';
                }
                
                const btn = document.querySelector(`.find-match-btn[data-jeu-id="${idJeu}"]`);
                if (btn) {
                    btn.disabled = true;
                    btn.innerHTML = '✅ Match trouvé';
                }
                
                // Recharger les sessions
                this.chargerMesSessions();
            }
        } catch (error) {
            console.error('Erreur lors de la vérification du statut:', error);
        }
    }
    
    /**
     * Vérifier le statut de tous les matchs
     */
    async verifierStatutMatchs() {
        const cards = document.querySelectorAll('.game-match-card');
        cards.forEach(card => {
            const idJeu = card.dataset.jeuId;
            if (idJeu) {
                this.verifierStatutMatch(idJeu);
            }
        });
    }
    
    /**
     * Charger les sessions actives de l'utilisateur
     */
    async chargerMesSessions() {
        const container = document.getElementById('mesSessionsList');
        if (!container) return;
        
        try {
            // Pour l'instant, on charge depuis les jeux achetés et on vérifie les statuts
            // Dans une vraie implémentation, on aurait un endpoint dédié
            const response = await fetch(`${API_BASE_URL}?action=jeux_achetes&id_utilisateur=${this.userId}`);
            const data = await response.json();
            
            if (data.success && data.jeux) {
                // Vérifier chaque jeu pour voir s'il y a une session active
                const sessions = [];
                for (const jeu of data.jeux) {
                    const statutResponse = await fetch(`${API_BASE_URL}?action=statut_attente&id_utilisateur=${this.userId}&id_jeu=${jeu.id_jeu}`);
                    const statutData = await statutResponse.json();
                    
                    if (statutData.success && statutData.matched && statutData.session) {
                        sessions.push({
                            jeu: jeu,
                            session: statutData.session
                        });
                    }
                }
                
                if (sessions.length > 0) {
                    container.innerHTML = '';
                    sessions.forEach(({jeu, session}) => {
                        const sessionCard = this.creerCarteSession(jeu, session);
                        container.appendChild(sessionCard);
                    });
                } else {
                    container.innerHTML = '<p style="color: #6b7280; text-align: center; padding: 20px;">Aucune session active pour le moment.</p>';
                }
            }
        } catch (error) {
            console.error('Erreur lors du chargement des sessions:', error);
            container.innerHTML = '<p style="color: #ef4444; text-align: center; padding: 20px;">Erreur lors du chargement des sessions.</p>';
        }
    }
    
    /**
     * Créer une carte de session
     */
    creerCarteSession(jeu, session) {
        const card = document.createElement('div');
        card.className = 'session-card';
        
        card.innerHTML = `
            <h4>${this.escapeHtml(jeu.nom)}</h4>
            <p><strong>Date de création:</strong> ${new Date(session.date_creation).toLocaleString('fr-FR')}</p>
            <p><strong>Participants:</strong> ${session.participants ? session.participants.length : 0} joueur(s)</p>
            <a href="${session.lien_session}" target="_blank" class="session-link">🔗 Rejoindre la Session</a>
        `;
        
        return card;
    }
    
    /**
     * Afficher un message
     */
    afficherMessage(message, type = 'info') {
        const messageDiv = document.getElementById('matchmakingMessage');
        if (!messageDiv) return;
        
        messageDiv.style.display = 'block';
        messageDiv.textContent = message;
        messageDiv.style.backgroundColor = type === 'success' ? '#d1fae5' : type === 'error' ? '#fee2e2' : '#dbeafe';
        messageDiv.style.color = type === 'success' ? '#065f46' : type === 'error' ? '#991b1b' : '#1e40af';
        messageDiv.style.border = `1px solid ${type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6'}`;
        
        // Masquer le message après 5 secondes
        setTimeout(() => {
            messageDiv.style.display = 'none';
        }, 5000);
    }
    
    /**
     * Valider les données avant d'envoyer une requête
     * Validation JavaScript (pas HTML5)
     */
    validerDonneesMatch(idJeu) {
        const errors = [];
        
        // Valider l'ID du jeu
        if (!idJeu || isNaN(idJeu) || idJeu <= 0) {
            errors.push('ID de jeu invalide');
        }
        
        // Valider l'ID utilisateur
        if (!this.userId || isNaN(this.userId) || this.userId <= 0) {
            errors.push('ID utilisateur invalide');
        }
        
        if (errors.length > 0) {
            this.afficherMessage('Erreur de validation: ' + errors.join(', '), 'error');
            return false;
        }
        
        return true;
    }
    
    /**
     * Échapper le HTML pour éviter les XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
}

// Initialiser le gestionnaire de matchmaking
document.addEventListener('DOMContentLoaded', () => {
    new MatchmakingManager();
});

// Gérer les onglets du compte
document.addEventListener('DOMContentLoaded', () => {
    const menuLinks = document.querySelectorAll('.account-menu a');
    const tabs = document.querySelectorAll('.account-tab');
    
    menuLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            
            // Mettre à jour les classes actives
            menuLinks.forEach(l => l.classList.remove('active'));
            tabs.forEach(t => t.classList.remove('active'));
            
            link.classList.add('active');
            const targetTab = document.getElementById(targetId);
            if (targetTab) {
                targetTab.classList.add('active');
                
                // Recharger les données si on ouvre l'onglet matchmaking
                if (targetId === 'matchmaking') {
                    const matchmakingManager = new MatchmakingManager();
                }
            }
        });
    });
});

