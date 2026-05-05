<?php
require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/EvenementController.php";
require_once __DIR__ . "/OrganisateurController.php";

class ExportController {
    private $db;
    private $eventController;
    private $organisateurController;

    public function __construct() {
        $this->db = config::getConnexion();
        $this->eventController = new EvenementController();
        $this->organisateurController = new OrganisateurController();
    }

    /**
     * Export des événements au format CSV
     */
    public function exportEventsToCSV() {
        $events = $this->eventController->listEvenements();
        
        if (empty($events)) {
            $_SESSION['message'] = "Aucun événement à exporter";
            header('Location: dashboardEvenement.php');
            exit();
        }
        
        // Nom du fichier
        $filename = "evenements_" . date('Y-m-d_H-i-s') . ".csv";
        
        // En-têtes HTTP
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Création du fichier CSV
        $output = fopen('php://output', 'w');
        
        // BOM pour UTF-8 (compatibilité Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // En-têtes des colonnes
        fputcsv($output, [
            'ID', 
            'Titre', 
            'Description', 
            'Date', 
            'Lieu', 
            'Type', 
            'Organisateur', 
            'Statut'
        ]);
        
        // Données
        foreach ($events as $event) {
            $statut = $event['date_event'] >= date('Y-m-d') ? 'À venir' : 'Passé';
            fputcsv($output, [
                $event['id'],
                $event['titre'],
                strip_tags($event['description']),
                date('d/m/Y', strtotime($event['date_event'])),
                $event['lieu'],
                $event['type'],
                $event['organisateur_nom'] ?? 'Non défini',
                $statut
            ]);
        }
        
        fclose($output);
        exit();
    }

    /**
     * Export des événements au format Excel (XLS)
     */
    public function exportEventsToExcel() {
        $events = $this->eventController->listEvenements();
        
        if (empty($events)) {
            $_SESSION['message'] = "Aucun événement à exporter";
            header('Location: dashboardEvenement.php');
            exit();
        }
        
        // Création du HTML pour Excel
        $html = '<html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<style>';
        $html .= 'th { background-color: #0f766e; color: white; padding: 8px; }';
        $html .= 'td { padding: 6px; border-bottom: 1px solid #ddd; }';
        $html .= 'table { border-collapse: collapse; width: 100%; }';
        $html .= '</style>';
        $html .= '</head><body>';
        $html .= '<h1>Liste des événements GreenBite</h1>';
        $html .= '<p>Généré le ' . date('d/m/Y à H:i:s') . '</p>';
        $html .= '<table border="1">';
        $html .= '<tr>';
        $html .= '<th>ID</th><th>Titre</th><th>Date</th><th>Lieu</th><th>Type</th><th>Organisateur</th><th>Statut</th>';
        $html .= '</tr>';
        
        foreach ($events as $event) {
            $statut = $event['date_event'] >= date('Y-m-d') ? 'À venir' : 'Passé';
            $html .= '<tr>';
            $html .= '<td>' . $event['id'] . '</td>';
            $html .= '<td>' . htmlspecialchars($event['titre']) . '</td>';
            $html .= '<td>' . date('d/m/Y', strtotime($event['date_event'])) . '</td>';
            $html .= '<td>' . htmlspecialchars($event['lieu']) . '</td>';
            $html .= '<td>' . $event['type'] . '</td>';
            $html .= '<td>' . htmlspecialchars($event['organisateur_nom'] ?? 'Non défini') . '</td>';
            $html .= '<td>' . $statut . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        $html .= '<p style="margin-top: 20px;">GreenBite - Plateforme événementielle éco-responsable</p>';
        $html .= '</body></html>';
        
        $filename = "evenements_" . date('Y-m-d_H-i-s') . ".xls";
        
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        echo $html;
        exit();
    }

    /**
     * Export des organisateurs au format CSV
     */
    public function exportOrganisateursToCSV() {
        $organisateurs = $this->organisateurController->listOrganisateurs();
        
        if (empty($organisateurs)) {
            $_SESSION['message'] = "Aucun organisateur à exporter";
            header('Location: organisateurs.php');
            exit();
        }
        
        $filename = "organisateurs_" . date('Y-m-d_H-i-s') . ".csv";
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID', 'Nom', 'Email', 'Téléphone', 'Adresse', 'Site Web', 
            'Nombre événements', 'Date inscription'
        ]);
        
        foreach ($organisateurs as $org) {
            $eventCount = $this->eventController->countEventsByOrganisateur($org['id']);
            fputcsv($output, [
                $org['id'],
                $org['nom'],
                $org['email'],
                $org['telephone'],
                $org['adresse'] ?? '',
                $org['site_web'] ?? '',
                $eventCount,
                date('d/m/Y', strtotime($org['created_at'] ?? date('Y-m-d')))
            ]);
        }
        
        fclose($output);
        exit();
    }

    /**
     * Export des participants d'un événement (si table participant existe)
     */
    public function exportParticipantsToCSV($eventId) {
        try {
            $eventId = filter_var($eventId, FILTER_VALIDATE_INT);
            if (!$eventId || $eventId <= 0) {
                throw new Exception("ID d'événement invalide");
            }
            
            $sql = "SELECT * FROM participant WHERE evenement_id = :event_id";
            $query = $this->db->prepare($sql);
            $query->execute(['event_id' => $eventId]);
            $participants = $query->fetchAll();
            
            $event = $this->eventController->getEvenementById($eventId);
            
            $filename = "participants_" . sanitize_title($event['titre']) . "_" . date('Y-m-d') . ".csv";
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            
            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($output, ['Nom', 'Email', 'Téléphone', 'Statut', "Date d'inscription"]);
            
            foreach ($participants as $p) {
                fputcsv($output, [
                    $p['nom'],
                    $p['email'],
                    $p['telephone'] ?? '',
                    $p['statut'],
                    date('d/m/Y H:i', strtotime($p['date_inscription']))
                ]);
            }
            
            fclose($output);
            exit();
        } catch (Exception $e) {
            $_SESSION['message'] = $e->getMessage();
            header('Location: dashboardEvenement.php');
            exit();
        }
    }

    /**
     * Export des statistiques au format PDF (HTML version)
     */
    public function exportStatsToHTML() {
        $stats = $this->eventController->getStats();
        $events = $this->eventController->listEvenements();
        $organisateurs = $this->organisateurController->listOrganisateurs();
        
        // Calculs supplémentaires
        $eventsByType = [];
        $eventsByMonth = [];
        
        foreach ($events as $event) {
            $type = $event['type'];
            if (!isset($eventsByType[$type])) $eventsByType[$type] = 0;
            $eventsByType[$type]++;
            
            $month = date('F Y', strtotime($event['date_event']));
            if (!isset($eventsByMonth[$month])) $eventsByMonth[$month] = 0;
            $eventsByMonth[$month]++;
        }
        
        $html = '<!DOCTYPE html>';
        $html .= '<html><head>';
        $html .= '<meta charset="UTF-8">';
        $html .= '<title>Statistiques GreenBite</title>';
        $html .= '<style>';
        $html .= 'body { font-family: Arial, sans-serif; margin: 40px; }';
        $html .= 'h1 { color: #0f766e; border-bottom: 2px solid #14b8a6; padding-bottom: 10px; }';
        $html .= 'h2 { color: #0f766e; margin-top: 30px; }';
        $html .= '.stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 20px 0; }';
        $html .= '.stat-card { background: #f8fafc; padding: 15px; border-radius: 10px; text-align: center; }';
        $html .= '.stat-value { font-size: 32px; font-weight: bold; color: #0f766e; }';
        $html .= '.stat-label { color: #64748b; }';
        $html .= 'table { width: 100%; border-collapse: collapse; margin: 20px 0; }';
        $html .= 'th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }';
        $html .= 'th { background: #0f766e; color: white; }';
        $html .= '.footer { margin-top: 50px; text-align: center; font-size: 12px; color: #64748b; }';
        $html .= '</style>';
        $html .= '</head><body>';
        
        $html .= '<h1>📊 GreenBite - Rapport de statistiques</h1>';
        $html .= '<p>Généré le ' . date('d/m/Y à H:i:s') . '</p>';
        
        $html .= '<div class="stats-grid">';
        $html .= '<div class="stat-card"><div class="stat-value">' . ($stats['total'] ?? 0) . '</div><div class="stat-label">Total événements</div></div>';
        $html .= '<div class="stat-card"><div class="stat-value">' . ($stats['upcoming'] ?? 0) . '</div><div class="stat-label">Événements à venir</div></div>';
        $html .= '<div class="stat-card"><div class="stat-value">' . count($organisateurs) . '</div><div class="stat-label">Organisateurs</div></div>';
        $html .= '</div>';
        
        $html .= '<h2>📅 Événements par type</h2>';
        $html .= '<table>';
        $html .= '<tr><th>Type</th><th>Nombre</th><th>Pourcentage</th></tr>';
        foreach ($eventsByType as $type => $count) {
            $percentage = round(($count / ($stats['total'] ?? 1)) * 100);
            $html .= '<tr><td>' . $type . '</td><td>' . $count . '</td><td>' . $percentage . '%</td></tr>';
        }
        $html .= '</table>';
        
        $html .= '<h2>📈 Événements par mois</h2>';
        $html .= '<table>';
        $html .= '<tr><th>Mois</th><th>Nombre d\'événements</th></tr>';
        foreach ($eventsByMonth as $month => $count) {
            $html .= '<tr><td>' . $month . '</td><td>' . $count . '</td></tr>';
        }
        $html .= '</table>';
        
        $html .= '<div class="footer">';
        $html .= 'GreenBite - Plateforme événementielle éco-responsable<br>';
        $html .= 'Rapport généré automatiquement';
        $html .= '</div>';
        
        $html .= '</body></html>';
        
        $filename = "statistiques_greenbite_" . date('Y-m-d') . ".html";
        
        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        echo $html;
        exit();
    }
}

// Fonction utilitaire
function sanitize_title($title) {
    $title = strtolower($title);
    $title = preg_replace('/[^a-z0-9]+/', '_', $title);
    $title = trim($title, '_');
    return $title;
}
?>