        <?php
        if (session_status() === PHP_SESSION_NONE) session_start();

        require_once __DIR__ . '/../config/database.php';
        require_once __DIR__ . '/../models/DocumentRequest.php';
        require_once __DIR__ . '/../models/ActivityLog.php';
        require_once __DIR__ . '/../models/Resident.php';
        require_once __DIR__ . '/../models/Household.php';

        // ADMIN GUARD
        if (empty($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header("Location: /BIS/views/login.php");
            exit;
        }

        // MODELS ( create first)
        $docReq     = new DocumentRequest($db);
        $logModel   = new ActivityLog($db);
        $resModel   = new Resident($db);
        $houseModel = new Household($db);

        // COUNTERS (existing)
        $pendingCount  = $docReq->countByStatus('Pending');
        $approvedCount = $docReq->countByStatus('Approved');
        
        // NEW TOTALS
        $totalResidents  = $resModel->countActive();
        $totalHouseholds = $houseModel->countActive();

        date_default_timezone_set('Asia/Manila');

        // NEW (needs methods in DocumentRequest)
        $incomeTotal   = $docReq->incomeTotalReleased();
        $todayRows     = $docReq->releasedTodayList(20);
        $todaySummary  = $docReq->releasedTodaySummary();

        $statusMap     = $docReq->statusCounts();
        $statusLabels  = array_keys($statusMap);
        $statusCounts  = array_values($statusMap);

        // income last 7 days
        $incomeLabels = [];
        $incomeValues = [];
        for ($i=6; $i>=0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $incomeLabels[] = $d;
            $incomeValues[] = $docReq->incomeByDate($d);
        }

        // RECENT LOGS (existing)
        $recentLogs = $logModel->latest(5);

        // VIEW
        require_once __DIR__ . '/../views/admin_dashboard.php';
