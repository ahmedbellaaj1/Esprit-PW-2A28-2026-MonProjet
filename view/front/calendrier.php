<?php
// view/front/calendrier.php
require_once "../../controller/EvenementController.php";

$controller = new EvenementController();
$events = $controller->listEvenements();

// Grouper les événements par mois pour l'affichage
$eventsByMonth = [];
$currentYear = date('Y');
$currentMonth = date('m');

foreach ($events as $event) {
    $date = $event['date_event'];
    $year = date('Y', strtotime($date));
    $month = date('m', strtotime($date));
    $monthYear = date('F Y', strtotime($date));
    
    if (!isset($eventsByMonth[$monthYear])) {
        $eventsByMonth[$monthYear] = [];
    }
    $eventsByMonth[$monthYear][] = $event;
}

// Trier les événements par date
foreach ($eventsByMonth as &$monthEvents) {
    usort($monthEvents, function($a, $b) {
        return strtotime($a['date_event']) - strtotime($b['date_event']);
    });
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendrier des événements - GreenBite</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(135deg, #f0fdfa 0%, #e6f7f5 100%);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: linear-gradient(90deg, #0f766e 0%, #14b8a6 100%);
            padding: 0 2rem;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 4px 20px rgba(15, 118, 110, 0.25);
        }

        .navbar-logo {
            font-size: 1.6rem;
            font-weight: 700;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-logo span {
            color: #ccfbf1;
        }

        .navbar-logo img {
            height: 35px;
            width: 35px;
            border-radius: 8px;
            object-fit: cover;
        }

        .navbar-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .navbar-links a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 0.5rem 0;
        }

        .navbar-links a:hover,
        .navbar-links a.active {
            color: white;
            border-bottom: 2px solid white;
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-btn {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 9999px;
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .nav-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            color: #64748b;
            font-size: 0.95rem;
        }

        /* Navigation mois */
        .month-navigation {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .month-btn {
            background: white;
            border: 2px solid #e2e8f0;
            padding: 0.6rem 1.2rem;
            border-radius: 9999px;
            cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s ease;
            color: #64748b;
        }

        .month-btn:hover,
        .month-btn.active {
            background: #0f766e;
            color: white;
            border-color: #0f766e;
            transform: translateY(-2px);
        }

        /* Calendrier */
        .calendar-container {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .calendar-header {
            background: linear-gradient(135deg, #0f766e, #14b8a6);
            padding: 1.5rem;
            text-align: center;
            color: white;
        }

        .calendar-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .calendar-weekday {
            padding: 1rem;
            text-align: center;
            font-weight: 600;
            color: #64748b;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .calendar-days {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }

        .calendar-day {
            min-height: 120px;
            border-right: 1px solid #f1f5f9;
            border-bottom: 1px solid #f1f5f9;
            padding: 0.5rem;
            transition: all 0.3s ease;
            background: white;
        }

        .calendar-day:hover {
            background: #f8fafc;
        }

        .calendar-day.empty {
            background: #f8fafc;
            color: #cbd5e1;
        }

        .calendar-day-number {
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: #64748b;
        }

        .calendar-day.today .calendar-day-number {
            background: #0f766e;
            color: white;
            width: 28px;
            height: 28px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        /* Événements dans le calendrier */
        .calendar-event {
            background: #dcfce7;
            color: #166534;
            padding: 0.25rem 0.5rem;
            border-radius: 8px;
            font-size: 0.7rem;
            margin-bottom: 0.25rem;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            transition: all 0.2s ease;
        }

        .calendar-event:hover {
            background: #bbf7d0;
            transform: translateX(2px);
        }

        .calendar-event.today-event {
            background: #fef3c7;
            color: #92400e;
        }

        .calendar-event.past-event {
            background: #f1f5f9;
            color: #64748b;
        }

        /* Vue liste des événements */
        .events-list {
            margin-top: 2rem;
        }

        .events-list-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .events-list-title::before {
            content: '';
            width: 4px;
            height: 22px;
            background: #14b8a6;
            border-radius: 2px;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }

        .event-card {
            background: white;
            border-radius: 16px;
            padding: 1rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #e2e8f0;
        }

        .event-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(15, 118, 110, 0.1);
            border-color: #14b8a6;
        }

        .event-card-date {
            font-size: 0.7rem;
            color: #64748b;
            margin-bottom: 0.25rem;
        }

        .event-card-title {
            font-size: 1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.25rem;
        }

        .event-card-lieu {
            font-size: 0.8rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .event-card-type {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.2rem 0.6rem;
            border-radius: 9999px;
            font-size: 0.65rem;
            font-weight: 600;
        }

        .type-Atelier {
            background: #dcfce7;
            color: #166534;
        }
        .type-Conférence {
            background: #dbeafe;
            color: #1e40af;
        }
        .type-Festival {
            background: #fef3c7;
            color: #92400e;
        }
        .type-Autre {
            background: #f3e8ff;
            color: #6b21a5;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 16px;
        }

        .empty-state .emoji {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            font-size: 1.2rem;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #64748b;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                height: auto;
                padding: 1rem;
                gap: 0.5rem;
            }

            .navbar-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }

            .main-container {
                padding: 1rem;
            }

            .calendar-day {
                min-height: 80px;
                font-size: 0.7rem;
            }

            .calendar-event {
                font-size: 0.6rem;
                padding: 0.15rem 0.3rem;
            }

            .calendar-weekday {
                font-size: 0.7rem;
                padding: 0.5rem;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .month-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }
        }

        @media (max-width: 640px) {
            .calendar-day {
                min-height: 60px;
            }

            .calendar-day-number {
                font-size: 0.7rem;
            }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a href="listEvenements.php" class="navbar-logo">
        <img src="../assets/images/logo.png" alt="GreenBite">
        <span>Green<span>Bite</span></span>
    </a>
    <ul class="navbar-links">
        <li><a href="listEvenements.php">Événements</a></li>
        <li><a href="recherche-avancee.php">🔍 Recherche avancée</a></li>
        <li><a href="calendrier.php" class="active">📅 Calendrier</a></li>
    </ul>
    <div class="navbar-right">
        <a href="../back/dashboardEvenement.php" class="nav-btn">👨‍💼 Admin</a>
    </div>
</nav>

<div class="main-container">
    <div class="page-header">
        <h1>📅 Calendrier des événements</h1>
        <p>Visualisez tous les événements GreenBite sur un calendrier interactif</p>
    </div>

    <!-- Sélecteur de mois -->
    <div class="month-navigation" id="monthNav">
        <button class="month-btn" data-month="1">Janvier</button>
        <button class="month-btn" data-month="2">Février</button>
        <button class="month-btn" data-month="3">Mars</button>
        <button class="month-btn" data-month="4">Avril</button>
        <button class="month-btn" data-month="5">Mai</button>
        <button class="month-btn" data-month="6">Juin</button>
        <button class="month-btn" data-month="7">Juillet</button>
        <button class="month-btn" data-month="8">Août</button>
        <button class="month-btn" data-month="9">Septembre</button>
        <button class="month-btn" data-month="10">Octobre</button>
        <button class="month-btn" data-month="11">Novembre</button>
        <button class="month-btn" data-month="12">Décembre</button>
    </div>

    <!-- Calendrier -->
    <div class="calendar-container" id="calendarContainer">
        <div class="calendar-header">
            <h2 id="currentMonthYear">Mai 2026</h2>
        </div>
        <div class="calendar-grid">
            <div class="calendar-weekday">Lun</div>
            <div class="calendar-weekday">Mar</div>
            <div class="calendar-weekday">Mer</div>
            <div class="calendar-weekday">Jeu</div>
            <div class="calendar-weekday">Ven</div>
            <div class="calendar-weekday">Sam</div>
            <div class="calendar-weekday">Dim</div>
        </div>
        <div class="calendar-days" id="calendarDays"></div>
    </div>

    <!-- Liste des événements du mois -->
    <div class="events-list">
        <div class="events-list-title">
            📋 Événements du mois
        </div>
        <div class="events-grid" id="eventsList"></div>
    </div>
</div>

<script>
    // Données des événements depuis PHP
    const eventsData = <?php 
        $eventsJson = [];
        foreach ($events as $e) {
            $eventsJson[] = [
                'id' => $e['id'],
                'title' => $e['titre'],
                'date' => $e['date_event'],
                'lieu' => $e['lieu'],
                'type' => $e['type']
            ];
        }
        echo json_encode($eventsJson);
    ?>;

    let currentDate = new Date();
    let currentYear = currentDate.getFullYear();
    let currentMonth = currentDate.getMonth();

    // Jours de la semaine (commence par lundi)
    const weekdays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    // Mois en français
    const monthNames = [
        'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin',
        'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'
    ];

    // Fonction pour obtenir la couleur du type d'événement
    function getEventTypeClass(type) {
        switch(type) {
            case 'Atelier': return 'type-Atelier';
            case 'Conférence': return 'type-Conférence';
            case 'Festival': return 'type-Festival';
            default: return 'type-Autre';
        }
    }

    // Fonction pour obtenir la classe de l'événement selon la date
    function getEventClass(eventDate) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const eventDateObj = new Date(eventDate);
        
        if (eventDateObj.toDateString() === today.toDateString()) {
            return 'today-event';
        } else if (eventDateObj < today) {
            return 'past-event';
        }
        return '';
    }

    // Fonction pour formater la date
    function formatDate(dateStr) {
        const date = new Date(dateStr);
        return date.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
    }

    // Fonction pour afficher le calendrier
    function renderCalendar() {
        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const startDayOfWeek = firstDayOfMonth.getDay(); // 0 = dimanche
        
        // Ajuster pour commencer par lundi (0 = dimanche, 1 = lundi, ...)
        let startOffset = startDayOfWeek === 0 ? 6 : startDayOfWeek - 1;
        
        const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        // Mettre à jour l'en-tête
        document.getElementById('currentMonthYear').textContent = `${monthNames[currentMonth]} ${currentYear}`;
        
        // Générer les jours
        let calendarHtml = '';
        
        // Jours vides avant le premier jour du mois
        for (let i = 0; i < startOffset; i++) {
            calendarHtml += `<div class="calendar-day empty"></div>`;
        }
        
        // Jours du mois
        for (let day = 1; day <= daysInMonth; day++) {
            const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const isToday = dateStr === today.toISOString().split('T')[0];
            const dayEvents = eventsData.filter(e => e.date === dateStr);
            
            calendarHtml += `<div class="calendar-day ${isToday ? 'today' : ''}">
                <div class="calendar-day-number">${day}</div>`;
            
            // Afficher les événements du jour (max 2)
            dayEvents.slice(0, 2).forEach(event => {
                const eventClass = getEventClass(event.date);
                calendarHtml += `
                    <div class="calendar-event ${eventClass}" onclick="event.stopPropagation(); window.location.href='showEvenement.php?id=${event.id}'" title="${event.title}">
                        ${event.title.length > 18 ? event.title.substring(0, 18) + '...' : event.title}
                    </div>
                `;
            });
            
            if (dayEvents.length > 2) {
                calendarHtml += `<div class="calendar-event" style="background:#e2e8f0; color:#64748b; cursor:default;">+${dayEvents.length - 2} autres</div>`;
            }
            
            calendarHtml += `</div>`;
        }
        
        // Remplir la fin du mois
        const totalCells = startOffset + daysInMonth;
        const remainingCells = 42 - totalCells;
        for (let i = 0; i < remainingCells; i++) {
            calendarHtml += `<div class="calendar-day empty"></div>`;
        }
        
        document.getElementById('calendarDays').innerHTML = calendarHtml;
        
        // Afficher la liste des événements du mois
        renderEventsList();
    }

    // Fonction pour afficher la liste des événements du mois
    function renderEventsList() {
        const startDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-01`;
        const endDate = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${new Date(currentYear, currentMonth + 1, 0).getDate()}`;
        
        const monthEvents = eventsData.filter(e => e.date >= startDate && e.date <= endDate);
        
        if (monthEvents.length === 0) {
            document.getElementById('eventsList').innerHTML = `
                <div class="empty-state">
                    <div class="emoji">📭</div>
                    <h3>Aucun événement ce mois-ci</h3>
                    <p>Revenez plus tard pour découvrir nos événements !</p>
                </div>
            `;
            return;
        }
        
        // Trier les événements par date
        monthEvents.sort((a, b) => new Date(a.date) - new Date(b.date));
        
        let eventsHtml = '';
        monthEvents.forEach(event => {
            eventsHtml += `
                <div class="event-card" onclick="window.location.href='showEvenement.php?id=${event.id}'">
                    <div class="event-card-date">📅 ${formatDate(event.date)}</div>
                    <div class="event-card-title">${escapeHtml(event.title)}</div>
                    <div class="event-card-lieu">📍 ${escapeHtml(event.lieu)}</div>
                    <span class="event-card-type ${getEventTypeClass(event.type)}">${event.type}</span>
                </div>
            `;
        });
        
        document.getElementById('eventsList').innerHTML = eventsHtml;
    }

    // Fonction pour échapper le HTML
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    // Changer de mois
    function changeMonth(month) {
        currentMonth = month - 1;
        renderCalendar();
    }

    // Initialisation
    function initCalendar() {
        renderCalendar();
        
        // Mettre en surbrillance le mois actuel dans la navigation
        const currentMonthBtn = document.querySelector(`.month-btn[data-month="${currentMonth + 1}"]`);
        if (currentMonthBtn) {
            currentMonthBtn.classList.add('active');
        }
        
        // Ajouter les événements aux boutons
        document.querySelectorAll('.month-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const month = parseInt(this.dataset.month);
                
                // Enlever la classe active de tous les boutons
                document.querySelectorAll('.month-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                changeMonth(month);
            });
        });
    }

    initCalendar();
</script>

</body>
</html>