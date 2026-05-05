<?php
require_once __DIR__ . "/ExportController.php";

$export = new ExportController();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'export_events_csv':
        $export->exportEventsToCSV();
        break;
    case 'export_events_excel':
        $export->exportEventsToExcel();
        break;
    case 'export_organisateurs_csv':
        $export->exportOrganisateursToCSV();
        break;
    case 'export_stats_html':
        $export->exportStatsToHTML();
        break;
    case 'export_participants':
        $eventId = $_GET['event_id'] ?? 0;
        $export->exportParticipantsToCSV($eventId);
        break;
    default:
        header('Location: ../view/back/dashboardEvenement.php');
        exit();
}
?>