<?php
/**
 * EvenementMailController - Adapté pour Green-Bite
 * Utilise mail() PHP natif (conservé du module événements-communaute)
 * Les liens QR pointent vers le dossier Green-Bite
 */
class EvenementMailController {

    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return rtrim($protocol . $host, '/');
    }

    /**
     * Envoyer un email de confirmation d'inscription avec QR code
     * @param array $user ['id', 'nom', 'prenom', 'email']
     * @param array $event données de l'événement
     * @param int $participation_id
     * @param string|null $qr_token
     */
    public function sendConfirmationEmail($user, $event, $participation_id, $qr_token = null) {
        $to = $user['email'];
        $nom = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        $baseUrl = $this->getBaseUrl();

        // Si le token n'est pas fourni, en générer un
        if ($qr_token === null) {
            $qr_token = md5($user['email'] . $participation_id . date('Y-m-d H:i:s'));
        }

        // Lien de validation du QR code - pointe vers Green-Bite
        $qrLink = $baseUrl . "/Green-Bite/View/front-office/evenements/valider-presence.php?token=" . $qr_token . "&id=" . $participation_id;

        $subject = "Confirmation d'inscription - GreenBite";
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f0fdfa; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0f766e, #14b8a6); padding: 30px; text-align: center; color: white; }
                .header h1 { margin: 0; font-size: 28px; }
                .content { padding: 30px; }
                .event-details { background: #f8fafc; padding: 15px; border-radius: 12px; margin: 20px 0; }
                .qr-code { text-align: center; margin: 20px 0; padding: 20px; background: #f8fafc; border-radius: 12px; }
                .qr-code img { width: 150px; height: 150px; }
                .btn { display: inline-block; background: #0f766e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-top: 10px; }
                .footer { background: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🌱 GreenBite</h1>
                    <p>Confirmation d\'inscription</p>
                </div>
                <div class="content">
                    <h2>Bonjour ' . htmlspecialchars($nom) . ',</h2>
                    <p>Votre inscription à l\'événement <strong>' . htmlspecialchars($event['titre']) . '</strong> a bien été prise en compte.</p>
                    <div class="event-details">
                        <h3>📅 Détails de l\'événement</h3>
                        <p><strong>📌 Titre :</strong> ' . htmlspecialchars($event['titre']) . '</p>
                        <p><strong>📅 Date :</strong> ' . date('d/m/Y', strtotime($event['date_event'])) . '</p>
                        <p><strong>📍 Lieu :</strong> ' . htmlspecialchars($event['lieu']) . '</p>
                        <p><strong>🏷️ Type :</strong> ' . $event['type'] . '</p>
                        <p><strong>👤 Organisateur :</strong> ' . htmlspecialchars($event['organisateur_nom'] ?? 'GreenBite') . '</p>
                    </div>
                    <div class="qr-code">
                        <h3>🎟️ Votre billet électronique</h3>
                        <p>Scannez ce QR code à l\'entrée de l\'événement :</p>
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=' . urlencode($qrLink) . '" alt="QR Code">
                        <p style="font-size: 12px; color: #64748b; margin-top: 10px;">Présentez ce QR code (imprimé ou sur votre téléphone) à l\'entrée.</p>
                        <a href="' . $qrLink . '" class="btn">📱 Accéder à mon billet</a>
                    </div>
                    <p><strong>🔑 Code d\'accès unique :</strong> <code>' . substr($qr_token, 0, 8) . '...' . substr($qr_token, -8) . '</code></p>
                    <p style="margin-top: 20px;">Nous vous remercions de votre confiance et avons hâte de vous accueillir !</p>
                </div>
                <div class="footer">
                    <p>GreenBite - Plateforme événementielle éco-responsable</p>
                    <p>Cet email est généré automatiquement, merci de ne pas y répondre.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: GreenBite <no-reply@greenbite.com>" . "\r\n";
        $headers .= "Reply-To: contact@greenbite.com" . "\r\n";

        return @mail($to, $subject, $message, $headers);
    }

    /**
     * Générer l'HTML du QR code
     */
    public function generateQRCodeHTML($token, $participation_id) {
        $baseUrl = $this->getBaseUrl();
        $qrContent = $baseUrl . "/Green-Bite/View/front-office/evenements/valider-presence.php?token=" . $token . "&id=" . $participation_id;
        $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrContent);
        return '<img src="' . $qrCodeUrl . '" alt="QR Code" style="width: 150px; height: 150px;">';
    }

    /**
     * Envoyer un email de récépissé après validation du QR code
     */
    public function sendReceiptEmail($user, $event, $participation) {
        $to = $user['email'];
        $nom = trim(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? ''));
        $baseUrl = $this->getBaseUrl();

        $subject = "Votre récépissé de présence - GreenBite";
        $message = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; background: #f0fdfa; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
                .header { background: linear-gradient(135deg, #0f766e, #14b8a6); padding: 30px; text-align: center; color: white; }
                .content { padding: 30px; }
                .receipt { background: #f8fafc; padding: 20px; border-radius: 12px; margin: 20px 0; text-align: center; }
                .receipt h3 { color: #0f766e; margin-bottom: 10px; }
                .receipt-number { font-size: 20px; font-weight: bold; color: #0f766e; }
                .footer { background: #f1f5f9; padding: 15px; text-align: center; font-size: 12px; color: #64748b; }
                .btn { display: inline-block; background: #0f766e; color: white; padding: 12px 24px; text-decoration: none; border-radius: 8px; margin-top: 10px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🌱 GreenBite</h1>
                    <p>Récépissé de présence</p>
                </div>
                <div class="content">
                    <h2>Bonjour ' . htmlspecialchars($nom) . ',</h2>
                    <p>Nous confirmons votre présence à l\'événement <strong>' . htmlspecialchars($event['titre']) . '</strong>.</p>
                    <div class="receipt">
                        <h3>🎟️ RÉCÉPISSÉ DE PARTICIPATION</h3>
                        <p class="receipt-number">N° ' . str_pad($participation['id'], 8, '0', STR_PAD_LEFT) . '</p>
                        <p>Délivré le ' . date('d/m/Y à H:i') . '</p>
                        <hr>
                        <p><strong>Participant :</strong> ' . htmlspecialchars($nom) . '</p>
                        <p><strong>Email :</strong> ' . htmlspecialchars($user['email']) . '</p>
                        <p><strong>Événement :</strong> ' . htmlspecialchars($event['titre']) . '</p>
                        <p><strong>Date :</strong> ' . date('d/m/Y', strtotime($event['date_event'])) . '</p>
                        <p><strong>Lieu :</strong> ' . htmlspecialchars($event['lieu']) . '</p>
                        <p><strong>Statut :</strong> ✅ Présent(e)</p>
                        ' . ($participation['date_validation'] ? '<p><strong>Validé le :</strong> ' . date('d/m/Y à H:i', strtotime($participation['date_validation'])) . '</p>' : '') . '
                    </div>
                    <p>Merci d\'avoir participé à cet événement ! À bientôt chez GreenBite.</p>
                    <div style="text-align: center;">
                        <a href="' . $baseUrl . '/Green-Bite/View/front-office/evenements/showEvenement.php?id=' . $event['id'] . '" class="btn">📋 Voir l\'événement</a>
                    </div>
                </div>
                <div class="footer">
                    <p>GreenBite - Plateforme événementielle éco-responsable</p>
                    <p>Ce document fait office de justificatif de présence.</p>
                </div>
            </div>
        </body>
        </html>
        ';

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: GreenBite <no-reply@greenbite.com>" . "\r\n";

        return @mail($to, $subject, $message, $headers);
    }
}
?>
