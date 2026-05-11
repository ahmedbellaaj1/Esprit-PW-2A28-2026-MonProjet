<?php
class Participation {
    private $id;
    private $evenement_id;
    private $user_id;
    private $statut;
    private $code_qr;
    private $date_inscription;
    private $date_validation;

    // Données utilisateur (jointure)
    private $user_nom;
    private $user_email;
    private $user_telephone;

    public function __construct($evenement_id = 0, $user_id = 0) {
        $this->evenement_id = $evenement_id;
        $this->user_id = $user_id;
        $this->statut = 'inscrit';
        $this->code_qr = $this->generateQRCode();
    }

    private function generateQRCode() {
        return uniqid('QR_') . bin2hex(random_bytes(16));
    }

    // Setters avec validation
    public function setEvenementId($id) {
        $this->evenement_id = (int)$id;
        return $this;
    }

    public function setUserId($id) {
        $this->user_id = (int)$id;
        return $this;
    }

    public function setStatut($statut) {
        $validStatuts = ['inscrit', 'present', 'annule', 'en_attente'];
        if (!in_array($statut, $validStatuts)) {
            throw new Exception("Statut invalide");
        }
        $this->statut = $statut;
        if ($statut == 'present') {
            $this->date_validation = date('Y-m-d H:i:s');
        }
        return $this;
    }

    // Données utilisateur (jointure)
    public function setUserNom($nom) {
        $this->user_nom = htmlspecialchars($nom, ENT_QUOTES);
        return $this;
    }

    public function setUserEmail($email) {
        $this->user_email = htmlspecialchars($email, ENT_QUOTES);
        return $this;
    }

    public function setUserTelephone($telephone) {
        $this->user_telephone = htmlspecialchars($telephone, ENT_QUOTES);
        return $this;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getEvenementId() { return $this->evenement_id; }
    public function getUserId() { return $this->user_id; }
    public function getStatut() { return $this->statut; }
    public function getCodeQR() { return $this->code_qr; }
    public function getDateInscription() { return $this->date_inscription; }
    public function getDateValidation() { return $this->date_validation; }
    public function getUserNom() { return $this->user_nom; }
    public function getUserEmail() { return $this->user_email; }
    public function getUserTelephone() { return $this->user_telephone; }

    public function getStatutLabel() {
        $labels = [
            'inscrit' => '✅ Inscrit',
            'present' => '🎉 Présent',
            'annule' => '❌ Annulé',
            'en_attente' => '⏳ En attente'
        ];
        return $labels[$this->statut] ?? $this->statut;
    }

    public function isValid() {
        return ($this->evenement_id > 0 && $this->user_id > 0);
    }
}
?>