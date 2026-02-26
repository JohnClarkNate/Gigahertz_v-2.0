<?php
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if company code already exists
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE code = ?");
    $stmt->execute([$code]);
    if ($stmt->fetch()) {
        // Set error message
        $message = "<p>Company code already exists. Choose a unique one.</p>";
        $message_type = "error"; // Define message type
    } else {
        // Create company
        $stmt = $pdo->prepare("INSERT INTO companies (code, name, email) VALUES (?, ?, ?)");
        $stmt->execute([$code, $name, $email]);
        $company_id = $pdo->lastInsertId();

        // Create admin user
        $stmt = $pdo->prepare("INSERT INTO users (company_id, username, password, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$company_id, $username, $password]);

        // Set success message
        $message = "<p>Admin registered successfully. <a href='login.php'>Login here</a></p>";
        $message_type = "success"; // Define message type
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Company & Admin Registration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

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
            background: linear-gradient(135deg, var(--bg-primary), var(--bg-secondary));
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            transition: var(--transition);
            overflow: hidden; /* Prevent scrollbars during animation */
        }
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-card {
            background: var(--bg-secondary);
            border-radius: var(--radius-lg);
            border: 1px solid var(--border-color);
            padding: 3rem;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-card);
            position: relative;
            z-index: 2; /* Ensure card is above background elements */
            animation: fadeInUp 0.6s ease-out;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            font-size: 2.25rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            background: linear-gradient(90deg, var(--primary), var(--info));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-fill-color: transparent;
        }
        .login-header p {
            color: var(--text-secondary);
            font-size: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--text-primary);
            font-size: 0.9375rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.5rem; /* Add padding for icon */
            border: 1px solid var(--border-color);
            border-radius: var(--radius);
            font-size: 0.9375rem;
            background: var(--bg-secondary);
            color: var(--text-primary);
            transition: var(--transition);
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .form-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.125rem;
        }
        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: var(--radius);
            font-weight: 600;
            cursor: pointer;
            transition: var(--transition);
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }
        .btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--primary-hover));
            color: white;
        }
        .btn-primary:hover {
            background: linear-gradient(90deg, var(--primary-hover), var(--primary));
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }
        .login-footer {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        .login-footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
        /* --- Message Styles --- */
        .message {
            padding: 0.875rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 500;
            animation: shake 0.5s ease-in-out;
        }
        .error-message {
            color: var(--danger);
            background: var(--danger-bg);
            border-left: 4px solid var(--danger);
        }
        .success-message {
            color: var(--success);
            background: var(--success-bg);
            border-left: 4px solid var(--success);
        }
        .theme-toggle {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
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
            z-index: 10;
        }
        .theme-toggle:hover {
            background: var(--border-light);
            border-color: var(--primary);
            color: var(--primary);
        }
        /* --- Background Elements for Visual Interest --- */
        .bg-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }
        .shape {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--info));
            opacity: 0.1;
            animation: float 15s infinite ease-in-out;
        }
        .shape:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }
        .shape:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -80px;
            right: 20%;
            animation-delay: -5s;
        }
        .shape:nth-child(3) {
            width: 150px;
            height: 150px;
            top: 40%;
            right: -50px;
            animation-delay: -10s;
        }
        /* --- Animations --- */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes float {
            0%, 100% {
                transform: translate(0, 0);
            }
            25% {
                transform: translate(-20px, -20px);
            }
            50% {
                transform: translate(20px, 20px);
            }
            75% {
                transform: translate(20px, -20px);
            }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        /* --- Responsive Adjustments --- */
        @media (max-width: 768px) {
            .login-card {
                margin: 1.5rem;
                padding: 2rem 1.5rem;
            }
            .theme-toggle {
                 top: 1rem;
                 right: 1rem;
            }
            .login-header h1 {
                 font-size: 1.75rem;
            }
            .bg-shapes {
                 display: none; /* Hide shapes on mobile for performance */
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
    </style>
</head>
<body>
<div class="bg-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<div class="theme-toggle" onclick="toggleTheme()" id="themeToggle">
    <i class="fas fa-moon"></i>
</div>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <h2><i class="fas fa-users"></i> Company & Admin Registration</h2>
            <p>Create your company and admin account</p>
        </div>

        <?php if (isset($message)): ?>
            <!-- Use the appropriate class based on message type -->
            <div class="message <?= $message_type ?>-message">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label for="code"><i class="fas fa-building"></i> Company Code</label>
                <div style="position: relative;">
                    <i class="fas fa-building form-icon"></i>
                    <input type="text" id="code" name="code" placeholder="Enter company code" required>
                </div>
            </div>

            <div class="form-group">
                <label for="name"><i class="fas fa-user-tie"></i> Company Name</label>
                <div style="position: relative;">
                    <i class="fas fa-user-tie form-icon"></i>
                    <input type="text" id="name" name="name" placeholder="Enter company name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email"><i class="fas fa-envelope"></i> Company Email</label>
                <div style="position: relative;">
                    <i class="fas fa-envelope form-icon"></i>
                    <input type="email" id="email" name="email" placeholder="Enter company email" required>
                </div>
            </div>

            <div class="form-group">
                <label for="username"><i class="fas fa-user"></i> Admin Username</label>
                <div style="position: relative;">
                    <i class="fas fa-user form-icon"></i>
                    <input type="text" id="username" name="username" placeholder="Enter admin username" required>
                </div>
            </div>

            <div class="form-group">
                <label for="password"><i class="fas fa-key"></i> Admin Password</label>
                <div style="position: relative;">
                    <i class="fas fa-key form-icon"></i>
                    <input type="password" id="password" name="password" placeholder="Enter admin password" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Register Company & Admin
            </button>
        </form>

        <div class="login-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <!-- Added Employee Login Text -->
            <p>Employee? <a href="employee_login.php">Login here</a></p>
        </div>
    </div>
</div>

<script>
    // --- Theme Toggle Logic ---
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
</script>
</body>
</html>