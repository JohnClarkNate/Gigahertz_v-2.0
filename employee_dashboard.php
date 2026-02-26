<?php
session_start();
require 'db.php';

$error_message = "";
$employee_id = "";
$company_code = "";
$company_id = null;
$employee_name = "";
$attendance_data = [];
$payroll_data = [];

// --- Authentication Check ---
// Expect employee_id (eid) and company_code (cc) via GET parameters
// This simulates the "one click" from the clock-in success message.
if (isset($_GET['eid']) && isset($_GET['cc'])) {
    $employee_id = $_GET['eid'];
    $company_code = $_GET['cc'];

    // Validate company code
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE code = ?");
    $stmt->execute([$company_code]);
    $company = $stmt->fetch();

    if ($company) {
        $company_id = $company['id'];

        // Validate employee ID belongs to this company (check in HR table for general employees)
        $stmt = $pdo->prepare("SELECT name FROM hr WHERE employee_id = ? AND company_id = ?");
        $stmt->execute([$employee_id, $company_id]);
        $hr_employee = $stmt->fetch();

        if ($hr_employee) {
            $employee_name = $hr_employee['name'];
            // --- Fetch Employee Data ---

            // 1. Fetch Attendance Records for this employee
            // Find the attendance.user_id which is -hr.id for general employees
            $stmt = $pdo->prepare("SELECT a.* FROM attendance a JOIN hr h ON a.user_id = -h.id WHERE h.employee_id = ? AND h.company_id = ? ORDER BY a.date DESC, a.time_in DESC");
            $stmt->execute([$employee_id, $company_id]);
            $attendance_data = $stmt->fetchAll();

            // 2. Fetch Payroll Records for this employee
            $stmt = $pdo->prepare("SELECT * FROM payroll WHERE employee_id = ? AND company_id = ? ORDER BY pay_period_start DESC");
            $stmt->execute([$employee_id, $company_id]);
            $payroll_data = $stmt->fetchAll();


        } else {
            $error_message = "Invalid Employee ID or not found in this company.";
        }
    } else {
        $error_message = "Invalid Company Code.";
    }
} else {
    $error_message = "Access Denied. Please clock in first or use a valid link.";
}

// If authentication failed, show error and stop further processing
if ($error_message) {
    // You might want to redirect to login or show an error page instead
    // header("Location: employee_login.php");
    // exit();
    // For now, we'll just display the error on this page.
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>My Records</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Apply saved theme immediately -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
       /* --- Reuse your existing CSS variables from employee_login.php/dashboard_admin.php --- */
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
        /* --- Sidebar Styles (Minimal for employee records) --- */
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
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
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
            flex-shrink: 0;
        }
        .theme-toggle:hover {
            background: var(--border-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        .search-box {
            position: relative;
            width: 100%;
            max-width: 20rem;
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
        /* --- Card Styles --- */
        .card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow-card);
            transition: var(--transition);
            margin-bottom: 2rem; /* Space between cards */
            display: none; /* Initially hidden */
        }
        .card.active {
            display: block; /* Show when active */
        }
        .card:hover {
             box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }
        .card-header {
            padding: 1.25rem;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--bg-secondary);
        }
        .card-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .card-title i {
            font-size: 1.125rem;
            color: var(--primary);
        }
        .card-badge {
            background: var(--primary);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: var(--radius);
            font-size: 0.75rem;
            font-weight: 600;
        }
        .card-body {
            padding: 1.25rem;
        }
        /* --- Table Styles --- */
        .table-container {
            overflow-x: auto;
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
            color: var(--danger);
        }
        .badge-head {
            background: var(--info);
            color: white;
        }
        .badge-general {
            background: var(--primary);
            color: white;
        }
        /* --- Error Message --- */
        .error-message {
            color: var(--danger);
            background: var(--danger-bg);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 500;
        }
        /* --- Responsive Adjustments --- */
        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                overflow: hidden;
            }
            .sidebar-logo span,
            .sidebar-user, /* Truncate user name on small screens */
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
                <i class="fas fa-user"></i> <!-- Icon for employee -->
            </div>
            <span>My Dashboard</span> <!-- Generic dashboard label -->
        </div>
        <!-- Show employee name/company code -->
        <div class="sidebar-user" title="<?= htmlspecialchars($employee_name . ' (' . $employee_id . ') - ' . $company_code) ?>"><?= htmlspecialchars($employee_name) ?></div>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-section-title">My Information</div>
            <!-- Link to My Records (Attendance) -->
            <a href="#" class="nav-item" data-target="attendance-section"> <!-- JS-controlled link -->
                <i class="fas fa-clock nav-icon"></i>
                <span>My Records</span>
            </a>
            <!-- Link to My Payroll Records -->
            <a href="#" class="nav-item" data-target="payroll-section"> <!-- JS-controlled link -->
                <i class="fas fa-money-bill-wave nav-icon"></i>
                <span>My Payroll</span>
            </a>
            <!-- Link to Clock In/Out -->
             <a href="employee_login.php" class="nav-item">
                <i class="fas fa-sign-in-alt nav-icon"></i>
                <span>Clock In/Out</span>
            </a>
        </div>
    </nav>
    <!-- No logout needed for public-facing kiosk/simple view -->
</div>
<div class="main-content">
    <header>
        <div class="header-content">
            <div>
                <div class="header-greeting">
                    Welcome, <?= htmlspecialchars($employee_name) ?>
                </div>
                <div class="header-subtitle">
                    Employee ID: <?= htmlspecialchars($employee_id) ?> | Company: <?= htmlspecialchars($company_code) ?>
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
        <?php if ($error_message): ?>
            <div class="error-message">
                <?= htmlspecialchars($error_message) ?>
                <br><br>
                <a href="employee_login.php" style="color: inherit; text-decoration: underline;">Go back to Clock In/Out</a>
            </div>
        <?php else: ?>
            <!-- Attendance Records Section -->
            <div class="card active" id="attendance-section"> <!-- Added ID and default active class -->
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-history"></i> My Attendance Records
                    </div>
                    <span class="card-badge"><?= count($attendance_data) ?> Records</span>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <?php if (empty($attendance_data)): ?>
                            <p>No attendance records found.</p>
                        <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time In</th>
                                    <th>Time Out</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($attendance_data as $record): ?>
                                <tr>
                                    <td><?= htmlspecialchars($record['date']) ?></td>
                                    <td>
                                        <?php if ($record['time_in']): ?>
                                            <span style="color: var(--success);"><i class="fas fa-sign-in-alt"></i> <?= date('h:i A', strtotime($record['time_in'])) ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['time_out']): ?>
                                            <span style="color: var(--danger);"><i class="fas fa-sign-out-alt"></i> <?= date('h:i A', strtotime($record['time_out'])) ?></span>
                                        <?php elseif ($record['time_in']): ?>
                                            <span style="color: var(--warning);"><i class="fas fa-clock"></i> Pending</span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($record['time_in'] && !$record['time_out']): ?>
                                            <span class="badge badge-active"><i class="fas fa-clock"></i> Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive"><i class="fas fa-check-circle"></i> Closed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Payroll Records Section -->
            <div class="card" id="payroll-section"> <!-- Added ID -->
                <div class="card-header">
                    <div class="card-title">
                        <i class="fas fa-money-bill-wave"></i> My Payroll Records
                    </div>
                    <span class="card-badge"><?= count($payroll_data) ?> Records</span>
                </div>
                <div class="card-body">
                     <div class="table-container">
                        <?php if (empty($payroll_data)): ?>
                            <p>No payroll records found.</p>
                        <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Period Start</th>
                                    <th>Period End</th>
                                    <th>Base Salary</th>
                                    <th>Overtime</th>
                                    <th>Allowances</th>
                                    <th>Deductions</th>
                                    <th>Net Salary</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payroll_data as $p): ?>
                                <tr>
                                    <td><?= htmlspecialchars($p['pay_period_start']) ?></td>
                                    <td><?= htmlspecialchars($p['pay_period_end']) ?></td>
                                    <td>₱<?= number_format($p['base_salary'], 2) ?></td>
                                    <td>₱<?= number_format($p['overtime_pay'], 2) ?></td>
                                    <td>₱<?= number_format($p['allowances'], 2) ?></td>
                                    <td>₱<?= number_format($p['deductions'], 2) ?></td>
                                    <td><strong>₱<?= number_format($p['net_salary'], 2) ?></strong></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
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

    // --- Section Switching Logic ---
    document.addEventListener('DOMContentLoaded', () => {
        const navLinks = document.querySelectorAll('.nav-item[data-target]');
        const sections = document.querySelectorAll('.card[id]');

        function switchSection(targetId) {
            // Hide all sections
            sections.forEach(section => {
                section.classList.remove('active');
            });

            // Show the target section
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.classList.add('active');
            }

            // Update active state on nav links (optional visual feedback)
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('data-target') === targetId) {
                    link.classList.add('active');
                }
            });
        }

        // Add click event listeners to navigation links
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const targetId = this.getAttribute('data-target');
                if (targetId) {
                    switchSection(targetId);
                }
            });
        });

        // --- Initial State ---
        // By default, show the attendance section (it has 'active' class)
        // No need to explicitly call switchSection here as the CSS handles it.
        // If you want to default to payroll, you could call:
        // switchSection('payroll-section');
    });
</script>
</body>
</html>