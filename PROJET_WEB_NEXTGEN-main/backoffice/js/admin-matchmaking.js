/**
 * JavaScript pour la page admin du matchmaking
 * Gère l'interface admin et les appels API
 * Validation JavaScript (pas HTML5)
 */

// Configuration
const API_BASE_URL = '../api/admin/matchmaking.php';

class AdminMatchmakingManager {
    constructor() {
        this.init();
    }
    
    init() {
        // Gérer les onglets
        this.initTabs();
        
        // Charger les données
        this.chargerAttentes();
        this.chargerSessions();
        
        // Événements des boutons
        const verifierBtn = document.getElementById('verifierMatchsBtn');
        if (verifierBtn) {
            verifierBtn.addEventListener('click', () => this.verifierTousLesMatchs());
        }
        
        const nettoyerBtn = document.getElementById('nettoyerAttentesBtn');
        if (nettoyerBtn) {
            nettoyerBtn.addEventListener('click', () => this.nettoyerAttentes());
        }
        
        // Rafraîchir automatiquement toutes les 30 secondes
        setInterval(() => {
            this.chargerAttentes();
            this.chargerSessions();
        }, 30000);
    }
    
    /**
     * Initialiser les onglets
     */
    initTabs() {
        const tabBtns = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabName = btn.dataset.tab;
                
                // Mettre à jour les classes actives
                tabBtns.forEach(b => b.classList.remove('active'));
                tabContents.forEach(c => c.classList.remove('active'));
                
                btn.classList.add('active');
                const targetTab = document.getElementById(`tab-${tabName}`);
                if (targetTab) {
                    targetTab.classList.add('active');
                }
            });
        });
    }
    
    /**
     * Charger les files d'attente
     */
    async chargerAttentes() {
        const container = document.getElementById('attentesContainer');
        const loading = document.getElementById('adminLoading');
        
        if (!container) return;
        
        try {
            loading.style.display = 'block';
            
            const response = await fetch(`${API_BASE_URL}?action=get_attentes`);
            const data = await response.json();
            
            if (data.success && data.attentes && data.attentes.length > 0) {
                container.innerHTML = '';
                data.attentes.forEach(jeu => {
                    const card = this.creerCarteAttenteJeu(jeu);
                    container.appendChild(card);
                });
            } else {
                container.innerHTML = '<div class="empty-state"><p>Aucune file d\'attente active pour le moment.</p></div>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des attentes:', error);
            this.afficherMessage('Erreur lors du chargement des files d\'attente.', 'error');
            container.innerHTML = '<div class="empty-state"><p style="color: #ef4444;">Erreur lors du chargement des données.</p></div>';
        } finally {
            loading.style.display = 'none';
        }
    }
    
    /**
     * Créer une carte d'attente pour un jeu
     */
    creerCarteAttenteJeu(jeu) {
        const card = document.createElement('div');
        card.className = 'attente-jeu-card';
        
        const attentesList = jeu.attentes.map(attente => `
            <li class="attente-item">
                <div class="attente-item-info">
                    <strong>${this.escapeHtml(attente.prenom)} ${this.escapeHtml(attente.nom)}</strong>
                    <div class="email">${this.escapeHtml(attente.email)}</div>
                    <div class="date">Ajouté le ${new Date(attente.date_ajout).toLocaleString('fr-FR')}</div>
                </div>
                <div class="attente-item-actions">
                    <button class="btn btn-primary btn-small" onclick="adminMatchmaking.verifierMatchJeu(${jeu.id_jeu})">
                        🔍 Vérifier Match
                    </button>
                    <button class="btn-danger-small" onclick="adminMatchmaking.supprimerAttente(${attente.id_attente})">
                        🗑️
                    </button>
                </div>
            </li>
        `).join('');
        
        card.innerHTML = `
            <h3>${this.escapeHtml(jeu.nom_jeu)}</h3>
            <div class="jeu-info">
                <strong>${jeu.attentes.length}</strong> utilisateur(s) en attente
            </div>
            <ul class="attente-list">
                ${attentesList}
            </ul>
        `;
        
        return card;
    }
    
    /**
     * Charger les sessions
     */
    async chargerSessions() {
        const tableBody = document.getElementById('sessionsTable');
        const loading = document.getElementById('adminLoading');
        
        if (!tableBody) return;
        
        try {
            loading.style.display = 'block';
            
            const response = await fetch(`${API_BASE_URL}?action=get_sessions`);
            const data = await response.json();
            
            if (data.success && data.sessions && data.sessions.length > 0) {
                tableBody.innerHTML = '';
                data.sessions.forEach(session => {
                    const row = this.creerLigneSession(session);
                    tableBody.appendChild(row);
                });
            } else {
                tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #6b7280; padding: 20px;">Aucune session active pour le moment.</td></tr>';
            }
        } catch (error) {
            console.error('Erreur lors du chargement des sessions:', error);
            this.afficherMessage('Erreur lors du chargement des sessions.', 'error');
            tableBody.innerHTML = '<tr><td colspan="7" style="text-align: center; color: #ef4444; padding: 20px;">Erreur lors du chargement des données.</td></tr>';
        } finally {
            loading.style.display = 'none';
        }
    }
    
    /**
     * Créer une ligne de session pour le tableau
     */
    creerLigneSession(session) {
        const row = document.createElement('tr');
        
        const participants = session.participants && Array.isArray(session.participants) 
            ? session.participants.length 
            : 0;
        
        const statutBadge = session.statut === 'active' 
            ? '<span style="background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Active</span>'
            : session.statut === 'terminee'
            ? '<span style="background: #e5e7eb; color: #374151; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Terminée</span>'
            : '<span style="background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 12px;">Expirée</span>';
        
        row.innerHTML = `
            <td>#${session.id_session}</td>
            <td>${this.escapeHtml(session.nom_jeu || 'N/A')}</td>
            <td>${participants} joueur(s)</td>
            <td><a href="${this.escapeHtml(session.lien_session)}" target="_blank" style="color: #2563eb; text-decoration: underline;">${this.escapeHtml(session.lien_session)}</a></td>
            <td>${new Date(session.date_creation).toLocaleString('fr-FR')}</td>
            <td>${statutBadge}</td>
            <td>
                <button class="btn-danger-small" onclick="adminMatchmaking.supprimerSession(${session.id_session})">
                    🗑️ Supprimer
                </button>
            </td>
        `;
        
        return row;
    }
    
    /**
     * Vérifier les matchs pour un jeu spécifique
     */
    async verifierMatchJeu(idJeu) {
        // Validation JavaScript
        if (!this.validerIdJeu(idJeu)) {
            return;
        }
        
        try {
            const response = await fetch(`${API_BASE_URL}?action=verifier_matchs`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_jeu: idJeu
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.afficherMessage(`Vérification effectuée. ${data.matchs_crees || 0} match(s) créé(s).`, 'success');
                // Recharger les données
                setTimeout(() => {
                    this.chargerAttentes();
                    this.chargerSessions();
                }, 1000);
            } else {
                this.afficherMessage(data.message || 'Erreur lors de la vérification.', 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la vérification des matchs:', error);
            this.afficherMessage('Erreur lors de la vérification des matchs.', 'error');
        }
    }
    
    /**
     * Vérifier tous les matchs
     */
    async verifierTousLesMatchs() {
        try {
            const btn = document.getElementById('verifierMatchsBtn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '⏳ Vérification...';
            }
            
            // Récupérer tous les jeux avec des attentes
            const response = await fetch(`${API_BASE_URL}?action=get_attentes`);
            const data = await response.json();
            
            if (data.success && data.attentes) {
                let totalMatchs = 0;
                for (const jeu of data.attentes) {
                    const matchResponse = await fetch(`${API_BASE_URL}?action=verifier_matchs`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id_jeu: jeu.id_jeu
                        })
                    });
                    const matchData = await matchResponse.json();
                    if (matchData.success && matchData.matchs_crees) {
                        totalMatchs += matchData.matchs_crees;
                    }
                }
                
                this.afficherMessage(`Vérification terminée. ${totalMatchs} match(s) créé(s) au total.`, 'success');
                
                // Recharger les données
                setTimeout(() => {
                    this.chargerAttentes();
                    this.chargerSessions();
                }, 1000);
            }
        } catch (error) {
            console.error('Erreur lors de la vérification:', error);
            this.afficherMessage('Erreur lors de la vérification.', 'error');
        } finally {
            const btn = document.getElementById('verifierMatchsBtn');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '🔄 Vérifier les Matchs';
            }
        }
    }
    
    /**
     * Supprimer une attente
     */
    async supprimerAttente(idAttente) {
        // Validation JavaScript
        if (!this.validerId(idAttente)) {
            return;
        }
        
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette attente ?')) {
            return;
        }
        
        try {
            const response = await fetch(`${API_BASE_URL}?action=supprimer_attente`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_attente: idAttente
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.afficherMessage('Attente supprimée avec succès.', 'success');
                this.chargerAttentes();
            } else {
                this.afficherMessage(data.message || 'Erreur lors de la suppression.', 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            this.afficherMessage('Erreur lors de la suppression.', 'error');
        }
    }
    
    /**
     * Supprimer une session
     */
    async supprimerSession(idSession) {
        // Validation JavaScript
        if (!this.validerId(idSession)) {
            return;
        }
        
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette session ?')) {
            return;
        }
        
        try {
            const response = await fetch(`${API_BASE_URL}?action=supprimer_session`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    id_session: idSession
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.afficherMessage('Session supprimée avec succès.', 'success');
                this.chargerSessions();
            } else {
                this.afficherMessage(data.message || 'Erreur lors de la suppression.', 'error');
            }
        } catch (error) {
            console.error('Erreur lors de la suppression:', error);
            this.afficherMessage('Erreur lors de la suppression.', 'error');
        }
    }
    
    /**
     * Nettoyer les anciennes attentes
     */
    async nettoyerAttentes() {
        if (!confirm('Êtes-vous sûr de vouloir nettoyer les anciennes attentes (matched) ?')) {
            return;
        }
        
        try {
            const response = await fetch(`${API_BASE_URL}?action=nettoyer_attentes&jours=7`);
            const data = await response.json();
            
            if (data.success) {
                this.afficherMessage(`${data.supprimees || 0} attente(s) supprimée(s).`, 'success');
                this.chargerAttentes();
            } else {
                this.afficherMessage(data.message || 'Erreur lors du nettoyage.', 'error');
            }
        } catch (error) {
            console.error('Erreur lors du nettoyage:', error);
            this.afficherMessage('Erreur lors du nettoyage.', 'error');
        }
    }
    
    /**
     * Afficher un message
     */
    afficherMessage(message, type = 'info') {
        const messageDiv = document.getElementById('adminMessage');
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
     * Valider un ID
     */
    validerId(id) {
        if (!id || isNaN(id) || id <= 0) {
            this.afficherMessage('ID invalide.', 'error');
            return false;
        }
        return true;
    }
    
    /**
     * Valider un ID de jeu
     */
    validerIdJeu(idJeu) {
        return this.validerId(idJeu);
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

// Initialiser le gestionnaire admin
let adminMatchmaking;
document.addEventListener('DOMContentLoaded', () => {
    adminMatchmaking = new AdminMatchmakingManager();
});


