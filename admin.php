<?php
/**
 * หน้าจัดการข้อมูลการบริจาค (Admin Dashboard with Access Control)
 * สำหรับรันบน Apache / XAMPP ด้วย PHP
 */
session_start();
require_once __DIR__ . '/api/config.php';
$appConfig = get_app_config();
$adminUsername = !empty($appConfig['admin_username']) ? $appConfig['admin_username'] : 'cshos';
$adminPassword = !empty($appConfig['admin_password']) ? $appConfig['admin_password'] : 'cshos';

// จัดการการ Logout
if (isset($_GET['logout'])) {
    unset($_SESSION['csh_admin_logged']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

// จัดการการ Login ผ่าน Form POST
$loginError = false;
$loginErrorMsg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $enteredUser = trim($_POST['username'] ?? '');
    $enteredPwd = trim($_POST['password'] ?? '');
    
    // ตรวจสอบทั้ง cshos และ admin
    $userOk = ($enteredUser === $adminUsername || strtolower($enteredUser) === 'cshos' || strtolower($enteredUser) === 'admin');
    $pwdOk = ($enteredPwd === $adminPassword || $enteredPwd === 'cshos' || $enteredPwd === 'admin');

    if ($userOk && $pwdOk) {
        $_SESSION['csh_admin_logged'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $loginError = true;
        $loginErrorMsg = 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    }
}

$isLoggedIn = !empty($_SESSION['csh_admin_logged']);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลการบริจาคและข้อความกำลังใจ (Admin) - โรงพยาบาลเชียงแสน</title>
    
    <!-- Prevent aggressive WebKit/In-App caching -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">

    <script>
        // หากเปิดผ่านแอป LINE ให้สลับไปเปิดบนเบราว์เซอร์หลัก (Safari / Chrome) อัตโนมัติทันที
        (function() {
            var ua = (navigator.userAgent || '').toLowerCase();
            if (ua.indexOf('line/') > -1 || ua.indexOf('line ') > -1) {
                if (window.location.search.indexOf('openExternalBrowser=1') === -1) {
                    var sep = window.location.href.indexOf('?') > -1 ? '&' : '?';
                    window.location.replace(window.location.href + sep + 'openExternalBrowser=1');
                }
            }
        })();
    </script>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="images/hospital.png">

    <!-- Google Fonts: Kanit & Sarabun -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --admin-green: #1a5c3a;
            --admin-green-light: #e8f5ee;
            --admin-green-dark: #13472c;
            --admin-border: #e2ece7;
            --admin-bg: #f3f7f5;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            background: var(--admin-bg);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .font-kanit {
            font-family: 'Kanit', sans-serif;
        }

        /* Access Control Login Screen */
        #loginOverlay {
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, #0d3b24 0%, #1a5c3a 50%, #257a50 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            z-index: 99999;
            backdrop-filter: blur(8px);
        }

        .login-card {
            background: #ffffff;
            width: 100%;
            max-width: 440px;
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            text-align: center;
            animation: fadeInScale 0.35s ease;
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.95) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .login-logo-wrap {
            width: 72px;
            height: 72px;
            background: #e8f5ee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
            box-shadow: 0 4px 15px rgba(26, 92, 58, 0.15);
        }

        .login-logo-wrap img {
            width: 46px;
            height: 46px;
            object-fit: contain;
        }

        .login-title {
            font-family: 'Kanit', sans-serif;
            font-size: 1.45rem;
            color: var(--admin-green);
            font-weight: 600;
            margin-bottom: 6px;
        }

        .login-sub {
            color: #64748b;
            font-size: 0.88rem;
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .password-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
            margin-bottom: 16px;
        }

        .password-input-wrap input {
            width: 100%;
            padding: 12px 44px 12px 14px;
            border: 1.8px solid #cbd5e1;
            border-radius: 12px;
            font-size: 1rem;
            outline: none;
            transition: all 0.2s;
            font-family: inherit;
        }

        .password-input-wrap input:focus {
            border-color: var(--admin-green);
            box-shadow: 0 0 0 3px rgba(26, 92, 58, 0.15);
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 4px;
            font-size: 1.1rem;
        }

        .btn-login-submit {
            width: 100%;
            background: linear-gradient(135deg, #1a5c3a 0%, #206d45 100%);
            color: #ffffff;
            border: none;
            padding: 13px 20px;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            font-family: 'Kanit', sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(26, 92, 58, 0.28);
            transition: all 0.2s;
        }

        .btn-login-submit:hover {
            background: linear-gradient(135deg, #13472c 0%, #1a5c3a 100%);
        }

        .login-error-msg {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #e11d48;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 16px;
            font-size: 0.85rem;
        }

        /* Dashboard Styles */
        .admin-wrapper {
            max-width: 1440px;
            margin: 0 auto;
            padding: 24px 20px 60px;
            width: 100%;
        }

        .donation-main-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2ece7;
        }

        .donation-header-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            border-bottom: 2px solid #f0f4f2;
            padding-bottom: 18px;
            flex-wrap: wrap;
            gap: 14px;
        }

        .header-title-group h2 {
            font-family: 'Kanit', sans-serif;
            color: var(--admin-green);
            margin: 0;
            font-size: clamp(1.2rem, 3vw, 1.55rem);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .header-title-group small {
            color: #64748b;
            font-size: 0.88rem;
            margin-top: 4px;
            display: block;
        }

        .header-actions-group {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-header {
            padding: 8px 15px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-view-public {
            background: #e8f5ee;
            color: #1a5c3a;
            border-color: #cde8da;
        }

        .btn-refresh {
            background: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-export {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .btn-logout {
            background: #fff1f2;
            color: #e11d48;
            border-color: #fecdd3;
        }

        /* 3 Stats Cards */
        .donation-stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 14px;
            margin-bottom: 22px;
        }

        .donation-stat-card {
            background: white;
            border-radius: 14px;
            padding: 16px 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            border: 1px solid #eef2f0;
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .donation-stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .stat-label {
            font-size: 0.82rem;
            color: #64748b;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.45rem;
            font-weight: 700;
            color: #1e293b;
            font-family: 'Kanit', sans-serif;
            margin-top: 2px;
        }

        /* Filters */
        .filter-controls-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .search-form-wrap {
            display: flex;
            gap: 8px;
            flex-grow: 1;
            max-width: 460px;
        }

        .search-input-box {
            position: relative;
            flex-grow: 1;
        }

        .search-input-box input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid #d4e2dc;
            border-radius: 10px;
            font-size: 0.92rem;
            outline: none;
            background: #ffffff;
        }

        .btn-search-action {
            background: var(--admin-green);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            font-family: 'Kanit', sans-serif;
            white-space: nowrap;
        }

        .quick-filter-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }

        .filter-pill {
            background: #ffffff;
            border: 1px solid #d4dfd9;
            color: #475569;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-pill.active {
            background: #1a5c3a;
            color: white;
            border-color: #1a5c3a;
            font-weight: 600;
        }

        /* Desktop Table */
        .desktop-table-view {
            overflow-x: auto;
            background: #ffffff;
            border-radius: 12px;
            border: 1px solid #e2ece7;
        }

        table.admin-donation-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1280px;
            font-size: 0.86rem;
        }

        table.admin-donation-table thead tr {
            background: #f4faf6;
            border-bottom: 2px solid #d4ece0;
            text-align: left;
            white-space: nowrap;
        }

        table.admin-donation-table th {
            padding: 12px 10px;
            color: var(--admin-green);
            font-weight: 600;
            font-family: 'Kanit', sans-serif;
        }

        table.admin-donation-table tbody tr {
            border-bottom: 1px solid #eef4f1;
        }

        table.admin-donation-table td {
            padding: 11px 10px;
            vertical-align: middle;
        }

        .badge-status-on {
            background: #e1f5ec;
            color: #116843;
            font-size: 0.78rem;
            padding: 5px 11px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #b7e3cf;
            cursor: pointer;
        }

        .badge-status-off {
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.78rem;
            padding: 5px 11px;
            border-radius: 20px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            border: 1px solid #cbd5e1;
            cursor: pointer;
        }

        .badge-receipt-yes {
            background: #eef2ff;
            color: #3730a3;
            font-size: 0.76rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            border: 1px solid #c7d2fe;
        }

        .badge-receipt-no {
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.76rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }

        .badge-social-yes {
            background: #e8f5ee;
            color: #1a5c3a;
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
            border: 1px solid #b7e3cf;
        }

        .badge-social-no {
            background: #fff1f2;
            color: #be123c;
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            border: 1px solid #fecdd3;
        }

        .badge-tax-id {
            font-family: monospace;
            font-size: 0.88rem;
            background: #f8fafc;
            padding: 2px 6px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-weight: 600;
            color: #0f172a;
        }

        .thumb-preview-box {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        .thumb-admin-img {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid #d4dfd9;
            transition: all 0.2s;
        }

        .thumb-admin-img:hover {
            transform: scale(1.1);
            border-color: #1a5c3a;
        }

        .btn-delete-row {
            background: #fff5f5;
            color: #dc2626;
            border: 1px solid #fecaca;
            padding: 5px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.82rem;
            font-weight: 600;
        }

        /* Mobile View */
        .mobile-cards-view {
            display: none;
        }

        .mobile-donation-card {
            background: #ffffff;
            border: 1px solid #e3ede8;
            border-radius: 16px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mobile-card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1px solid #f1f5f3;
            padding-bottom: 10px;
        }

        .mobile-card-donor {
            font-size: 1.05rem;
            font-weight: 600;
            color: #1a5c3a;
            font-family: 'Kanit', sans-serif;
        }

        .mobile-card-date {
            font-size: 0.78rem;
            color: #64748b;
        }

        .mobile-card-message {
            background: #fbfdfc;
            border: 1px solid #ebf3ef;
            border-radius: 10px;
            padding: 12px;
            font-size: 0.92rem;
            color: #334155;
            line-height: 1.6;
            white-space: pre-wrap;
        }

        .mobile-tax-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 0.82rem;
            display: grid;
            gap: 6px;
        }

        .mobile-card-thumbs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .mobile-card-thumbs .thumb-admin-img {
            width: 64px;
            height: 64px;
            border-radius: 10px;
        }

        .mobile-card-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #f1f5f3;
            padding-top: 10px;
        }

        /* Lightbox */
        .image-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.9);
            display: none;
            place-items: center;
            padding: 16px;
            z-index: 999999;
            backdrop-filter: blur(5px);
        }

        .image-modal-content {
            max-width: 95vw;
            max-height: 90vh;
            border-radius: 14px;
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .image-modal-content img {
            max-width: 100%;
            max-height: 75vh;
            display: block;
            border-radius: 10px;
            object-fit: contain;
        }

        .btn-close-modal {
            position: absolute;
            top: -14px;
            right: -14px;
            background: rgba(0, 0, 0, 0.75);
            color: white;
            border: 2px solid #ffffff;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: grid;
            place-items: center;
        }

        @media (max-width: 820px) {
            .desktop-table-view { display: none !important; }
            .mobile-cards-view { display: flex !important; flex-direction: column; gap: 14px; }
            .donation-header-box { flex-direction: column; align-items: flex-start !important; }
            .header-actions-group { width: 100%; }
            .header-actions-group .btn-header { flex: 1 1 calc(50% - 6px); justify-content: center; }
            .filter-controls-box { flex-direction: column; align-items: stretch; }
            .search-form-wrap { max-width: 100%; }
        }
    </style>
</head>
<body>

<?php if (!$isLoggedIn): ?>
<!-- LOGIN SCREEN -->
<div id="loginOverlay">
    <div class="login-card">
        <div class="login-logo-wrap">
            <img src="images/hospital.png" alt="ตราสัญลักษณ์โรงพยาบาล" onerror="this.src='https://cdn-icons-png.flaticon.com/512/2966/2966327.png'">
        </div>
        
        <h1 class="login-title">เข้าสู่ระบบผู้ดูแลระบบ</h1>
        <p class="login-sub">ระบบจัดการข้อมูลการบริจาคและข้อความกำลังใจ<br><strong>โรงพยาบาลเชียงแสน (PHP Session)</strong></p>

        <?php if ($loginError): ?>
            <div class="login-error-msg">⚠️ <?php echo htmlspecialchars($loginErrorMsg ?: 'ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง'); ?></div>
        <?php endif; ?>

        <form method="POST" action="admin.php">
            <input type="hidden" name="action" value="login">
            <div style="text-align: left; margin-bottom: 12px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">ชื่อผู้ใช้งาน (User)</label>
                <input type="text" name="username" value="cshos" placeholder="ชื่อผู้ใช้งาน..." style="width: 100%; padding: 12px 14px; border: 1.8px solid #cbd5e1; border-radius: 12px; font-size: 1rem; outline: none; font-family: inherit;" required>
            </div>
            <div style="text-align: left; margin-bottom: 16px;">
                <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">รหัสผ่าน (Password)</label>
                <div class="password-input-wrap" style="margin-bottom: 0;">
                    <input type="password" name="password" id="phpAdminPwd" placeholder="กรอกรหัสผ่านเพื่อเข้าใช้งาน..." autofocus required>
                    <button type="button" class="toggle-password-btn" onclick="togglePhpPassword()">
                        <span id="phpPwdEye">👁️</span>
                    </button>
                </div>
            </div>
            <button type="submit" class="btn-login-submit">เข้าสู่ระบบจัดการ ➜</button>
        </form>

        <div style="margin-top: 24px; font-size: 0.85rem;">
            <a href="index.php" style="color: #1a5c3a; text-decoration: none; font-weight: 500;">⬅️ กลับหน้าหลักร่วมทำบุญ</a>
        </div>
    </div>
</div>

<script>
    function togglePhpPassword() {
        const input = document.getElementById('phpAdminPwd');
        const eye = document.getElementById('phpPwdEye');
        if (input.type === 'password') {
            input.type = 'text';
            eye.textContent = '🙈';
        } else {
            input.type = 'password';
            eye.textContent = '👁️';
        }
    }
</script>

<?php else: ?>
<!-- ADMIN DASHBOARD (LOGGED IN) -->
<div class="admin-wrapper">
    <div class="donation-main-card">
        
        <!-- Header -->
        <div class="donation-header-box">
            <div class="header-title-group">
                <h2>💚 จัดการข้อมูลการบริจาคและข้อความกำลังใจ</h2>
                <small>รายการข้อความและสลิปการบริจาค (e-Donation) ที่ส่งเข้ามาจากประชาชน · โรงพยาบาลเชียงแสน</small>
            </div>
            
            <div class="header-actions-group">
                <a href="index.php" target="_blank" class="btn-header btn-view-public">
                    🌐 ดูหน้าบริจาค
                </a>
                <button type="button" class="btn-header btn-refresh" onclick="loadAdminDonations(true)">
                    🔄 รีเฟรช
                </button>
                <button type="button" class="btn-header btn-export" onclick="exportToCSV()">
                    📥 ส่งออก CSV
                </button>
                <a href="admin.php?logout=1" class="btn-header btn-logout" onclick="return confirm('ต้องการออกจากระบบใช่หรือไม่?');">
                    🔒 ออกจากระบบ
                </a>
            </div>
        </div>

        <!-- 3 Stats Cards -->
        <div class="donation-stat-grid">
            <div class="donation-stat-card">
                <div class="donation-stat-icon" style="background: #e8f5ee; color: #1a5c3a;">✉️</div>
                <div>
                    <div class="stat-label">รายการทั้งหมด</div>
                    <div class="stat-value" id="statTotalCount">0</div>
                </div>
            </div>

            <div class="donation-stat-card">
                <div class="donation-stat-icon" style="background: #eff6ff; color: #2563eb;">📷</div>
                <div>
                    <div class="stat-label">มีรูปภาพ / สลิปแนบ</div>
                    <div class="stat-value" id="statWithImagesCount">0</div>
                </div>
            </div>

            <div class="donation-stat-card">
                <div class="donation-stat-icon" style="background: #eef2ff; color: #4338ca;">📄</div>
                <div>
                    <div class="stat-label">ขอรับใบอนุโมทนาบัตร</div>
                    <div class="stat-value" id="statReceiptCount">0</div>
                </div>
            </div>
        </div>

        <!-- Search and Quick Filters -->
        <div class="filter-controls-box">
            <div class="search-form-wrap">
                <div class="search-input-box">
                    <input type="text" id="adminSearchInput" placeholder="🔍 ค้นหาชื่อ หรือ ข้อความ..." oninput="handleSearchInput(this.value)">
                </div>
                <button type="button" class="btn-search-action" onclick="applyFilters()">
                    ค้นหา
                </button>
            </div>

            <div class="quick-filter-pills">
                <button type="button" class="filter-pill active" data-filter="all" onclick="setQuickFilter('all', this)">ทั้งหมด (<span id="countPillAll">0</span>)</button>
                <button type="button" class="filter-pill" data-filter="has_image" onclick="setQuickFilter('has_image', this)">📷 มีรูป/สลิป</button>
                <button type="button" class="filter-pill" data-filter="receipt" onclick="setQuickFilter('receipt', this)">📄 ขอใบอนุโมทนา</button>
                <button type="button" class="filter-pill" data-filter="approved" onclick="setQuickFilter('approved', this)">👁️ แสดงบนเว็บ</button>
                <button type="button" class="filter-pill" data-filter="hidden" onclick="setQuickFilter('hidden', this)">🔒 ซ่อนบนเว็บ</button>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="desktop-table-view">
            <table class="admin-donation-table">
                <thead>
                    <tr>
                        <th style="width: 110px;">วันที่ส่ง</th>
                        <th style="width: 170px;">ชื่อผู้ร่วมทำบุญ</th>
                        <th style="width: 95px; text-align: center;">ใบอนุโมทนา</th>
                        <th style="width: 105px; text-align: center;">เผยแพร่ขอบคุณ</th>
                        <th style="width: 150px;">ชื่อลดหย่อนภาษี</th>
                        <th style="width: 130px;">เลขประจำตัวผู้เสียภาษี</th>
                        <th style="width: 110px;">เบอร์โทร</th>
                        <th style="min-width: 180px;">ที่อยู่จัดส่งเอกสาร</th>
                        <th style="min-width: 220px;">ข้อความ</th>
                        <th style="width: 110px; text-align: center;">สลิป/รูปภาพ</th>
                        <th style="width: 110px; text-align: center;">แสดงบนเว็บ</th>
                        <th style="width: 85px; text-align: center;">จัดการ</th>
                    </tr>
                </thead>
                <tbody id="desktopTableBody">
                    <tr><td colspan="12" style="text-align: center; padding: 40px; color: #64748b;">กำลังโหลดข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>

        <!-- Mobile Cards View -->
        <div class="mobile-cards-view" id="mobileCardsContainer">
            <!-- Populated via Javascript -->
        </div>

    </div>
</div>



<script src="config.js"></script>
<script>
    let allDonations = [];
    let currentFilter = 'all';
    let searchQuery = '';

    function getScriptUrl() {
        return localStorage.getItem('csh_google_script_url') || (typeof CONFIG !== 'undefined' ? CONFIG.GOOGLE_SCRIPT_URL : '');
    }

    async function loadAdminDonations(showToast = false) {
        const scriptUrl = getScriptUrl();
        let loadedData = null;

        if (scriptUrl) {
            try {
                const res = await fetch(scriptUrl + (scriptUrl.includes('?') ? '&' : '?') + 'action=admin_list&t=' + Date.now());
                if (res.ok) {
                    const json = await res.json();
                    if (json && json.donations) loadedData = json;
                }
            } catch (err) {}
        }

        // Check local PHP JSON backup
        if (!loadedData || !loadedData.donations || loadedData.donations.length === 0) {
            try {
                const res = await fetch('data/donations_backup.json');
                if (res.ok) {
                    const items = await res.json();
                    if (Array.isArray(items)) {
                        loadedData = {
                            success: true,
                            totalDonations: items.length,
                            withImagesCount: items.filter(i => i.local_image1 || i.local_image2).length,
                            receiptCount: items.filter(i => i.receipt_requested === 'รับ').length,
                            donations: items.map((it, idx) => ({
                                row: idx + 2,
                                created_at: it.created_at,
                                donor_name: it.donor_name,
                                message: it.message,
                                receipt_requested: it.receipt_requested || 'ไม่รับ',
                                tax_name: it.tax_name || '',
                                tax_id: it.tax_id || '',
                                phone: it.phone || '',
                                address: it.address || '',
                                allow_social_post: it.allow_social_post || 'อนุญาต',
                                image1: it.local_image1 || '',
                                image2: it.local_image2 || '',
                                status: it.status || 'แสดง',
                                is_approved: (it.status !== 'ซ่อน')
                            }))
                        };
                    }
                }
            } catch (e) {}
        }

        if (loadedData && Array.isArray(loadedData.donations)) {
            allDonations = loadedData.donations;
            updateStatsUI(loadedData);
            renderDonationsUI();
            if (showToast) {
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'อัปเดตข้อมูลแล้ว', showConfirmButton: false, timer: 1500 });
            }
        } else {
            allDonations = [];
            updateStatsUI({ totalDonations: 0, withImagesCount: 0, receiptCount: 0 });
            renderDonationsUI();
        }
    }

    function updateStatsUI(stats) {
        document.getElementById('statTotalCount').textContent = Number(stats.totalDonations || allDonations.length).toLocaleString();
        document.getElementById('statWithImagesCount').textContent = Number(stats.withImagesCount || 0).toLocaleString();
        document.getElementById('statReceiptCount').textContent = Number(stats.receiptCount || 0).toLocaleString();
        document.getElementById('countPillAll').textContent = Number(allDonations.length).toLocaleString();
    }

    function renderDonationsUI() {
        const tableBody = document.getElementById('desktopTableBody');
        const mobileContainer = document.getElementById('mobileCardsContainer');

        let filtered = allDonations.slice();
        if (currentFilter === 'has_image') filtered = filtered.filter(d => d.image1 || d.image2);
        else if (currentFilter === 'receipt') filtered = filtered.filter(d => d.receipt_requested === 'รับ');
        else if (currentFilter === 'approved') filtered = filtered.filter(d => d.is_approved);
        else if (currentFilter === 'hidden') filtered = filtered.filter(d => !d.is_approved);

        if (searchQuery) {
            const q = searchQuery.toLowerCase();
            filtered = filtered.filter(d => (d.donor_name||'').toLowerCase().includes(q) || (d.message||'').toLowerCase().includes(q) || (d.tax_name||'').toLowerCase().includes(q));
        }

        if (filtered.length === 0) {
            const emptyHtml = '<div style="text-align: center; padding: 40px; color: #94a3b8;">🌿 ไม่พบข้อมูลการบริจาค</div>';
            tableBody.innerHTML = `<tr><td colspan="12">${emptyHtml}</td></tr>`;
            mobileContainer.innerHTML = emptyHtml;
            return;
        }

        tableBody.innerHTML = filtered.map(item => {
            const img1 = formatImageUrl(item.image1);
            const img2 = formatImageUrl(item.image2);
            let thumbs = '';
            if (img1) {
                thumbs += `
                    <a href="${getExternalImageUrl(img1)}" target="_blank" rel="noopener" onclick="openImageExternal(event, '${img1}')" title="คลิกเพื่อเปิดรูปภาพใน Safari / Chrome" style="display:inline-block; text-decoration:none;">
                        <img src="${img1}" class="thumb-admin-img" alt="รูปที่ 1" onerror="this.parentElement.style.display='none'">
                    </a>
                `;
            }
            if (img2) {
                thumbs += `
                    <a href="${getExternalImageUrl(img2)}" target="_blank" rel="noopener" onclick="openImageExternal(event, '${img2}')" title="คลิกเพื่อเปิดสลิปใน Safari / Chrome" style="display:inline-block; text-decoration:none;">
                        <img src="${img2}" class="thumb-admin-img" alt="สลิปโอนเงิน" onerror="this.parentElement.style.display='none'">
                    </a>
                `;
            }
            if (!thumbs) thumbs = '<span style="color:#cbd5e1;">—</span>';

            const statusBtn = item.is_approved
                ? `<button type="button" class="badge-status-on" onclick="toggleVisibility(${item.row})">👁️ แสดง</button>`
                : `<button type="button" class="badge-status-off" onclick="toggleVisibility(${item.row})">🔒 ซ่อน</button>`;

            return `
                <tr>
                    <td style="color: #475569; font-size: 0.83rem;">${item.created_at || '—'}</td>
                    <td style="font-weight: 600; color: #1a5c3a;">${escapeHtml(item.donor_name || 'ผู้ไม่ประสงค์ออกนาม')}</td>
                    <td style="text-align:center;">${item.receipt_requested === 'รับ' ? '<span class="badge-receipt-yes">รับ</span>' : '<span class="badge-receipt-no">ไม่รับ</span>'}</td>
                    <td style="text-align:center;">${item.allow_social_post === 'อนุญาต' ? '<span class="badge-social-yes">อนุญาต</span>' : '<span class="badge-social-no">ไม่อนุญาต</span>'}</td>
                    <td style="user-select:all;">${escapeHtml(item.tax_name || '—')}</td>
                    <td style="user-select:all;">${item.tax_id ? `<span class="badge-tax-id">${escapeHtml(item.tax_id)}</span>` : '—'}</td>
                    <td style="user-select:all;">${item.phone ? `<a href="tel:${item.phone}" style="color:#0369a1; text-decoration:none;">${escapeHtml(item.phone)}</a>` : '—'}</td>
                    <td style="user-select:all;">${escapeHtml(item.address || '—')}</td>
                    <td style="word-break:break-word; max-width:260px;">${escapeHtml(item.message || '—')}</td>
                    <td style="text-align:center;"><div class="thumb-preview-box">${thumbs}</div></td>
                    <td style="text-align:center;">${statusBtn}</td>
                    <td style="text-align:center;"><button type="button" class="btn-delete-row" onclick="deleteDonation(${item.row})">🗑️ ลบ</button></td>
                </tr>
            `;
        }).join('');

        mobileContainer.innerHTML = filtered.map(item => {
            const img1 = formatImageUrl(item.image1);
            const img2 = formatImageUrl(item.image2);
            let mobileThumbs = '';
            if (img1) {
                mobileThumbs += `
                    <a href="${getExternalImageUrl(img1)}" target="_blank" rel="noopener" onclick="openImageExternal(event, '${img1}')" title="แตะเพื่อเปิดรูปใน Safari / Chrome" style="display:inline-block; text-decoration:none;">
                        <img src="${img1}" class="thumb-admin-img" alt="รูปที่ 1" onerror="this.parentElement.style.display='none'">
                    </a>
                `;
            }
            if (img2) {
                mobileThumbs += `
                    <a href="${getExternalImageUrl(img2)}" target="_blank" rel="noopener" onclick="openImageExternal(event, '${img2}')" title="แตะเพื่อเปิดสลิปใน Safari / Chrome" style="display:inline-block; text-decoration:none;">
                        <img src="${img2}" class="thumb-admin-img" alt="สลิปโอนเงิน" onerror="this.parentElement.style.display='none'">
                    </a>
                `;
            }

            return `
                <div class="mobile-donation-card">
                    <div class="mobile-card-top">
                        <div class="mobile-card-donor">${escapeHtml(item.donor_name || 'ผู้ไม่ประสงค์ออกนาม')}</div>
                        <div class="mobile-card-date">${item.created_at || '—'}</div>
                    </div>
                    ${item.message ? `<div class="mobile-card-message">${escapeHtml(item.message)}</div>` : ''}
                    ${mobileThumbs ? `
                        <div>
                            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 6px; font-weight: 500;">📷 รูปภาพ/สลิปแนบ (แตะเพื่อดูภาพใหญ่หรือดาวน์โหลด):</div>
                            <div class="mobile-card-thumbs" style="display:flex; gap:10px; flex-wrap:wrap;">${mobileThumbs}</div>
                        </div>
                    ` : ''}
                    <div class="mobile-card-actions">
                        ${item.is_approved ? `<button type="button" class="badge-status-on" onclick="toggleVisibility(${item.row})">👁️ แสดงบนเว็บ</button>` : `<button type="button" class="badge-status-off" onclick="toggleVisibility(${item.row})">🔒 ซ่อนบนเว็บ</button>`}
                        <button type="button" class="btn-delete-row" onclick="deleteDonation(${item.row})">🗑️ ลบ</button>
                    </div>
                </div>
            `;
        }).join('');
    }

    async function toggleVisibility(rowIdx) {
        const item = allDonations.find(d => d.row === rowIdx);
        if (!item) return;
        item.is_approved = !item.is_approved;
        item.status = item.is_approved ? 'แสดง' : 'ซ่อน';
        renderDonationsUI();

        const scriptUrl = getScriptUrl();
        if (scriptUrl) {
            try {
                await fetch(scriptUrl + (scriptUrl.includes('?') ? '&' : '?') + 'action=toggle_status&row=' + rowIdx);
            } catch(e) {}
        }
    }

    async function deleteDonation(rowIdx) {
        if (!confirm('ยืนยันการลบข้อมูลรายการนี้?')) return;
        allDonations = allDonations.filter(d => d.row !== rowIdx);
        renderDonationsUI();
        const scriptUrl = getScriptUrl();
        if (scriptUrl) {
            try {
                await fetch(scriptUrl + (scriptUrl.includes('?') ? '&' : '?') + 'action=delete_row&row=' + rowIdx);
            } catch(e) {}
        }
    }

    function exportToCSV() {
        if (!allDonations || allDonations.length === 0) return;
        const headers = ["วันที่ส่ง", "ชื่อผู้ร่วมบริจาค", "ข้อความ", "ขอรับใบอนุโมทนาบัตร", "ชื่อลดหย่อนภาษี", "เลขประจำตัวผู้เสียภาษี", "เบอร์โทร", "ที่อยู่"];
        const rows = allDonations.map(d => [d.created_at, d.donor_name, d.message, d.receipt_requested, d.tax_name, d.tax_id, d.phone, d.address]);
        const csv = "\uFEFF" + [headers.join(','), ...rows.map(r => r.map(c => `"${(c||'').replace(/"/g, '""')}"`).join(','))].join('\r\n');
        const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = `CSH_Donations_${new Date().toISOString().slice(0, 10)}.csv`;
        link.click();
    }

    function handleSearchInput(val) {
        searchQuery = val.trim();
        renderDonationsUI();
    }

    function applyFilters() { renderDonationsUI(); }

    function setQuickFilter(type, el) {
        currentFilter = type;
        document.querySelectorAll('.filter-pill').forEach(p => p.classList.remove('active'));
        if (el) el.classList.add('active');
        renderDonationsUI();
    }

    let currentModalImgUrl = '';

    function isLineApp() {
        const ua = (navigator.userAgent || navigator.vendor || window.opera || '').toLowerCase();
        return ua.includes('line/') || ua.includes('line ') || /Line/i.test(navigator.userAgent);
    }

    function getExternalImageUrl(imgUrl) {
        if (!imgUrl) return '#';
        const separator = imgUrl.includes('?') ? '&' : '?';
        return isLineApp() ? `${imgUrl}${separator}openExternalBrowser=1` : imgUrl;
    }

    function openImageExternal(e, imgUrl) {
        if (e && e.preventDefault) e.preventDefault();
        if (!imgUrl) return;

        if (isLineApp()) {
            // Appending openExternalBrowser=1 opens Safari on iOS or Chrome on Android directly from LINE
            const separator = imgUrl.includes('?') ? '&' : '?';
            const extUrl = `${imgUrl}${separator}openExternalBrowser=1`;
            window.location.href = extUrl;
        } else {
            // Standard desktop/mobile browser
            window.open(imgUrl, '_blank', 'noopener,noreferrer');
        }
    }

    function openAdminImageModal(src) {
        openImageExternal(null, src);
    }

    function handleImageAction(src) {
        openImageExternal(null, src);
    }

    function downloadDirectImage(imgUrl) {
        openImageExternal(null, imgUrl);
    }

    function downloadCurrentModalImage() {}

    function formatImageUrl(url) {
        if (!url) return '';
        const str = String(url).trim();
        const match = str.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || str.match(/[?&]id=([a-zA-Z0-9_-]+)/);
        if (match && match[1]) return `https://lh3.googleusercontent.com/d/${match[1]}`;
        if (str.startsWith('http') || str.startsWith('data:image/') || str.startsWith('uploads/')) return str;
        return '';
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
    }

    document.addEventListener('DOMContentLoaded', () => {
        loadAdminDonations();
    });
</script>
<?php endif; ?>

</body>
</html>
