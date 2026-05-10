<?php
require_once __DIR__ . "/../config/database.php";

class QRCodeController {
    private $db;
    
    public function __construct() {
        $this->db = config::getConnexion();
    }
    
    /**
     * Dispatcher pour les actions (appelé depuis l'URL)
     */
    public function dispatch() {
        $action = isset($_GET['action']) ? $_GET['action'] : '';
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        
        switch ($action) {
            case 'show':
                $this->showQRCode($id);
                break;
            case 'download':
                $this->downloadQRCode($id);
                break;
            case 'generate':
                $this->generateQRCodeForParticipant($id);
                break;
            default:
                $this->generateDefaultQR();
        }
    }
    
    /**
     * Générer un code QR pour un participant
     * @param int $participant_id
     * @param string $nom
     * @param string $email
     * @return string Chemin du fichier QR généré
     */
    public function generateQRCode($participant_id, $nom, $email) {
        // Créer le dossier qr_codes s'il n'existe pas
        $qrDir = __DIR__ . "/../qr_codes/";
        if (!is_dir($qrDir)) {
            mkdir($qrDir, 0777, true);
        }
        
        // Contenu du QR code (lien de validation)
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        $baseUrl = rtrim($baseUrl, '/');
        $qrContent = $baseUrl . "/projetwebnova/view/front/valider-presence.php?id=" . $participant_id . "&code=" . md5($email);
        
        // Générer une image PNG simple (solution sans librairie externe)
        $filename = $qrDir . "participant_" . $participant_id . ".png";
        $this->createSimpleQRImage($filename, $qrContent, $nom);
        
        // Mettre à jour le code dans la base de données
        $uniqueCode = md5($email . $participant_id . date('Y-m-d H:i:s'));
        $sql = "UPDATE participant SET code_qr = :code_qr WHERE id = :id";
        $query = $this->db->prepare($sql);
        $query->execute([
            'code_qr' => $uniqueCode,
            'id' => $participant_id
        ]);
        
        return $filename;
    }
    
    /**
     * Générer un QR code pour un participant existant (depuis l'admin)
     */
    public function generateQRCodeForParticipant($participant_id) {
        try {
            $participant_id = filter_var($participant_id, FILTER_VALIDATE_INT);
            if (!$participant_id || $participant_id <= 0) {
                throw new Exception("ID participant invalide");
            }
            
            // Récupérer les infos du participant
            $sql = "SELECT p.*, e.titre as event_titre 
                    FROM participant p 
                    LEFT JOIN evenement e ON p.evenement_id = e.id 
                    WHERE p.id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $participant_id]);
            $participant = $query->fetch();
            
            if (!$participant) {
                throw new Exception("Participant non trouvé");
            }
            
            $filename = $this->generateQRCode($participant_id, $participant['nom'], $participant['email']);
            
            $_SESSION['message'] = "QR Code généré avec succès";
            header('Location: editParticipant.php?id=' . $participant_id);
            exit();
            
        } catch (Exception $e) {
            $_SESSION['message'] = "Erreur: " . $e->getMessage();
            header('Location: participants.php');
            exit();
        }
    }
    
    /**
     * Créer une image simple pour le QR code
     */
    private function createSimpleQRImage($filename, $content, $nom) {
        // Créer une image de 300x300
        $size = 300;
        $image = imagecreatetruecolor($size, $size);
        
        // Couleurs
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $green = imagecolorallocate($image, 15, 118, 110);
        $lightGreen = imagecolorallocate($image, 204, 251, 241);
        $gray = imagecolorallocate($image, 100, 116, 139);
        
        // Fond blanc
        imagefill($image, 0, 0, $white);
        
        // Dessiner un motif de QR code stylisé
        $cellSize = 12;
        $startX = 30;
        $startY = 30;
        
        // Générer une matrice pseudo-aléatoire basée sur le contenu
        $hash = md5($content);
        $matrix = [];
        for ($i = 0; $i < 20; $i++) {
            for ($j = 0; $j < 20; $j++) {
                $char = $hash[($i * 20 + $j) % 32];
                $matrix[$i][$j] = (ord($char) % 2 == 0);
            }
        }
        
        // Dessiner le motif central
        for ($i = 0; $i < 20; $i++) {
            for ($j = 0; $j < 20; $j++) {
                $x = $startX + ($i * $cellSize);
                $y = $startY + ($j * $cellSize);
                $color = $matrix[$i][$j] ? $green : $lightGreen;
                imagefilledrectangle($image, $x, $y, $x + $cellSize - 1, $y + $cellSize - 1, $color);
            }
        }
        
        // Dessiner les marqueurs de coin (comme un vrai QR code)
        $this->drawCornerMarker($image, $startX - 5, $startY - 5, $green, $white);
        $this->drawCornerMarker($image, $startX + 20 * $cellSize - 35, $startY - 5, $green, $white);
        $this->drawCornerMarker($image, $startX - 5, $startY + 20 * $cellSize - 35, $green, $white);
        
        // Ajouter le texte du nom
        $fontSize = 5;
        $textX = ($size - imagefontwidth($fontSize) * strlen($nom)) / 2;
        imagestring($image, $fontSize, $textX, $size - 30, $nom, $gray);
        
        // Sauvegarder l'image
        imagepng($image, $filename);
        imagedestroy($image);
    }
    
    /**
     * Dessiner un marqueur de coin pour le QR code
     */
    private function drawCornerMarker($image, $x, $y, $color, $bgColor) {
        // Carré extérieur
        for ($i = 0; $i < 21; $i++) {
            imagesetpixel($image, $x + $i, $y, $color);
            imagesetpixel($image, $x + $i, $y + 20, $color);
            imagesetpixel($image, $x, $y + $i, $color);
            imagesetpixel($image, $x + 20, $y + $i, $color);
        }
        // Carré intérieur
        imagefilledrectangle($image, $x + 6, $y + 6, $x + 14, $y + 14, $color);
    }
    
    /**
     * Afficher le QR code d'un participant
     */
    public function showQRCode($participant_id) {
        $filename = __DIR__ . "/../qr_codes/participant_" . $participant_id . ".png";
        
        if (file_exists($filename)) {
            header('Content-Type: image/png');
            header('Cache-Control: public, max-age=3600');
            readfile($filename);
            exit();
        } else {
            // Vérifier si le participant existe et générer le QR
            $sql = "SELECT nom, email FROM participant WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $participant_id]);
            $participant = $query->fetch();
            
            if ($participant) {
                $this->generateQRCode($participant_id, $participant['nom'], $participant['email']);
                $this->showQRCode($participant_id);
            } else {
                $this->generateDefaultQR();
            }
        }
    }
    
    /**
     * Télécharger le QR code
     */
    public function downloadQRCode($participant_id) {
        $filename = __DIR__ . "/../qr_codes/participant_" . $participant_id . ".png";
        
        if (file_exists($filename)) {
            header('Content-Type: image/png');
            header('Content-Disposition: attachment; filename="qr_code_participant_' . $participant_id . '.png"');
            readfile($filename);
            exit();
        } else {
            // Générer le QR s'il n'existe pas
            $sql = "SELECT nom, email FROM participant WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->execute(['id' => $participant_id]);
            $participant = $query->fetch();
            
            if ($participant) {
                $this->generateQRCode($participant_id, $participant['nom'], $participant['email']);
                $this->downloadQRCode($participant_id);
            } else {
                $this->generateDefaultQR();
            }
        }
    }
    
    /**
     * Générer un QR code par défaut
     */
    private function generateDefaultQR() {
        $size = 200;
        $image = imagecreatetruecolor($size, $size);
        $white = imagecolorallocate($image, 255, 255, 255);
        $gray = imagecolorallocate($image, 128, 128, 128);
        $green = imagecolorallocate($image, 15, 118, 110);
        
        imagefill($image, 0, 0, $white);
        
        // Dessiner un motif simple
        for ($i = 0; $i < 10; $i++) {
            for ($j = 0; $j < 10; $j++) {
                $color = (($i + $j) % 2 == 0) ? $green : $gray;
                imagefilledrectangle($image, 
                    20 + ($i * 16), 
                    20 + ($j * 16), 
                    20 + ($i * 16) + 14, 
                    20 + ($j * 16) + 14, 
                    $color);
            }
        }
        
        header('Content-Type: image/png');
        imagepng($image);
        imagedestroy($image);
        exit();
    }
}

// Dispatcher pour les appels directs
if (basename($_SERVER['PHP_SELF']) == 'QRCodeController.php') {
    $qrController = new QRCodeController();
    $qrController->dispatch();
}
?>