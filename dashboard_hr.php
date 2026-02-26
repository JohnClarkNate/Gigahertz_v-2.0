<?php
session_start();
ob_start(); // Start output buffering
require 'db.php';

// - Session Check -
if (!isset($_SESSION['user']) || !in_array($_SESSION['role'], ['head_hr'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}

$section = $_GET['section'] ?? 'attendance'; // Default to attendance
$company_id = $_SESSION['company_id'];
$hrDuplicateMessage = null;
$employeeFormDefaults = [
    'employee_id' => '',
    'name' => '',
    'date_hired' => date('Y-m-d')
];

// - Handle Payroll Actions -
if ($section === 'payroll') {
    // Add Payroll Entry
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_payroll'])) {
        $employee_id = $_POST['employee_id'];
        $pay_period_start = $_POST['pay_period_start'];
        $pay_period_end = $_POST['pay_period_end'];
        $base_salary = (float) ($_POST['base_salary'] ?? 0);
        $overtime_pay = (float) ($_POST['overtime_pay'] ?? 0);
        $allowances = (float) ($_POST['allowances'] ?? 0);
        $deductions = (float) ($_POST['deductions'] ?? 0);
        $net_salary = $base_salary + $overtime_pay + $allowances - $deductions;

        // Verify employee exists in the company (check both hr and users tables)
        $stmt = $pdo->prepare("SELECT id FROM hr WHERE employee_id = ? AND company_id = ?");
        $stmt->execute([$employee_id, $company_id]);
        $emp = $stmt->fetch();

        if (!$emp) {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE employee_id = ? AND company_id = ?");
            $stmt->execute([$employee_id, $company_id]);
            $emp = $stmt->fetch();
        }

        if ($emp) {
            $stmt = $pdo->prepare("INSERT INTO payroll (employee_id, company_id, pay_period_start, pay_period_end, base_salary, overtime_pay, allowances, deductions, net_salary) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$employee_id, $company_id, $pay_period_start, $pay_period_end, $base_salary, $overtime_pay, $allowances, $deductions, $net_salary]);
            header("Location: dashboard_hr.php?section=payroll");
            exit();
        } else {
            $payroll_message = "Employee ID {$employee_id} not found in this company.";
        }
    }

    // Fetch Payroll Data for Display
    $stmt = $pdo->prepare("
        SELECT p.*, COALESCE(h.name, u.username) as employee_name
        FROM payroll p
        LEFT JOIN hr h ON p.employee_id = h.employee_id AND p.company_id = h.company_id
        LEFT JOIN users u ON p.employee_id = u.employee_id AND p.company_id = u.company_id
        WHERE p.company_id = ?
        ORDER BY p.pay_period_start DESC
    ");
    $stmt->execute([$company_id]);
    $payroll_data = $stmt->fetchAll();
}

// Handle edit attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_attendance'])) {
    $attendance_id = $_POST['attendance_id'];
    $time_in = $_POST['time_in'];
    $time_out = $_POST['time_out'] ?: null; // Use null if empty string

    $stmt = $pdo->prepare("UPDATE attendance SET time_in = ?, time_out = ? WHERE id = ? AND company_id = ?");
    $stmt->execute([$time_in, $time_out, $attendance_id, $company_id]);

    // Redirect back to the same page with the current filter date
    $redirect_url = "dashboard_hr.php?section=attendance" . (isset($_GET['filter_date']) ? "&filter_date=" . $_GET['filter_date'] : "");
    header("Location: $redirect_url");
    exit();
}

// Handle delete attendance
if (isset($_GET['delete_attendance'])) {
    $delete_id = $_GET['delete_attendance'];
    $stmt = $pdo->prepare("DELETE FROM attendance WHERE id = ? AND company_id = ?");
    $stmt->execute([$delete_id, $company_id]);
    header("Location: dashboard_hr.php?section=attendance" . (isset($_GET['filter_date']) ? "&filter_date=" . $_GET['filter_date'] : ""));
    exit();
}

// Handle edit employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_employee'])) {
    $employee_db_id = $_POST['employee_db_id'];
    $employee_id = $_POST['employee_id'];
    $name = $_POST['name'];
    $date_hired = $_POST['date_hired'];

    $stmt = $pdo->prepare("UPDATE hr SET employee_id = ?, name = ?, date_hired = ? WHERE id = ? AND company_id = ?");
    $stmt->execute([$employee_id, $name, $date_hired, $employee_db_id, $company_id]);
    header("Location: dashboard_hr.php?section=employees");
    exit();
}

// Handle delete employee
if (isset($_GET['delete_employee'])) {
    $delete_id = $_GET['delete_employee'];
    $stmt = $pdo->prepare("DELETE FROM hr WHERE id = ? AND company_id = ?");
    $stmt->execute([$delete_id, $company_id]);
    header("Location: dashboard_hr.php?section=employees");
    exit();
}

// Add General Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_employee'])) {
    $employee_id = trim($_POST['employee_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $date_hired = $_POST['date_hired'] ?? '';

    $employeeFormDefaults = [
        'employee_id' => $employee_id,
        'name' => $name,
        'date_hired' => $date_hired !== '' ? $date_hired : date('Y-m-d'),
    ];

    if ($employee_id === '' || $name === '' || $date_hired === '') {
        $hrDuplicateMessage = 'Please complete all required fields before submitting.';
        $section = 'employees';
    } else {
        $duplicateSources = [];

        $dupStmt = $pdo->prepare("SELECT COUNT(*) FROM hr WHERE company_id = ? AND employee_id = ? LIMIT 1");
        $dupStmt->execute([$company_id, $employee_id]);
        if ($dupStmt->fetchColumn() > 0) {
            $duplicateSources[] = 'the HR module';
        }

        $dupStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE company_id = ? AND employee_id = ? LIMIT 1");
        $dupStmt->execute([$company_id, $employee_id]);
        if ($dupStmt->fetchColumn() > 0) {
            $duplicateSources[] = 'the user module';
        }

        if (!empty($duplicateSources)) {
            $duplicateLabel = count($duplicateSources) > 1
                ? implode(' and ', $duplicateSources)
                : $duplicateSources[0];
            $hrDuplicateMessage = sprintf('Employee ID %s already exists in %s. Please use another Employee ID.', $employee_id, $duplicateLabel);
            $section = 'employees';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO hr (company_id, employee_id, name, date_hired) VALUES (?, ?, ?, ?)");
                $stmt->execute([$company_id, $employee_id, $name, $date_hired]);
                header("Location: dashboard_hr.php?section=employees");
                exit();
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $hrDuplicateMessage = sprintf('Employee ID %s already exists. Please use another Employee ID.', $employee_id);
                    $section = 'employees';
                } else {
                    throw $e;
                }
            }
        }
    }
}

// Fetch data for the selected section
$attendance_records = [];
$employees = [];
$payroll_data = [];
$filter_date = null;
$total_employees = 0;
$head_count = 0;
$general_count = 0;

if ($section === 'attendance') {
    $filter_date = $_GET['filter_date'] ?? date('Y-m-d'); // Use provided date or today's date

    // Fetch Attendance Data for Display
    // Join with 'users' table to get names for 'head' type employees (heads)
    // Join with 'hr' table to get names for 'general' type employees (general staff)
    // The logic relies on the user_id being positive for heads and negative for general staff, as set by employee_login.php
    $attendance_query = "
        SELECT
            a.id,
            a.employee_id,
            COALESCE(u.username, h.name, a.employee_name) AS employee_name,
            a.time_in,
            a.time_out,
            a.employee_type,
            a.date,
            a.scheduled_start_time,
            a.scheduled_end_time,
            a.is_late,
            a.late_minutes,
            a.is_ot_without_pay,
            a.overtime_is_paid,
            a.ot_minutes,
            a.is_early_clockout,
            a.early_minutes
        FROM attendance a
        LEFT JOIN users u ON a.user_id = u.id AND a.user_id > 0 AND a.employee_type = 'head'
        LEFT JOIN hr h ON -a.user_id = h.id AND a.user_id < 0 AND a.employee_type = 'general'
        WHERE a.company_id = ?
        AND a.date = ?
        ORDER BY a.time_in ASC
    ";
    $stmt = $pdo->prepare($attendance_query);
    $stmt->execute([$company_id, $filter_date]);
    $attendance_records = $stmt->fetchAll();

    // Fetch head count and general employee count for summary
    $stmt = $pdo->prepare("SELECT COUNT(*) as head_count FROM users WHERE company_id = ? AND employee_id IS NOT NULL"); // Assuming heads are in users table with employee_id
    $stmt->execute([$company_id]);
    $head_count = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) as general_count FROM hr WHERE company_id = ?");
    $stmt->execute([$company_id]);
    $general_count = $stmt->fetchColumn();

    $total_employees = $head_count + $general_count;
} elseif ($section === 'employees') {
    $stmt = $pdo->prepare("SELECT * FROM hr WHERE company_id = ? ORDER BY name ASC");
    $stmt->execute([$company_id]);
    $employees = $stmt->fetchAll();
}

function formatMinutesToHours($minutes) {
    $hours = floor($minutes / 60);
    $remainingMinutes = $minutes % 60;
    return "{$hours}h {$remainingMinutes}m";
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>HR Dashboard</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Apply saved theme immediately -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css        ">
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
        /* --- Sidebar Styles --- */
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
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        .card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow-card);
            transition: var(--transition);
        }
        .card:hover {
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            padding: 1.5rem 1.5rem 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-body {
            padding: 1.5rem;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary);
            margin: 0.5rem 0;
        }
        .stat-label {
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        /* --- Data Table Styles --- */
        .table-container {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            margin-top: 1rem;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table th {
            text-align: left;
            padding: 0.75rem 1rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid var(--border-color);
            background: var(--bg-secondary);
        }
        .data-table td {
            padding: 1rem;
            font-size: 0.9375rem;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-primary);
        }
        .data-table tbody tr:hover {
            background: var(--border-light);
        }
        /* --- Action Buttons --- */
        .action-btn, .edit-btn {
            padding: 0.4rem 0.8rem;
            border-radius: var(--radius-sm);
            font-size: 0.8125rem;
            font-weight: 600;
            transition: var(--transition);
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border: none;
            cursor: pointer;
            text-decoration: none;
            justify-content: center;
            min-width: 70px;
        }
        .action-btn {
            background: var(--danger);
            color: white;
        }
        .action-btn:hover {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        .edit-btn {
            background: var(--primary);
            color: white;
            margin-right: 0.5rem;
        }
        .edit-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }
        /* --- Badge Styles --- */
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-active {
            background: var(--success-bg);
            color: var(--success);
        }
        .badge-inactive {
            background: var(--danger-bg);
            color: var,--danger;
        }
        .badge-head { /* Added specific class for Heads */
            background: var(--info);
            color: white;
        }
        .badge-general { /* Added specific class for General */
            background: var(--primary);
            color: white;
        }
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.5rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-late {
            background: var(--danger-bg);
            color: var(--danger);
        }
        .badge-early {
            background: rgba(245, 158, 11, 0.2);
            color: var(--warning);
        }
        .badge-ot-paid {
            background: var(--success-bg);
            color: var(--success);
        }
        .badge-ot-unpaid {
            background: rgba(14, 165, 233, 0.2);
            color: var(--info);
        }
        .badge-on-time {
            background: var(--success-bg);
            color: var(--success);
        }
        /* --- Modal Styles --- */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            backdrop-filter: blur(2px);
        }
        .modal-box {
            background: var(--bg-secondary);
            padding: 2rem;
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            width: 100%;
            max-width: 500px;
            box-shadow: var(--shadow-modal);
            position: relative;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-primary);
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--text-muted);
            cursor: pointer;
            transition: var(--transition);
        }
        .modal-close:hover {
            color: var(--text-primary);
        }
        .modal-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1.5rem;
        }
        .modal-actions button {
            padding: 0.75rem 1.5rem;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            border: none;
            font-size: 0.9375rem;
        }
        #confirmDeleteBtn {
            background: var(--danger);
            color: white;
        }
        #confirmDeleteBtn:hover {
            background: #dc2626;
        }
        .modal-actions button:last-child {
            background: var(--border-color);
            color: var(--text-primary);
        }
        .modal-actions button:last-child:hover {
            background: var(--text-muted);
        }
        /* --- Form Card --- */
        .form-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 1.5rem;
            box-shadow: var(--shadow-card);
            transition: var(--transition);
        }
        .form-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
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
        .form-group select,
        .form-group textarea {
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
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
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
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-primary:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .btn-secondary {
            background: var(--border-color);
            color: var(--text-primary);
        }
        .btn-secondary:hover {
            background: var(--text-secondary);
            color: var(--bg-secondary);
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
            .content-grid {
                grid-template-columns: 1fr; /* Stack on smaller screens */
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
        .error-message {
            color: var(--danger);
            background: var(--danger-bg);
            padding: 0.75rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="sidebar-logo-icon">
                    <i class="fas fa-user-tie"></i>
                </div>
                <span><?= htmlspecialchars($_SESSION['company'] ?? '') ?></span>
            </div>
            <!-- Changed this line: -->
            <div class="sidebar-user">Code: <?= htmlspecialchars($_SESSION['company_code']) ?></div>
        </div>
        <nav class="sidebar-nav">
            <div class="nav-section">
                <div class="nav-section-title">HR</div>
                <a href="?section=attendance" class="nav-item <?= $section === 'attendance' ? 'active' : '' ?>">
                    <i class="fas fa-clock nav-icon"></i>
                    <span>Attendance</span>
                </a>
                <a href="?section=employees" class="nav-item <?= $section === 'employees' ? 'active' : '' ?>">
                    <i class="fas fa-users nav-icon"></i>
                    <span>Employees</span>
                </a>
                <a href="?section=payroll" class="nav-item <?= $section === 'payroll' ? 'active' : '' ?>">
                    <i class="fas fa-money-bill-wave nav-icon"></i>
                    <span>Payroll</span>
                </a>
                <?php $filter_date_query = $filter_date ? '&filter_date=' . urlencode($filter_date) : ''; ?>

            </div>
        </nav>
        <div class="sidebar-footer">
            <form method="POST">
                <button type="submit" name="logout" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Sign Out
                </button>
            </form>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-content">
                <div>
                    <div class="header-greeting">
                        <?php
                        $hour = date('H');
                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                        echo $greeting . ', HR Head';
                        ?>
                    </div>
                    <div class="header-subtitle">
                        <?= date('l, F j, Y') ?>
                    </div>
                </div>
                <div class="header-actions">
                    <div class="search-box">
                        <input type="text" placeholder="Search...">
                        <i class="fas fa-search search-icon"></i>
                    </div>
                    <button class="theme-toggle" onclick="toggleTheme()" id="themeToggle">
                        <i class="fas fa-moon"></i>
                    </button>
                </div>
            </div>
        </header>

        <div class="content">
            <?php if ($section === 'attendance'): ?>
            <div class="content-grid">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-users"></i> Summary</div>
                    </div>
                    <div class="card-body">
                        <div class="stat-number"><?= $total_employees ?></div>
                        <div class="stat-label">Total Users/Employees</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-user-tie"></i> Heads</div>
                    </div>
                    <div class="card-body">
                        <div class="stat-number"><?= $head_count ?></div>
                        <div class="stat-label">Total Heads</div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <div class="card-title"><i class="fas fa-user"></i> General Staff</div>
                    </div>
                    <div class="card-body">
                        <div class="stat-number"><?= $general_count ?></div>
                        <div class="stat-label">Total General Staff</div>
                    </div>
                </div>
            </div>

            <div class="form-card">
                <div class="form-title">Filter Attendance</div>
                <form method="GET">
                    <input type="hidden" name="section" value="attendance">
                    <div class="form-group">
                        <label for="filter_date"><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" id="filter_date" name="filter_date" value="<?= htmlspecialchars($filter_date) ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Scheduled Shift</th>
                            <th>Time In</th>
                            <th>Time Out</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($attendance_records as $record): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($record['employee_id']) ?></strong></td>
                        <td><?= htmlspecialchars($record['employee_name']) ?></td>
                        <td>
                            <?php
                            $employee_type_code = $record['employee_type'] ?? '';
                            $display_type = $employee_type_code === 'head' ? 'Heads' : 'General';
                            $badge_class = $employee_type_code === 'head' ? 'head' : 'general';
                            ?>
                            <span class="badge badge-<?= $badge_class ?>"><?= htmlspecialchars($display_type) ?></span>
                        </td>
                        <td>
                            <?php
                            $schedStart = $record['scheduled_start_time'] ? date('h:i A', strtotime($record['scheduled_start_time'])) : null;
                            $schedEnd = $record['scheduled_end_time'] ? date('h:i A', strtotime($record['scheduled_end_time'])) : null;
                            ?>
                            <?php if ($schedStart && $schedEnd): ?>
                                <?= htmlspecialchars($schedStart . ' - ' . $schedEnd) ?>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['time_in']): ?>
                                <span style="color: var(--success);"><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($record['time_in'])) ?></span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['time_out']): ?>
                                <span style="color: var(--danger);"><i class="fas fa-clock"></i> <?= date('h:i A', strtotime($record['time_out'])) ?></span>
                            <?php elseif ($record['time_in']): ?>
                                <span style="color: var(--warning);"><i class="fas fa-clock"></i> Pending</span>
                            <?php else: ?>
                                <span style="color: var(--text-muted);">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $statusBadges = [];
                            if (!empty($record['is_late'])) {
                                $lateLabel = 'Late';
                                if (!empty($record['late_minutes'])) {
                                    $lateLabel .= ' ' . formatMinutesToHours((int)$record['late_minutes']);
                                }
                                $statusBadges[] = '<span class="badge-status badge-late"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($lateLabel) . '</span>';
                            }
                            if (!empty($record['is_early_clockout'])) {
                                $earlyLabel = 'Early Out';
                                if (!empty($record['early_minutes'])) {
                                    $earlyLabel .= ' ' . formatMinutesToHours((int)$record['early_minutes']);
                                }
                                $statusBadges[] = '<span class="badge-status badge-early"><i class="fas fa-hourglass-end"></i> ' . htmlspecialchars($earlyLabel) . '</span>';
                            }
                            if (!empty($record['overtime_is_paid']) && !empty($record['ot_minutes'])) {
                                $statusBadges[] = '<span class="badge-status badge-ot-paid"><i class="fas fa-coins"></i> OT ' . formatMinutesToHours((int)$record['ot_minutes']) . '</span>';
                            }
                            if (!empty($record['is_ot_without_pay']) && !empty($record['ot_minutes'])) {
                                $statusBadges[] = '<span class="badge-status badge-ot-unpaid"><i class="fas fa-clock"></i> OT (Unpaid) ' . formatMinutesToHours((int)$record['ot_minutes']) . '</span>';
                            }

                            if ($statusBadges) {
                                echo implode(' ', $statusBadges);
                            } else {
                                echo '<span class="badge-status badge-on-time"><i class="fas fa-check"></i> On Time</span>';
                            }
                            ?>
                        </td>
                        <td>
                            <button type="button" class="edit-btn" onclick='openEditModal(<?= json_encode($record, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i> Edit</button>
                            <button type="button" class="action-btn" onclick="confirmDelete(<?= $record['id'] ?>)"><i class="fas fa-trash-alt"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($section === 'employees'): ?>
            <div class="content-grid">
                <div>
                    <div class="form-card">
                        <div class="form-title">Add Employee</div>
                        <form method="POST">
                            <div class="form-group">
                                <label for="employee_id"><i class="fas fa-id-card"></i> Employee ID</label>
                                <input type="text" id="employee_id" name="employee_id" placeholder="Enter unique ID" value="<?= htmlspecialchars($employeeFormDefaults['employee_id'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="name"><i class="fas fa-user"></i> Name</label>
                                <input type="text" id="name" name="name" placeholder="Enter name" value="<?= htmlspecialchars($employeeFormDefaults['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="date_hired"><i class="fas fa-calendar-plus"></i> Date Hired</label>
                                <input type="date" id="date_hired" name="date_hired" value="<?= htmlspecialchars($employeeFormDefaults['date_hired'] ?? date('Y-m-d')) ?>" required>
                            </div>
                            <button type="submit" name="add_employee" class="btn btn-primary">Add Employee</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Date Hired</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($emp['employee_id']) ?></strong></td>
                        <td><?= htmlspecialchars($emp['name']) ?></td>
                        <td><?= htmlspecialchars($emp['date_hired']) ?></td>
                        <td>
                            <button type="button" class="edit-btn" onclick='openEditEmployeeModal(<?= json_encode($emp, JSON_HEX_APOS|JSON_HEX_QUOT) ?>)'><i class="fas fa-edit"></i> Edit</button>
                            <button type="button" class="action-btn" onclick="confirmDeleteEmployee(<?= $emp['id'] ?>)"><i class="fas fa-trash-alt"></i> Delete</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($section === 'payroll'): ?>
            <div class="content-grid">
                <div>
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><i class="fas fa-money-bill-wave"></i> Payroll Records</div>
                        </div>
                        <div class="table-container">
                            <?php if (isset($payroll_message)): ?>
                                <div class="error-message" style="margin: 1rem;"><?= htmlspecialchars($payroll_message) ?></div>
                            <?php endif; ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Employee ID</th>
                                        <th>Name</th>
                                        <th>Period Start</th>
                                        <th>Period End</th>
                                        <th>Base Salary</th>
                                        <th>Overtime</th>
                                        <th>Allowances</th>
                                        <th>Deductions</th>
                                        <th>Net Salary</th>
                                    </tr>
                                </thead>
                                <tbody id="hrPayrollTableBody">
                                <?php foreach ($payroll_data as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['employee_id']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['employee_name']) ?></td>
                                    <td><?= htmlspecialchars($p['pay_period_start']) ?></td>
                                    <td><?= htmlspecialchars($p['pay_period_end']) ?></td>
                                    <td>₱<?= number_format($p['base_salary'], 2) ?></td>
                                    <td>₱<?= number_format($p['overtime_pay'], 2) ?></td>
                                    <td>₱<?= number_format($p['allowances'], 2) ?></td>
                                    <td>₱<?= number_format($p['deductions'], 2) ?></td>
                                    <td>₱<?= number_format($p['net_salary'], 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Duplicate Employee Modal -->
    <div id="hrDuplicateModal" class="modal-overlay" style="display:none;">
        <div class="modal-box" style="max-width: 420px;">
            <div class="modal-header">
                <div class="modal-title" style="display:flex; align-items:center; gap:0.5rem;">
                    <i class="fas fa-triangle-exclamation" style="color: var(--danger);"></i>
                    Duplicate Employee ID
                </div>
                <button type="button" class="btn btn-primary" style="padding: 0.4rem 1rem; font-size: 0.85rem;" onclick="closeHrDuplicateModal()">Close</button>
            </div>
            <p class="duplicate-message" style="margin-bottom: 1rem; line-height: 1.5; color: var(--text-primary);"></p>
        </div>
    </div>

    <!-- Edit Attendance Modal -->
    <div id="editModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-edit"></i> Edit Attendance</div>
                <button class="modal-close" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" id="edit_attendance_id" name="attendance_id">
                <div class="form-group">
                    <label for="edit_time_in"><i class="fas fa-sign-in-alt"></i> Time In</label>
                    <input type="time" id="edit_time_in" name="time_in">
                </div>
                <div class="form-group">
                    <label for="edit_time_out"><i class="fas fa-sign-out-alt"></i> Time Out</label>
                    <input type="time" id="edit_time_out" name="time_out">
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" name="edit_attendance" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Attendance Confirmation Modal -->
    <div id="deleteModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i> Confirm Deletion</div>
                <button class="modal-close" onclick="closeDeleteModal()">&times;</button>
            </div>
            <p>Are you sure you want to delete this attendance record? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="confirmDeleteBtn">Yes, Delete</button>
                <button onclick="closeDeleteModal()">Cancel</button>
            </div>
        </div>
    </div>

    <!-- Edit Employee Modal -->
    <div id="editEmployeeModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-edit"></i> Edit Employee</div>
                <button class="modal-close" onclick="closeEditEmployeeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" id="edit_employee_db_id" name="employee_db_id">
                <div class="form-group">
                    <label for="edit_employee_id"><i class="fas fa-id-card"></i> Employee ID</label>
                    <input type="text" id="edit_employee_id" name="employee_id" required>
                </div>
                <div class="form-group">
                    <label for="edit_name"><i class="fas fa-user"></i> Name</label>
                    <input type="text" id="edit_name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="edit_date_hired"><i class="fas fa-calendar-plus"></i> Date Hired</label>
                    <input type="date" id="edit_date_hired" name="date_hired" required>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-secondary" onclick="closeEditEmployeeModal()">Cancel</button>
                    <button type="submit" name="edit_employee" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Employee Confirmation Modal -->
    <div id="deleteEmployeeModal" class="modal-overlay">
        <div class="modal-box">
            <div class="modal-header">
                <div class="modal-title"><i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i> Confirm Deletion</div>
                <button class="modal-close" onclick="closeDeleteEmployeeModal()">&times;</button>
            </div>
            <p>Are you sure you want to delete this employee? This action cannot be undone.</p>
            <div class="modal-actions">
                <button id="confirmDeleteEmployeeBtn">Yes, Delete</button>
                <button onclick="closeDeleteEmployeeModal()">Cancel</button>
            </div>
        </div>
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

        // --- Edit Modal Logic ---
        let deleteId = null;
        let deleteEmployeeId = null;

        function openEditModal(record) {
            document.getElementById('edit_attendance_id').value = record.id;
            // Format time for input (HH:MM)
            document.getElementById('edit_time_in').value = record.time_in ? record.time_in.slice(0, 5) : '';
            document.getElementById('edit_time_out').value = record.time_out ? record.time_out.slice(0, 5) : '';
            document.getElementById('editModal').style.display = 'flex';
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function confirmDelete(id) {
            deleteId = id;
            document.getElementById('deleteModal').style.display = 'flex';
        }

        function closeDeleteModal() {
            deleteId = null;
            document.getElementById('deleteModal').style.display = 'none';
        }

        function openEditEmployeeModal(record) {
            document.getElementById('edit_employee_db_id').value = record.id;
            document.getElementById('edit_employee_id').value = record.employee_id;
            document.getElementById('edit_name').value = record.name;
            document.getElementById('edit_date_hired').value = record.date_hired;
            document.getElementById('editEmployeeModal').style.display = 'flex';
        }

        function closeEditEmployeeModal() {
            document.getElementById('editEmployeeModal').style.display = 'none';
        }

        function confirmDeleteEmployee(id) {
            deleteEmployeeId = id;
            document.getElementById('deleteEmployeeModal').style.display = 'flex';
        }

        function closeDeleteEmployeeModal() {
            deleteEmployeeId = null;
            document.getElementById('deleteEmployeeModal').style.display = 'none';
        }

        function openHrDuplicateModal(message) {
            const modal = document.getElementById('hrDuplicateModal');
            if (!modal) return;
            const messageNode = modal.querySelector('.duplicate-message');
            if (messageNode) {
                messageNode.textContent = message;
            }
            modal.style.display = 'flex';
        }

        function closeHrDuplicateModal() {
            const modal = document.getElementById('hrDuplicateModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        const filterDateQuery = <?= json_encode($filter_date_query) ?>;

        document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
            if (deleteId) {
                let url = `dashboard_hr.php?delete_attendance=${deleteId}&section=attendance`;
                if (filterDateQuery) {
                    url += filterDateQuery;
                }
                window.location.href = url;
            }
        });

        document.getElementById('confirmDeleteEmployeeBtn').addEventListener('click', function() {
            if (deleteEmployeeId) {
                window.location.href = 'dashboard_hr.php?delete_employee=' + deleteEmployeeId + '&section=employees';
            }
        });

        // close modals on overlay click
        document.getElementById('editModal')?.addEventListener('click', function(e) { if(e.target===this) closeEditModal(); });
        document.getElementById('deleteModal')?.addEventListener('click', function(e) { if(e.target===this) closeDeleteModal(); });
        document.getElementById('editEmployeeModal')?.addEventListener('click', function(e) { if(e.target===this) closeEditEmployeeModal(); });
        document.getElementById('deleteEmployeeModal')?.addEventListener('click', function(e) { if(e.target===this) closeDeleteEmployeeModal(); });
        document.getElementById('hrDuplicateModal')?.addEventListener('click', function(e) { if(e.target===this) closeHrDuplicateModal(); });

        // --- Global search box filtering ---
        (function initHrSearchBox() {
            const debounce = (fn, wait) => {
                let t;
                return (...args) => {
                    clearTimeout(t);
                    t = setTimeout(() => fn.apply(null, args), wait);
                };
            };

            const ensureNoResultsRow = (table) => {
                const tbody = table.tBodies[0];
                if (!tbody) {
                    return null;
                }
                let marker = tbody.querySelector('.no-results-row');
                if (!marker) {
                    marker = document.createElement('tr');
                    marker.className = 'no-results-row';
                    marker.innerHTML = `<td colspan="${table.tHead ? table.tHead.rows[0].cells.length : tbody.rows[0]?.cells.length || 1}" style="text-align:center;padding:1rem;color:var(--text-secondary)">No matching results</td>`;
                    tbody.appendChild(marker);
                }
                return marker;
            };

            const filterTables = (input) => {
                const query = (input.value || '').trim().toLowerCase();
                const scope = input.closest('.main-content, .content') || document;
                let tables = Array.from(scope.querySelectorAll('.data-table'));
                if (!tables.length) {
                    tables = Array.from(document.querySelectorAll('.data-table'));
                }
                tables.forEach((table) => {
                    const tbody = table.tBodies[0];
                    if (!tbody) {
                        return;
                    }
                    const rows = Array.from(tbody.rows).filter((row) => !row.classList.contains('no-results-row'));
                    let visible = 0;
                    if (query === '') {
                        rows.forEach((row) => {
                            row.style.display = '';
                            visible++;
                        });
                    } else {
                        rows.forEach((row) => {
                            const match = row.textContent.toLowerCase().includes(query);
                            row.style.display = match ? '' : 'none';
                            if (match) {
                                visible++;
                            }
                        });
                    }
                    const marker = ensureNoResultsRow(table);
                    if (marker) {
                        marker.style.display = visible === 0 ? '' : 'none';
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', () => {
                const inputs = Array.from(document.querySelectorAll('.search-box input'));
                if (!inputs.length) {
                    return;
                }
                inputs.forEach((input) => {
                    const handler = debounce(() => filterTables(input), 160);
                    input.addEventListener('input', handler);
                    input.addEventListener('keydown', (event) => {
                        if (event.key === 'Enter') {
                            const scope = input.closest('.main-content, .content') || document;
                            const firstVisible = scope.querySelector('.data-table tbody tr:not(.no-results-row):not([style*="display: none"])');
                            if (firstVisible) {
                                firstVisible.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            }
                        }
                    });
                });
            });
        })();

        <?php if ($section === 'payroll'): ?>
        (function initPayrollSync() {
            const tableBody = document.getElementById('hrPayrollTableBody');
            if (!tableBody) {
                return;
            }

            const messageRow = (text) => {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 9;
                cell.textContent = text;
                cell.style.textAlign = 'center';
                row.appendChild(cell);
                return row;
            };

            const formatCurrency = (value) => {
                const num = Number(value) || 0;
                return '₱' + num.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            };

            const renderRows = (rows) => {
                tableBody.innerHTML = '';
                if (!rows || rows.length === 0) {
                    tableBody.appendChild(messageRow('No payroll records yet.'));
                    return;
                }

                rows.forEach((item) => {
                    const tr = document.createElement('tr');

                    const idCell = document.createElement('td');
                    const idStrong = document.createElement('strong');
                    idStrong.textContent = item.employee_id || '';
                    idCell.appendChild(idStrong);
                    tr.appendChild(idCell);

                    const cells = [
                        item.employee_name || '—',
                        item.pay_period_start || '—',
                        item.pay_period_end || '—',
                        formatCurrency(item.base_salary),
                        formatCurrency(item.overtime_pay),
                        formatCurrency(item.allowances),
                        formatCurrency(item.deductions),
                        formatCurrency(item.net_salary)
                    ];

                    cells.forEach((value) => {
                        const td = document.createElement('td');
                        td.textContent = value;
                        tr.appendChild(td);
                    });

                    tableBody.appendChild(tr);
                });
            };

            const fetchPayroll = () => {
                fetch('payroll_feed.php', { credentials: 'same-origin' })
                    .then((response) => {
                        if (!response.ok) {
                            throw new Error('Failed to fetch payroll records.');
                        }
                        return response.json();
                    })
                    .then((payload) => {
                        if (!payload.success) {
                            throw new Error(payload.message || 'Failed to load payroll records.');
                        }
                        renderRows(payload.data || []);
                    })
                    .catch((error) => {
                        console.error(error);
                        tableBody.innerHTML = '';
                        tableBody.appendChild(messageRow('Unable to refresh payroll records right now.'));
                    });
            };

            fetchPayroll();
            setInterval(fetchPayroll, 15000);
        })();
        <?php endif; ?>

        <?php if ($hrDuplicateMessage): ?>
        document.addEventListener('DOMContentLoaded', function() {
            openHrDuplicateModal(<?= json_encode($hrDuplicateMessage) ?>);
        });
        <?php endif; ?>
    </script>
</body>
</html>