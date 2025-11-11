<?php
/**
 * Service EmailService
 * Gère l'envoi d'emails pour les matchs
 */

class EmailService {
    private $fromEmail = 'noreply@nextgen.com';
    private $fromName = 'NextGen Matchmaking';
    
    /**
     * Envoyer un email de match trouvé
     * @param string $toEmail
     * @param string $toName
     * @param string $nomJeu
     * @param string $lienSession
     * @param int $idSession
     * @return bool
     */
    public function envoyerEmailMatch($toEmail, $toName, $nomJeu, $lienSession, $idSession) {
        try {
            $subject = "🎮 Match trouvé pour $nomJeu - NextGen";
            
            $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background: linear-gradient(135deg, #2563eb 0%, #ea580c 100%); color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                    .content { background: #f9fafb; padding: 30px; border-radius: 0 0 8px 8px; }
                    .button { display: inline-block; padding: 12px 24px; background: #2563eb; color: white; text-decoration: none; border-radius: 6px; margin: 20px 0; }
                    .footer { text-align: center; margin-top: 20px; color: #6b7280; font-size: 12px; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h1>🎮 Match Trouvé !</h1>
                    </div>
                    <div class='content'>
                        <p>Bonjour $toName,</p>
                        <p>Excellente nouvelle ! Un match a été trouvé pour le jeu <strong>$nomJeu</strong>.</p>
                        <p>Vous pouvez maintenant rejoindre la session de jeu avec d'autres joueurs :</p>
                        <p style='text-align: center;'>
                            <a href='$lienSession' class='button'>Rejoindre la Session</a>
                        </p>
                        <p>Ou copiez ce lien dans votre navigateur :</p>
                        <p style='word-break: break-all; color: #2563eb;'>$lienSession</p>
                        <p>Amusez-vous bien ! 🎉</p>
                        <p>Cordialement,<br>L'équipe NextGen</p>
                    </div>
                    <div class='footer'>
                        <p>Cet email a été envoyé automatiquement. Merci de ne pas y répondre.</p>
                    </div>
                </div>
            </body>
            </html>
            ";
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: $this->fromName <$this->fromEmail>\r\n";
            $headers .= "Reply-To: $this->fromEmail\r\n";
            
            // En production, utiliser PHPMailer ou un service d'email
            // Pour le développement, on peut utiliser mail() ou logger
            $success = mail($toEmail, $subject, $message, $headers);
            
            // Logger l'envoi (pour le développement)
            if (!$success) {
                error_log("Erreur envoi email à $toEmail pour session $idSession");
            } else {
                error_log("Email envoyé à $toEmail pour session $idSession");
            }
            
            return $success;
            
        } catch (Exception $e) {
            error_log("Erreur EmailService::envoyerEmailMatch: " . $e->getMessage());
            return false;
        }
    }
}

?>


