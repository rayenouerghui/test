/**
 * JavaScript pour la page compte utilisateur
 * Gère la navigation entre les onglets et la validation des formulaires
 * Validation JavaScript (pas HTML5)
 */

document.addEventListener('DOMContentLoaded', () => {
    // Gestion des onglets
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
            }
        });
    });
    
    // Validation du formulaire de profil
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const firstName = document.getElementById('profileFirstName').value.trim();
            const lastName = document.getElementById('profileLastName').value.trim();
            const email = document.getElementById('profileEmail').value.trim();
            
            // Validation JavaScript (pas HTML5)
            const errors = [];
            
            if (!firstName || firstName.length < 2) {
                errors.push('Le prénom doit contenir au moins 2 caractères');
            }
            
            if (!lastName || lastName.length < 2) {
                errors.push('Le nom doit contenir au moins 2 caractères');
            }
            
            if (!email || !validerEmail(email)) {
                errors.push('Email invalide');
            }
            
            if (errors.length > 0) {
                alert('Erreurs de validation:\n' + errors.join('\n'));
                return;
            }
            
            // Soumettre le formulaire (simulation)
            alert('Profil mis à jour avec succès !');
        });
    }
    
    // Validation du formulaire de changement de mot de passe
    const settingsForm = document.getElementById('settingsForm');
    if (settingsForm) {
        settingsForm.addEventListener('submit', (e) => {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            
            // Validation JavaScript (pas HTML5)
            const errors = [];
            
            if (!currentPassword || currentPassword.length < 6) {
                errors.push('Le mot de passe actuel est requis (minimum 6 caractères)');
            }
            
            if (!newPassword || newPassword.length < 8) {
                errors.push('Le nouveau mot de passe doit contenir au moins 8 caractères');
            }
            
            if (newPassword !== confirmPassword) {
                errors.push('Les mots de passe ne correspondent pas');
            }
            
            if (errors.length > 0) {
                alert('Erreurs de validation:\n' + errors.join('\n'));
                return;
            }
            
            // Soumettre le formulaire (simulation)
            alert('Mot de passe mis à jour avec succès !');
        });
    }
});

/**
 * Valider un email avec une expression régulière
 */
function validerEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}


