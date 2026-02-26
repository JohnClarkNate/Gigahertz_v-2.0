<?php
session_start();
require 'db.php';

date_default_timezone_set('Asia/Manila');

const DEFAULT_SCHEDULE_START = '09:00:00';
const DEFAULT_SCHEDULE_END = '18:00:00';

$message = '';
$message_is_success = false;
$show_time_out = false;
$prefillEmployeeId = '';

if (isset($_SESSION['attendance_flash']) && is_array($_SESSION['attendance_flash'])) {
    $flash = $_SESSION['attendance_flash'];
    unset($_SESSION['attendance_flash']);
    $message = $flash['message'] ?? '';
    $message_is_success = !empty($flash['is_success']);
    $show_time_out = !empty($flash['show_time_out']);
    $prefillEmployeeId = $flash['employee_id'] ?? '';
}

function getWorkSchedule(PDO $pdo, int $companyId, string $employeeIdentifier): array
{
    static $scheduleCache = [];
    $cacheKey = $companyId . '|' . strtolower(trim($employeeIdentifier));

    if (isset($scheduleCache[$cacheKey])) {
        return $scheduleCache[$cacheKey];
    }

    $schedule = null;
    if ($employeeIdentifier !== '') {
        $stmt = $pdo->prepare("SELECT scheduled_start_time, scheduled_end_time, allow_paid_overtime FROM work_schedules WHERE company_id = ? AND employee_id = ? LIMIT 1");
        $stmt->execute([$companyId, $employeeIdentifier]);
        $schedule = $stmt->fetch();
    }

    if (!$schedule) {
        $stmt = $pdo->prepare("SELECT scheduled_start_time, scheduled_end_time, allow_paid_overtime FROM work_schedules WHERE company_id = ? AND (employee_id IS NULL OR employee_id = '') LIMIT 1");
        $stmt->execute([$companyId]);
        $schedule = $stmt->fetch();
    }

    $resolved = [
        'start' => $schedule['scheduled_start_time'] ?? DEFAULT_SCHEDULE_START,
        'end' => $schedule['scheduled_end_time'] ?? DEFAULT_SCHEDULE_END,
        'allow_paid_overtime' => isset($schedule['allow_paid_overtime']) ? (bool)$schedule['allow_paid_overtime'] : false,
    ];

    $scheduleCache[$cacheKey] = $resolved;
    return $resolved;
}

function calculateMinutesDiff(DateTime $later, DateTime $earlier): int
{
    $diffSeconds = $later->getTimestamp() - $earlier->getTimestamp();
    if ($diffSeconds <= 0) {
        return 0;
    }
    return (int)ceil($diffSeconds / 60);
}

function formatDurationHrsMins(int $minutes): string
{
    if ($minutes <= 0) {
        return '00:00';
    }
    $hours = intdiv($minutes, 60);
    $remaining = $minutes % 60;
    return sprintf('%02d:%02d', $hours, $remaining);
}

function buildDateTime(string $date, string $time): DateTime
{
    return new DateTime($date . ' ' . $time, new DateTimeZone('Asia/Manila'));
}

function lookupEmployeeRecord(PDO $pdo, string $employeeIdentifier): ?array
{
    $identifier = trim($employeeIdentifier);
    if ($identifier === '') {
        return null;
    }

    $stmt = $pdo->prepare("SELECT id, username, company_id FROM users WHERE employee_id = ? LIMIT 1");
    $stmt->execute([$identifier]);
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return [
            'attendance_user_id' => (int)$user['id'],
            'company_id' => (int)$user['company_id'],
            'display_name' => $user['username'],
            'employee_type' => 'head',
        ];
    }

    $stmt = $pdo->prepare("SELECT id, name, company_id FROM hr WHERE employee_id = ? LIMIT 1");
    $stmt->execute([$identifier]);
    if ($hr = $stmt->fetch(PDO::FETCH_ASSOC)) {
        return [
            'attendance_user_id' => -(int)$hr['id'],
            'company_id' => (int)$hr['company_id'],
            'display_name' => $hr['name'],
            'employee_type' => 'general',
        ];
    }

    return null;
}

// --- AJAX check for attendance (returns JSON) ---
if (isset($_GET['check']) && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');

    $employee_id  = isset($_GET['employee_id']) ? trim($_GET['employee_id']) : '';
    $today = date('Y-m-d');

    if ($employee_id === '') {
        echo json_encode(['ok' => false, 'error' => 'missing']);
        exit();
    }

    $employeeRecord = lookupEmployeeRecord($pdo, $employee_id);
    if (!$employeeRecord) {
        echo json_encode(['ok' => true, 'found' => false, 'show_time_out' => false]);
        exit();
    }

    $company_id = $employeeRecord['company_id'];
    $user_id_for_attendance = $employeeRecord['attendance_user_id'];

    $stmt = $pdo->prepare("SELECT id, time_in, time_out FROM attendance WHERE user_id = ? AND company_id = ? AND date = ?");
    $stmt->execute([$user_id_for_attendance, $company_id, $today]);
    $record = $stmt->fetch();

    // show_time_out true when a record exists and time_out is null (so button should be Time Out)
    $show_time_out = $record && empty($record['time_out']);

    echo json_encode([
        'ok' => true,
        'found' => true,
        'record_exists' => (bool)$record,
        'show_time_out' => (bool)$show_time_out
    ]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kiosk_submit'])) {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $today = date('Y-m-d');

    if ($employee_id === '') {
        $message = "Employee/User ID is required.";
        $message_is_success = false;
    } else {
        $employeeRecord = lookupEmployeeRecord($pdo, $employee_id);
        if ($employeeRecord) {
            $company_id = $employeeRecord['company_id'];
            $user_name = $employeeRecord['display_name'];
            $user_id_for_attendance = $employeeRecord['attendance_user_id'];
            $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND company_id = ? AND date = ?");
            $stmt->execute([$user_id_for_attendance, $company_id, $today]);
            $record = $stmt->fetch();
            $hasOpenRecord = $record && empty($record['time_out']);
            $schedule = getWorkSchedule($pdo, $company_id, $employee_id);
            $scheduledStartDt = buildDateTime($today, $schedule['start']);
            $scheduledEndDt = buildDateTime($today, $schedule['end']);
            $employee_type = $user_id_for_attendance > 0 ? 'head' : 'general';

            if (isset($_POST['time_in']) && !$record) {
                // Record Clock In
                $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                $timeInFormatted = $now->format('Y-m-d H:i:s');
                $lateMinutes = 0;
                $isLate = 0;

                if ($now > $scheduledStartDt) {
                    $lateMinutes = calculateMinutesDiff($now, $scheduledStartDt);
                    $isLate = $lateMinutes > 0 ? 1 : 0;
                }

                $stmt = $pdo->prepare("INSERT INTO attendance (user_id, company_id, employee_id, employee_name, employee_type, time_in, date, scheduled_start_time, scheduled_end_time, is_late, late_minutes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id_for_attendance,
                    $company_id,
                    $employee_id,
                    $user_name,
                    $employee_type,
                    $timeInFormatted,
                    $today,
                    $schedule['start'],
                    $schedule['end'],
                    $isLate,
                    $lateMinutes,
                ]);

                $message = "Clock In recorded for {$user_name} (ID: {$employee_id}).";
                if ($isLate) {
                    $lateDuration = formatDurationHrsMins($lateMinutes);
                    $message .= " Tagged as Late ({$lateDuration} late).";
                }
                $message_is_success = true;
                // --- END MODIFIED SUCCESS MESSAGE ---
                $show_time_out = true; // <- show Time Out after successful Clock In
            } elseif (isset($_POST['time_out']) && $record && !$record['time_out']) {
                // Record Clock Out
                $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
                $timeOutFormatted = $now->format('Y-m-d H:i:s');

                $scheduledStartStr = !empty($record['scheduled_start_time']) ? $record['scheduled_start_time'] : $schedule['start'];
                $scheduledEndStr = !empty($record['scheduled_end_time']) ? $record['scheduled_end_time'] : $schedule['end'];
                $scheduledStartForDay = buildDateTime($record['date'], $scheduledStartStr);
                $scheduledEndForDay = buildDateTime($record['date'], $scheduledEndStr);

                $earlyMinutes = 0;
                $isEarlyClockout = 0;
                $otMinutes = 0;
                $isOtWithoutPay = 0;
                $overtimeIsPaid = 0;

                if ($now < $scheduledEndForDay) {
                    $earlyMinutes = calculateMinutesDiff($scheduledEndForDay, $now);
                    $isEarlyClockout = $earlyMinutes > 0 ? 1 : 0;
                } elseif ($now > $scheduledEndForDay) {
                    $otMinutes = calculateMinutesDiff($now, $scheduledEndForDay);
                    if ($schedule['allow_paid_overtime']) {
                        $overtimeIsPaid = $otMinutes > 0 ? 1 : 0;
                    } else {
                        $isOtWithoutPay = $otMinutes > 0 ? 1 : 0;
                    }
                }

                $stmt = $pdo->prepare("UPDATE attendance SET time_out = ?, scheduled_start_time = ?, scheduled_end_time = ?, is_ot_without_pay = ?, overtime_is_paid = ?, ot_minutes = ?, is_early_clockout = ?, early_minutes = ? WHERE id = ?");
                $stmt->execute([
                    $timeOutFormatted,
                    $scheduledStartStr,
                    $scheduledEndStr,
                    $isOtWithoutPay,
                    $overtimeIsPaid,
                    $otMinutes,
                    $isEarlyClockout,
                    $earlyMinutes,
                    $record['id'],
                ]);

                $message = "Clock Out recorded for {$user_name} (ID: {$employee_id}).";
                $message_is_success = true;
                if ($isOtWithoutPay) {
                    $otDuration = formatDurationHrsMins($otMinutes);
                    $message .= " Tagged as OT without pay ({$otDuration} hrs:mins).";
                } elseif ($overtimeIsPaid) {
                    $otDuration = formatDurationHrsMins($otMinutes);
                    $message .= " Overtime recorded ({$otDuration} hrs:mins).";
                }
                if ($isEarlyClockout) {
                    $earlyDuration = formatDurationHrsMins($earlyMinutes);
                    $message .= " Tagged as Early Clock-Out ({$earlyDuration} hrs:mins early).";
                }
                // --- END MODIFIED SUCCESS MESSAGE ---
                $show_time_out = false; // <- hide Time Out after Clock Out
            } elseif (!$record && isset($_POST['time_out'])) {
                // Attempted Clock Out without Clock In
                $message = "Please Clock In first before Clock Out ({$user_name} - ID: {$employee_id}).";
                $message_is_success = false;
                $show_time_out = false;
            } else {
                if ($hasOpenRecord) {
                    $message = "Clock In already recorded for {$user_name} (ID: {$employee_id}). Please Clock Out to finish.";
                    $message_is_success = false;
                    $show_time_out = true;
                } else {
                    $message = "Attendance already completed for {$user_name} (ID: {$employee_id}) today.";
                    $message_is_success = false;
                    $show_time_out = false;
                }
            }
        } else {
            $message = "Invalid Employee/User ID (ID: {$employee_id}) for this company.";
            $message_is_success = false;
        }
    }

    $_SESSION['attendance_flash'] = [
        'message' => $message,
        'is_success' => $message_is_success,
        'show_time_out' => $show_time_out,
        'employee_id' => $employee_id,
    ];

    header('Location: employee_login.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Employee Attendance</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Apply saved theme immediately -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css      ">
    <style>
        /* --- Reuse your existing CSS variables from dashboard_admin.php --- */
        :root {
            --bg-primary: #f0f4f8;
            --bg-secondary: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --border-light: #f1f5f9;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-modal: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #10b981;
            --success-bg: #d1fae5;
            --danger: #ef4444;
            --danger-bg: #fee2e2;
            --warning: #f59e0b;
            --info: #0ea5e9;
            --sidebar-width: 240px;
            --transition: all 0.2s ease;
            --radius: 0.5rem;
            --radius-sm: 0.25rem;
            --radius-lg: 0.75rem;
        }
        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border-color: #334155;
            --border-light: #1e293b;
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
            --shadow-modal: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --success: #34d399;
            --success-bg: rgba(16, 185, 129, 0.2);
            --danger: #f87171;
            --danger-bg: rgba(239, 68, 68, 0.2);
            --warning: #fbbf24;
            --info: #7dd3fc;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            display: flex; /* Flex for sidebar + main */
            min-height: 100vh;
            transition: var(--transition);
        }
        /* --- Sidebar Styles (Minimal for employee login) --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-secondary);
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: var(--transition);
            box-shadow: var(--shadow-card);
            border-right: 1px solid var(--border-color);
        }
        .sidebar-header {
            padding: 1.5rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
        }
        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .sidebar-logo-icon {
            width: 2.5rem;
            height: 2.5rem;
            background: var(--primary);
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.125rem;
        }
        .sidebar-user {
            margin-top: 0.5rem;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            overflow-y: auto;
        }
        .nav-section {
            margin-bottom: 1.5rem;
        }
        .nav-section-title {
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            color: var(--text-muted);
            padding: 0 0.75rem 0.25rem;
            margin-bottom: 0.5rem;
            letter-spacing: 0.5px;
        }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--text-secondary);
            text-decoration: none;
            transition: var(--transition);
            border-radius: var(--radius-sm);
            margin: 0.125rem 0;
            font-size: 0.9375rem;
            font-weight: 500;
            gap: 0.75rem;
        }
        .nav-item:hover {
            background: var(--border-light);
            color: var(--text-primary);
        }
        .nav-item.active {
            background: var(--primary);
            color: white;
        }
        .nav-item.active:hover {
            background: var(--primary-hover);
        }
        .nav-icon {
            font-size: 1.125rem;
            width: 1.5rem;
            text-align: center;
        }
        .sidebar-footer {
            padding: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .logout-btn {
            width: 100%;
            background: var(--danger);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .logout-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        /* --- Main Content Styles --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        header {
            background: var(--bg-secondary);
            padding: 1.25rem 2rem;
            border-bottom: 1px solid var(--border-color);
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            position: sticky;
            top: 0;
            z-index: 900;
        }
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-greeting {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .header-subtitle {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .theme-toggle {
            width: 2.5rem;
            height: 2.5rem;
            border-radius: var(--radius);
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text-primary);
            font-size: 1.125rem;
            flex-shrink: 0; /* Prevents shrinking */
        }
        .theme-toggle:hover {
            background: var(--border-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        .search-box {
            position: relative;
            width: 100%; /* Make search box flexible */
            max-width: 20rem; /* Limit width on larger screens */
        }
        .search-box input {
            padding: 0.5rem 3rem 0.5rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            width: 100%;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: var(--transition);
        }
        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .search-icon {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }
        .content {
            padding: 2rem;
            flex: 1;
            background-color: var(--bg-primary);
            overflow-y: auto;
        }
        /* --- Employee Attendance Specific Styles --- */
        .attendance-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 2rem;
            max-width: 600px;
            margin: 0 auto;
            box-shadow: var(--shadow-card);
        }
        .attendance-card h2 {
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .form-group {
            margin-bottom: 1.25rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: var(--transition);
        }
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 0.9375rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-clock-in {
            background: var(--success);
            color: white;
        }
        .btn-clock-in:hover {
            background: #0da271;
        }
        .btn-clock-out {
            background: var(--danger);
            color: white;
        }
        .btn-clock-out:hover {
            background: #dc2626;
        }
        .message {
            padding: 0.75rem;
            border-radius: var(--radius);
            margin-top: 1rem;
            text-align: center;
            font-weight: 500;
        }
        .message.success {
            background: var(--success-bg);
            color: #065f46;
        }
        .message.error {
            background: var(--danger-bg);
            color: #991b1b;
        }
        .current-time {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 1.5rem;
            font-family: 'Courier New', monospace;
        }
        /* --- Authentication Links Styles --- */
        .auth-links {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.9375rem;
            color: var(--text-secondary);
        }
        .auth-links p {
            margin: 0.5rem 0;
        }
        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: var(--transition);
        }
        .auth-links a:hover {
            text-decoration: underline;
            color: var(--primary-hover);
        }
        .auth-links a:nth-of-type(2) {
            color: var(--info);
        }
        .auth-links a:nth-of-type(2):hover {
            color: #3b82f6; /* Adjust hover color for info link if needed */
        }
        /* --- Responsive Adjustments --- */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            .sidebar-logo span,
            .sidebar-user,
            .nav-section-title,
            .nav-item span {
                display: none;
            }
            .sidebar-logo {
                justify-content: center;
            }
            .nav-item {
                justify-content: center;
                padding: 1rem 0;
            }
            .nav-icon {
                margin-right: 0;
            }
            .main-content {
                margin-left: 70px;
            }
            .header-content {
                 flex-direction: column;
                 align-items: flex-start;
                 gap: 1rem;
            }
            .header-actions {
                 width: 100%;
                 justify-content: space-between;
            }
            .search-box {
                 max-width: 100%;
            }
            .content {
                 padding: 1.5rem;
            }
            .button-group {
                 flex-direction: column;
            }
            .auth-links a {
                 display: block;
                 margin: 0.5rem 0;
            }
        }
        /* --- Focus Styles --- */
        button:focus,
        input:focus,
        select:focus,
        textarea:focus,
        a:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
        }
        .nav-item:focus {
            outline: 2px solid var(--primary);
            outline-offset: 2px;
            border-radius: var(--radius-sm);
        }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <i class="fas fa-clock"></i> <!-- Changed icon for attendance -->
            </div>
            <span>Company Attendance</span> <!-- Generic company label -->
        </div>
        <div class="sidebar-user">Employee Portal</div> <!-- Changed user context -->
    </div>
    <nav class="sidebar-nav">
        <!-- Removed the 'Admin Login' link -->
        <div class="nav-section">
            <div class="nav-section-title">Attendance</div>
            <a href="#" class="nav-item active"> <!-- Current page -->
                <i class="fas fa-clock nav-icon"></i>
                <span>Time Clock</span>
            </a>
        </div>
    </nav>
    <!-- No logout needed for public-facing kiosk -->
</div>
<div class="main-content">
    <header>
        <div class="header-content">
            <div>
                <div class="header-greeting">
                    <?php
                    $hour = date('H');
                    $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                    echo $greeting . ', Employee';
                    ?>
                </div>
                <div class="header-subtitle">
                    <?= date('l, F j, Y') ?>
                </div>
            </div>
            <div class="header-actions">
                <button class="theme-toggle" onclick="toggleTheme()" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                <!-- Search box might not be relevant here -->
            </div>
        </div>
    </header>
    <div class="content">
        <div class="attendance-card">
            <h2><i class="fas fa-clock"></i> Employee Time Clock</h2>
            <div class="current-time" id="currentTime">00:00:00</div>

            <form method="POST">
                <input type="hidden" name="kiosk_submit" value="1">
                <div class="form-group">
                    <label for="employee_id"><i class="fas fa-id-card"></i> Employee/User ID</label>
                    <input type="text" id="employee_id" name="employee_id" placeholder="Enter your employee or user ID" value="<?= htmlspecialchars($prefillEmployeeId) ?>" required>
                </div>
                <div class="button-group">
                <?php if ($show_time_out): ?>
                    <button type="submit" name="time_out" class="btn btn-clock-out">
                        <i class="fas fa-sign-out-alt"></i> Clock Out
                    </button>
                <?php else: ?>
                    <button type="submit" name="time_in" class="btn btn-clock-in">
                        <i class="fas fa-sign-in-alt"></i> Clock In
                    </button>
                <?php endif; ?>
             </div>
            </form>

            <?php if ($message): ?>
                <div class="message <?= $message_is_success ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <footer class="footer-spacer"></footer>
</div>

<script>
    // --- Theme Toggle Logic (Copied from dashboard_admin.php) ---
    function toggleTheme() {
        const html = document.documentElement;
        const themeToggle = document.getElementById('themeToggle');
        const icon = themeToggle.querySelector('i');
        const currentTheme = html.getAttribute('data-theme');
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        html.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);

        icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';

        // Add subtle animation
        icon.style.transform = 'rotate(180deg)';
        setTimeout(() => {
            icon.className = newTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            icon.style.transform = 'rotate(0deg)';
        }, 150);
    }

    // Load saved theme on page load
    document.addEventListener('DOMContentLoaded', () => {
        const savedTheme = localStorage.getItem('theme') || 'light';
        const html = document.documentElement;
        const themeToggle = document.getElementById('themeToggle');
        const icon = themeToggle.querySelector('i');

        html.setAttribute('data-theme', savedTheme);
        icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
    });

    // --- Clock Update Logic ---
    function updateTime() {
        const now = new Date();
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds}`;
    }

    updateTime();
    setInterval(updateTime, 1000);


    // --- Dynamic button show (check server for existing record) ---
    (function(){
      const empInput = document.getElementById('employee_id');
      const buttonGroup = document.querySelector('.button-group');
      if (!empInput || !buttonGroup) return;

      let timer = null;
      function renderButton(showTimeOut) {
        if (showTimeOut) {
          buttonGroup.innerHTML = '<button type="submit" name="time_out" class="btn btn-clock-out"><i class="fas fa-sign-out-alt"></i> Clock Out</button>';
        } else {
          buttonGroup.innerHTML = '<button type="submit" name="time_in" class="btn btn-clock-in"><i class="fas fa-sign-in-alt"></i> Clock In</button>';
        }
      }

      async function checkStatus() {
        const e = empInput.value.trim();
        if (!e) return;
        try {
          const url = `employee_login.php?check=1&employee_id=${encodeURIComponent(e)}`;
          const res = await fetch(url, { credentials: 'same-origin' });
          if (!res.ok) return;
          const data = await res.json();
          if (data && data.ok) {
            renderButton(Boolean(data.show_time_out));
          }
        } catch (err) {
          console.error(err);
        }
      }

      function scheduleCheck() {
        clearTimeout(timer);
        timer = setTimeout(checkStatus, 350);
      }

      empInput.addEventListener('input', scheduleCheck);
      empInput.addEventListener('blur', checkStatus);

            // Ensure correct button is shown if the browser autofills the ID.
            checkStatus();
    })();
</script>
</body>
</html>