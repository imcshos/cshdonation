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
            max-width: 1560px;
            margin: 0 auto;
            padding: 24px 24px 60px;
            width: 100%;
            box-sizing: border-box;
        }

        .donation-main-card {
            background: #ffffff;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2ece7;
            width: 100%;
            box-sizing: border-box;
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
            font-size: clamp(1.2rem, 2.5vw, 1.55rem);
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
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-header {
            padding: 8px 14px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            white-space: nowrap;
        }

        .btn-view-public {
            background: #e8f5ee;
            color: #1a5c3a;
            border-color: #cde8da;
        }

        .btn-view-public:hover {
            background: #d5eedf;
        }

        .btn-refresh {
            background: #f1f5f9;
            color: #334155;
            border-color: #cbd5e1;
        }

        .btn-refresh:hover {
            background: #e2e8f0;
        }

        .btn-export {
            background: #f0fdf4;
            color: #15803d;
            border-color: #bbf7d0;
        }

        .btn-export:hover {
            background: #dcfce7;
        }

        .btn-logout {
            background: #fff1f2;
            color: #e11d48;
            border-color: #fecdd3;
        }

        .btn-logout:hover {
            background: #ffe4e6;
        }

        /* 3 Stats Cards */
        .donation-stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
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
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .donation-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.07);
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

        /* Search and Quick Filters */
        .filter-controls-box {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
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
            padding: 10px 36px 10px 14px;
            border: 1.5px solid #d4e2dc;
            border-radius: 10px;
            font-size: 0.92rem;
            outline: none;
            background: #ffffff;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }

        .search-input-box input:focus {
            border-color: var(--admin-green);
        }

        .search-clear-btn {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            font-size: 14px;
            display: none;
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
            transition: background 0.2s;
        }

        .btn-search-action:hover {
            background: var(--admin-green-dark);
        }

        .quick-filter-pills {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
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
            user-select: none;
        }

        .filter-pill:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
        }

        .filter-pill.active {
            background: #1a5c3a;
            color: white;
            border-color: #1a5c3a;
            font-weight: 600;
        }

        /* Desktop Table View */
        .desktop-table-view {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid #e2ece7;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.02);
            scrollbar-width: thin;
            scrollbar-color: #cbd5e1 #f8fafc;
        }

        .desktop-table-view::-webkit-scrollbar {
            height: 8px;
        }

        .desktop-table-view::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .desktop-table-view::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .desktop-table-view::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        table.admin-donation-table {
            width: 100%;
            border-collapse: collapse;
            min-width: 1180px;
            font-size: 0.86rem;
        }

        table.admin-donation-table thead tr {
            background: #f4faf6;
            border-bottom: 2px solid #d4ece0;
            text-align: left;
            white-space: nowrap;
        }

        table.admin-donation-table th {
            position: sticky;
            top: 0;
            background: #f4faf6;
            z-index: 3;
            padding: 12px 10px;
            color: var(--admin-green);
            font-weight: 600;
            font-family: 'Kanit', sans-serif;
            font-size: 0.88rem;
            box-shadow: 0 1px 0 #d4ece0;
        }

        table.admin-donation-table tbody tr {
            border-bottom: 1px solid #eef4f1;
            transition: background 0.15s;
        }

        table.admin-donation-table tbody tr:hover {
            background: #fbfdfc;
        }

        table.admin-donation-table td {
            padding: 11px 10px;
            vertical-align: middle;
        }

        /* Badges & Tags */
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
            transition: all 0.2s;
        }

        .badge-status-on:hover {
            background: #cdeee0;
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
            transition: all 0.2s;
        }

        .badge-status-off:hover {
            background: #e2e8f0;
            color: #334155;
        }

        .badge-receipt-yes {
            background: #eef2ff;
            color: #3730a3;
            font-size: 0.76rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            border: 1px solid #c7d2fe;
            display: inline-block;
        }

        .badge-receipt-no {
            background: #f1f5f9;
            color: #64748b;
            font-size: 0.76rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-social-yes {
            background: #e8f5ee;
            color: #1a5c3a;
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
            border: 1px solid #b7e3cf;
            display: inline-block;
        }

        .badge-social-no {
            background: #fff1f2;
            color: #be123c;
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
            border: 1px solid #fecdd3;
            display: inline-block;
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
            display: inline-block;
        }

        .thumb-preview-box {
            display: flex;
            gap: 6px;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
        }

        /* Thumbnail Cell & Quick Share */
        .thumb-cell-wrapper {
            display: inline-flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .btn-share-inline {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-share-inline:hover {
            background: #dcfce7;
            border-color: #86efac;
            transform: translateY(-1px);
        }

        .mobile-thumbs-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
            margin-top: 4px;
        }

        .mobile-thumbs-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-mobile-share-all {
            background: #166534;
            color: #ffffff;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            box-shadow: 0 2px 4px rgba(22, 101, 52, 0.2);
            transition: all 0.2s;
            font-family: inherit;
        }

        .btn-mobile-share-all:active {
            transform: scale(0.96);
            background: #14532d;
        }

        .thumb-admin-img {
            width: 52px;
            height: 52px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            border: 1.5px solid #d4dfd9;
            transition: all 0.2s;
            background: #f8fafc;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: block;
        }

        .thumb-admin-img:hover {
            transform: scale(1.12);
            border-color: #1a5c3a;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.18);
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
            font-family: inherit;
            transition: all 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .btn-delete-row:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* View Mode Switcher Bar */
        .view-mode-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding: 4px 2px;
            gap: 10px;
            flex-wrap: wrap;
        }

        .view-mode-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .view-mode-title small {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: normal;
        }

        .view-mode-switch {
            display: inline-flex;
            background: #e2e8f0;
            padding: 3px;
            border-radius: 10px;
            gap: 3px;
        }

        .btn-view-pill {
            background: transparent;
            border: none;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 0.82rem;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-view-pill.active {
            background: #ffffff;
            color: #166534;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Table Scroll Hint */
        .mobile-table-hint {
            display: none;
            background: #f0fdf4;
            color: #166534;
            border: 1px dashed #86efac;
            border-radius: 8px;
            padding: 7px 12px;
            font-size: 0.78rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 8px;
        }

        /* Mobile Cards View */
        .mobile-cards-view {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .mobile-donation-card {
            background: #ffffff;
            border: 1px solid #e2ece7;
            border-radius: 16px;
            padding: 14px 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: all 0.2s;
        }

        .mobile-donation-card:hover {
            border-color: #cbdad3;
            box-shadow: 0 4px 12px rgba(22, 101, 52, 0.06);
        }

        /* Card Top Header */
        .m-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 10px;
            border-bottom: 1px solid #f1f5f3;
            padding-bottom: 10px;
        }

        .m-donor-name {
            font-size: 1.05rem;
            font-weight: 700;
            color: #166534;
            font-family: 'Kanit', sans-serif;
            line-height: 1.3;
        }

        .m-donor-date {
            font-size: 0.76rem;
            color: #64748b;
            margin-top: 3px;
        }

        /* Badges Row */
        .m-card-badges {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
            align-items: center;
        }

        .m-badge {
            font-size: 0.74rem;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .m-badge-receipt-yes { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .m-badge-receipt-no { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .m-badge-social-yes { background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; }
        .m-badge-social-no { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }

        /* Message Box */
        .m-card-message {
            background: #f8fafc;
            border-left: 3px solid #166534;
            border-radius: 4px 10px 10px 4px;
            padding: 8px 12px;
            font-size: 0.85rem;
            color: #334155;
            line-height: 1.5;
            word-break: break-word;
        }

        /* Structured Key-Value Data Sheet */
        .m-data-sheet {
            background: #fbfdfc;
            border: 1px solid #e6f0eb;
            border-radius: 12px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .m-data-sheet-title {
            font-size: 0.76rem;
            font-weight: 700;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .m-data-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            font-size: 0.82rem;
            gap: 8px;
            padding-bottom: 5px;
            border-bottom: 1px dashed #edf4f0;
        }

        .m-data-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .m-data-label {
            color: #64748b;
            font-size: 0.78rem;
            flex-shrink: 0;
        }

        .m-data-value {
            color: #1e293b;
            font-weight: 500;
            text-align: right;
            word-break: break-word;
        }

        .m-code-tax {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 2px 6px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 0.82rem;
            color: #0f172a;
            font-weight: 600;
        }

        .m-tel-link {
            color: #0369a1;
            text-decoration: none;
            font-weight: 600;
        }

        /* Media Box on Mobile */
        .m-media-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 10px 12px;
        }

        .m-media-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            gap: 8px;
            flex-wrap: wrap;
        }

        .m-media-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
        }

        .m-media-thumbs {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .m-media-thumbs .thumb-admin-img {
            width: 76px;
            height: 76px;
            border-radius: 10px;
            border: 1.5px solid #cbd5e1;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.06);
            object-fit: cover;
        }

        /* Card Actions Footer */
        .m-card-footer {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            border-top: 1px solid #f1f5f3;
            padding-top: 10px;
            gap: 8px;
        }

        /* Loading Skeleton & Empty States */
        .skeleton-row {
            height: 48px;
            background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeletonLoading 1.5s infinite;
            border-radius: 8px;
            margin: 6px 0;
        }

        @keyframes skeletonLoading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .empty-state-box {
            text-align: center;
            padding: 40px 16px;
            color: #94a3b8;
            background: #fbfdfc;
            border-radius: 14px;
            border: 1px dashed #d4e2dc;
            margin: 10px 0;
            font-size: 0.9rem;
        }

        /* ==========================================================================
           RESPONSIVE BREAKPOINTS (COMPUTER & MOBILE FIT)
           ========================================================================== */
        @media (max-width: 992px) {
            .mobile-table-hint {
                display: flex !important;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .desktop-table-view {
                border-radius: 12px;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
            }

            table.admin-donation-table {
                min-width: 1040px;
            }

            table.admin-donation-table th,
            table.admin-donation-table td {
                padding: 9px 8px !important;
                font-size: 0.8rem !important;
            }

            /* Sticky Donor Column on Mobile Table */
            table.admin-donation-table th:nth-child(2),
            table.admin-donation-table td:nth-child(2) {
                position: sticky;
                left: 0;
                background: #ffffff;
                z-index: 2;
                box-shadow: 3px 0 6px -2px rgba(0, 0, 0, 0.08);
            }

            table.admin-donation-table th:nth-child(2) {
                background: #f4faf6;
                z-index: 4;
            }

            .admin-wrapper {
                padding: 14px 12px 48px !important;
            }

            .donation-main-card {
                padding: 16px 14px !important;
                border-radius: 16px !important;
            }

            .donation-header-box {
                flex-direction: column;
                align-items: stretch !important;
                gap: 12px !important;
                margin-bottom: 16px !important;
                padding-bottom: 14px !important;
            }

            .header-title-group h2 {
                font-size: 1.25rem !important;
            }

            .header-title-group small {
                font-size: 0.82rem !important;
            }

            .header-actions-group {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 8px !important;
                width: 100% !important;
            }

            .header-actions-group .btn-header {
                width: 100% !important;
                justify-content: center !important;
                text-align: center !important;
                font-size: 0.84rem !important;
                padding: 9px 8px !important;
                box-sizing: border-box !important;
            }

            .header-actions-group .btn-logout {
                grid-column: span 2 !important;
            }

            .filter-controls-box {
                flex-direction: column;
                align-items: stretch !important;
                gap: 10px !important;
                margin-bottom: 16px !important;
            }

            .search-form-wrap {
                max-width: 100% !important;
                width: 100% !important;
            }

            .search-input-box input {
                font-size: 0.88rem !important;
                padding: 9px 34px 9px 12px !important;
            }

            .btn-search-action {
                padding: 9px 16px !important;
                font-size: 0.88rem !important;
            }

            .quick-filter-pills {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
                scrollbar-width: none;
                padding: 2px 2px 6px !important;
                flex-wrap: nowrap !important;
            }

            .quick-filter-pills::-webkit-scrollbar {
                display: none;
            }

            .quick-filter-pills .filter-pill {
                white-space: nowrap !important;
                padding: 6px 12px !important;
                font-size: 0.78rem !important;
                flex-shrink: 0 !important;
            }
        }

        @media (max-width: 640px) {
            .admin-wrapper {
                padding: 8px 6px 36px !important;
            }

            .donation-main-card {
                padding: 12px 10px !important;
                border-radius: 14px !important;
            }

            /* Compact 3 Stats Cards in 1 Row on Mobile Phones */
            .donation-stat-grid {
                grid-template-columns: repeat(3, 1fr) !important;
                gap: 6px !important;
                margin-bottom: 12px !important;
            }

            .donation-stat-card {
                padding: 8px 6px !important;
                flex-direction: column !important;
                text-align: center !important;
                gap: 4px !important;
                border-radius: 10px !important;
            }

            .donation-stat-icon {
                width: 32px !important;
                height: 32px !important;
                font-size: 16px !important;
                border-radius: 8px !important;
                margin: 0 auto !important;
            }

            .stat-label {
                font-size: 0.68rem !important;
                line-height: 1.2 !important;
                height: 2.4em !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
            }

            .stat-value {
                font-size: 1.15rem !important;
                margin-top: 0 !important;
                line-height: 1.1 !important;
            }

            /* Mobile Login Overlay */
            #loginOverlay {
                padding: 12px !important;
            }

            .login-card {
                padding: 24px 16px !important;
                border-radius: 16px !important;
                max-width: 100% !important;
            }

            .login-logo-wrap {
                width: 58px !important;
                height: 58px !important;
                margin-bottom: 12px !important;
            }

            .login-title {
                font-size: 1.25rem !important;
            }

            .login-sub {
                font-size: 0.82rem !important;
                margin-bottom: 16px !important;
            }

            .password-input-wrap input {
                padding: 11px 40px 11px 12px !important;
                font-size: 0.95rem !important;
            }

            .btn-login-submit {
                padding: 12px !important;
                font-size: 0.95rem !important;
            }
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

        <!-- View Mode Switcher Bar -->
        <div class="view-mode-bar">
            <div class="view-mode-title">
                <span>📋 รายการข้อมูลบริจาค</span>
                <small id="filteredCountLabel">(กำลังโหลด...)</small>
            </div>
            <div class="view-mode-switch">
                <button type="button" id="btnViewCard" class="btn-view-pill active" onclick="switchViewMode('card')" title="แสดงแบบการ์ด">
                    🗂️ การ์ด
                </button>
                <button type="button" id="btnViewTable" class="btn-view-pill" onclick="switchViewMode('table')" title="แสดงแบบตาราง">
                    📋 ตาราง
                </button>
            </div>
        </div>

        <!-- Desktop Table View -->
        <div class="desktop-table-view">
            <div class="mobile-table-hint">↔️ เลื่อนตารางแนวนอน เพื่อดูข้อมูลครบทุกคอลัมน์</div>
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
            preloadDonationImages(allDonations);
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
            let thumbsContent = '<span style="color:#cbd5e1;">—</span>';
            if (thumbs) {
                const imgCount = (img1 ? 1 : 0) + (img2 ? 1 : 0);
                const shareLabel = imgCount > 1 ? '📤 แชร์รูป (2 รูป)' : '📤 แชร์รูป';
                thumbsContent = `
                    <div class="thumb-cell-wrapper" onmouseenter="quickPreloadRow(${item.row})" ontouchstart="quickPreloadRow(${item.row})">
                        <div class="thumb-preview-box">${thumbs}</div>
                        <button type="button" class="btn-share-inline" onclick="shareDonationByRow(${item.row})" title="คลิกเพื่อแชร์รูปภาพทันที">
                            ${shareLabel}
                        </button>
                    </div>
                `;
            }

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
                    <td style="text-align:center;">${thumbsContent}</td>
                    <td style="text-align:center;">${statusBtn}</td>
                    <td style="text-align:center;"><button type="button" class="btn-delete-row" onclick="deleteDonation(${item.row})">🗑️ ลบ</button></td>
                </tr>
            `;
        }).join('');

        // 2. Mobile Cards (Table-Card Structured View)
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

            const isReceipt = (item.receipt_requested === 'รับ');
            const statusBtn = item.is_approved
                ? `<button type="button" class="badge-status-on" onclick="toggleVisibility(${item.row})" title="คลิกเพื่อสลับเป็น ซ่อน">👁️ แสดงบนเว็บ</button>`
                : `<button type="button" class="badge-status-off" onclick="toggleVisibility(${item.row})" title="คลิกเพื่อสลับเป็น แสดง">🔒 ซ่อนบนเว็บ</button>`;

            const hasTaxInfo = item.tax_name || item.tax_id || item.phone || item.address || isReceipt;

            return `
                <div class="mobile-donation-card" data-row="${item.row}">
                    <!-- Card Top Header -->
                    <div class="m-card-header">
                        <div>
                            <div class="m-donor-name">${escapeHtml(item.donor_name || 'ผู้ไม่ประสงค์ออกนาม')}</div>
                            <div class="m-donor-date">📅 ${formatShortDate(item.created_at)}</div>
                        </div>
                        <div>${statusBtn}</div>
                    </div>

                    <!-- Badges Row -->
                    <div class="m-card-badges">
                        <span class="m-badge ${isReceipt ? 'm-badge-receipt-yes' : 'm-badge-receipt-no'}">
                            ${isReceipt ? '📄 ขอใบอนุโมทนาบัตร' : '⚪ ไม่รับใบอนุโมทนา'}
                        </span>
                        <span class="m-badge ${item.allow_social_post === 'อนุญาต' ? 'm-badge-social-yes' : 'm-badge-social-no'}">
                            ${item.allow_social_post === 'อนุญาต' ? '📢 เผยแพร่บนเว็บ' : '🔒 ไม่อนุญาตเผยแพร่'}
                        </span>
                    </div>

                    <!-- Message Bubble -->
                    ${item.message ? `
                        <div class="m-card-message">
                            <strong>💬 ข้อความ:</strong> ${escapeHtml(item.message)}
                        </div>
                    ` : ''}

                    <!-- Structured Data Sheet (ตารางข้อมูลย่อ) -->
                    ${hasTaxInfo ? `
                        <div class="m-data-sheet">
                            <div class="m-data-sheet-title">📋 ข้อมูลลดหย่อนภาษี & ติดต่อ:</div>
                            ${item.tax_name ? `
                                <div class="m-data-row">
                                    <span class="m-data-label">🏷️ ชื่อลดหย่อน:</span>
                                    <span class="m-data-value"><strong>${escapeHtml(item.tax_name)}</strong></span>
                                </div>
                            ` : ''}
                            ${item.tax_id ? `
                                <div class="m-data-row">
                                    <span class="m-data-label">🆔 เลขประจำตัว:</span>
                                    <span class="m-data-value"><code class="m-code-tax">${escapeHtml(item.tax_id)}</code></span>
                                </div>
                            ` : ''}
                            ${item.phone ? `
                                <div class="m-data-row">
                                    <span class="m-data-label">📞 เบอร์โทร:</span>
                                    <span class="m-data-value"><a href="tel:${item.phone}" class="m-tel-link">${escapeHtml(item.phone)}</a></span>
                                </div>
                            ` : ''}
                            ${item.address ? `
                                <div class="m-data-row" style="flex-direction: column; align-items: flex-start; gap: 2px;">
                                    <span class="m-data-label">📍 ที่อยู่จัดส่ง:</span>
                                    <span class="m-data-value" style="text-align: left;">${escapeHtml(item.address)}</span>
                                </div>
                            ` : ''}
                        </div>
                    ` : ''}

                    <!-- Media Section & Quick Share -->
                    ${mobileThumbs ? `
                        <div class="m-media-box" ontouchstart="quickPreloadRow(${item.row})">
                            <div class="m-media-header">
                                <span class="m-media-title">📷 รูปภาพและสลิปแนบ (${(img1 && img2) ? '2 รูป' : '1 รูป'}):</span>
                                <button type="button" class="btn-mobile-share-all" onclick="shareDonationByRow(${item.row})">
                                    📤 แชร์รูปภาพ${(img1 && img2) ? ' (แชร์พร้อมกัน 2 รูป)' : ''}
                                </button>
                            </div>
                            <div class="m-media-thumbs">${mobileThumbs}</div>
                        </div>
                    ` : ''}

                    <!-- Footer Actions -->
                    <div class="m-card-footer">
                        <button type="button" class="btn-delete-row" onclick="deleteDonation(${item.row}, '${escapeHtml(item.donor_name || 'ผู้บริจาค')}')">
                            🗑️ ลบข้อมูล
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        const countLabel = document.getElementById('filteredCountLabel');
        if (countLabel) {
            countLabel.textContent = `(แสดง ${filtered.length} รายการ)`;
        }
        applyViewModeUI();
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

    async function deleteDonation(rowIdx, donorName = 'ผู้บริจาค') {
        const result = await Swal.fire({
            title: 'ยืนยันการลบข้อมูล?',
            html: `ต้องการลบข้อมูลของ <strong>"${escapeHtml(donorName)}"</strong> ใช่หรือไม่?<br><small style="color: #dc2626;">การลบนี้จะไม่สามารถย้อนกลับได้</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'ใช่, ต้องการลบ',
            cancelButtonText: 'ยกเลิก'
        });
        if (!result.isConfirmed) return;

        allDonations = allDonations.filter(d => d.row !== rowIdx);
        renderDonationsUI();
        updateStatsUI({ totalDonations: allDonations.length });
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

    // Image File In-Memory Cache for Instant Web Share
    const imageFileCache = new Map();

    function preloadDonationImages(donations) {
        if (!donations || !Array.isArray(donations)) return;
        donations.forEach(item => {
            const u1 = formatImageUrl(item.image1);
            const u2 = formatImageUrl(item.image2);
            if (u1 && !imageFileCache.has(u1)) {
                loadImageAsFile(u1, `csh_${item.row}_photo.jpg`).then(file => {
                    if (file) imageFileCache.set(u1, file);
                }).catch(() => {});
            }
            if (u2 && !imageFileCache.has(u2)) {
                loadImageAsFile(u2, `csh_${item.row}_slip.jpg`).then(file => {
                    if (file) imageFileCache.set(u2, file);
                }).catch(() => {});
            }
        });
    }

    function quickPreloadRow(rowIdx) {
        const item = allDonations.find(d => Number(d.row) === Number(rowIdx));
        if (!item) return;
        const u1 = formatImageUrl(item.image1);
        const u2 = formatImageUrl(item.image2);
        if (u1 && !imageFileCache.has(u1)) {
            loadImageAsFile(u1, `csh_${rowIdx}_photo.jpg`).then(file => {
                if (file) imageFileCache.set(u1, file);
            }).catch(() => {});
        }
        if (u2 && !imageFileCache.has(u2)) {
            loadImageAsFile(u2, `csh_${rowIdx}_slip.jpg`).then(file => {
                if (file) imageFileCache.set(u2, file);
            }).catch(() => {});
        }
    }

    // Share donation images (supports sharing 2 images simultaneously via Web Share API)
    async function shareDonationByRow(rowIdx) {
        const item = allDonations.find(d => Number(d.row) === Number(rowIdx));
        if (!item) {
            Swal.fire({ icon: 'warning', title: 'ไม่พบข้อมูลรายการนี้' });
            return;
        }

        const img1 = formatImageUrl(item.image1);
        const img2 = formatImageUrl(item.image2);
        const validUrls = [img1, img2].filter(Boolean);

        if (validUrls.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'ไม่มีรูปภาพแนบ',
                text: 'รายการนี้ไม่มีรูปภาพหรือสลิปแนบ',
                confirmButtonColor: '#1a5c3a'
            });
            return;
        }

        const shareTitle = 'รูปภาพการบริจาค - โรงพยาบาลเชียงแสน';
        const shareText = `รูปภาพและสลิปการร่วมบริจาค${item.donor_name ? ` คุณ${item.donor_name}` : ''} - โรงพยาบาลเชียงแสน`;

        // Check if pre-cached files are available for instant synchronous share
        const cachedFiles = [];
        if (img1 && imageFileCache.has(img1)) cachedFiles.push(imageFileCache.get(img1));
        if (img2 && imageFileCache.has(img2)) cachedFiles.push(imageFileCache.get(img2));

        // If all files are already loaded in memory -> immediate synchronous share
        if (cachedFiles.length === validUrls.length && navigator.canShare && navigator.canShare({ files: cachedFiles })) {
            try {
                await navigator.share({
                    title: shareTitle,
                    text: shareText,
                    files: cachedFiles
                });
                return;
            } catch (shareErr) {
                if (shareErr.name === 'AbortError') return; // User closed sheet
                console.warn('Sync share error:', shareErr);
            }
        }

        // If not cached yet or sync share was blocked, show loading and fetch
        const isDouble = validUrls.length > 1;
        Swal.fire({
            title: isDouble ? 'กำลังเปิดหน้าต่างแชร์ (2 รูป)...' : 'กำลังเปิดหน้าต่างแชร์...',
            text: 'กำลังโหลดรูปภาพเพื่อเปิดหน้าต่างแชร์ทันที',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const timestamp = Date.now();
            const promises = validUrls.map((url, idx) => {
                if (imageFileCache.has(url)) return Promise.resolve(imageFileCache.get(url));
                const filename = `csh_donation_${item.row || timestamp}_${idx + 1}.jpg`;
                return loadImageAsFile(url, filename).then(file => {
                    if (file) imageFileCache.set(url, file);
                    return file;
                });
            });

            const files = (await Promise.all(promises)).filter(Boolean);

            if (files.length > 0 && navigator.canShare && navigator.canShare({ files })) {
                Swal.close();
                const shareData = { title: shareTitle, text: shareText, files: files };
                try {
                    await navigator.share(shareData);
                } catch (shareErr) {
                    if (shareErr.name === 'NotAllowedError') {
                        showTapToShareModal(shareData, validUrls, files, item.donor_name);
                    } else if (shareErr.name !== 'AbortError') {
                        showShareFallbackModal(validUrls, files, item.donor_name);
                    }
                }
                return;
            }

            Swal.close();
            showShareFallbackModal(validUrls, files, item.donor_name);
        } catch (err) {
            Swal.close();
            if (err.name === 'AbortError') return;
            console.warn('Share error:', err);
            showShareFallbackModal(validUrls, [], item.donor_name);
        }
    }

    async function loadImageAsFile(url, defaultName = 'image.jpg') {
        if (!url) return null;

        // Case 1: Data URL
        if (url.startsWith('data:image/')) {
            try {
                const parts = url.split(',');
                const mime = parts[0].match(/:(.*?);/)?.[1] || 'image/jpeg';
                const bstr = atob(parts[1]);
                let n = bstr.length;
                const u8arr = new Uint8Array(n);
                while (n--) {
                    u8arr[n] = bstr.charCodeAt(n);
                }
                return new File([u8arr], defaultName, { type: mime });
            } catch (e) {
                console.warn('Data URL to File conversion error:', e);
            }
        }

        // Case 2: Direct fetch (works for same origin / CORS enabled lh3)
        try {
            const response = await fetch(url, { mode: 'cors', credentials: 'omit' });
            if (response.ok) {
                const blob = await response.blob();
                let mime = blob.type;
                if (!mime || mime === 'application/octet-stream') {
                    mime = defaultName.endsWith('.png') ? 'image/png' : 'image/jpeg';
                }
                return new File([blob], defaultName, { type: mime });
            }
        } catch (fetchErr) {
            console.warn('Direct fetch failed, falling back to Canvas for:', url, fetchErr);
        }

        // Case 3: Canvas drawing fallback
        try {
            return await new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    try {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.naturalWidth || img.width;
                        canvas.height = img.naturalHeight || img.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);
                        canvas.toBlob((blob) => {
                            if (blob) {
                                resolve(new File([blob], defaultName, { type: 'image/jpeg' }));
                            } else {
                                resolve(null);
                            }
                        }, 'image/jpeg', 0.95);
                    } catch (e) {
                        console.warn('Canvas toBlob failed:', e);
                        resolve(null);
                    }
                };
                img.onerror = () => resolve(null);
                img.src = url;
            });
        } catch (e) {
            console.warn('Canvas fallback error:', e);
        }

        return null;
    }

    function showTapToShareModal(shareData, urls, files, donorName) {
        const count = shareData.files.length;
        Swal.fire({
            icon: 'success',
            title: 'รูปภาพพร้อมแชร์แล้ว',
            html: `
                <p style="color:#475569; font-size:0.9rem; margin:0 0 14px;">แตะปุ่มด้านล่างเพื่อแชร์ทันที${count > 1 ? ' (พร้อมกัน 2 รูป)' : ''}</p>
                <button id="btnTapShareNow" class="swal2-confirm swal2-styled" style="background-color:#166534; margin:0; width:100%; font-size:1rem; padding:12px; font-weight:600;">
                    📤 แตะเพื่อแชร์ทันที${count > 1 ? ' (2 รูป)' : ''}
                </button>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            didOpen: () => {
                document.getElementById('btnTapShareNow')?.addEventListener('click', () => {
                    // Call share synchronously inside the click handler (fresh user gesture)
                    navigator.share(shareData).then(() => Swal.close()).catch(err => {
                        Swal.close();
                        if (err && err.name !== 'AbortError') showShareFallbackModal(urls, files, donorName);
                    });
                });
            }
        });
    }

    function showShareFallbackModal(urls, files, donorName) {
        const isMultiple = urls.length > 1;
        const inLine = isLineApp();
        Swal.fire({
            title: isMultiple ? 'แชร์รูปภาพ (2 รูป)' : 'แชร์รูปภาพ',
            html: `
                <div style="text-align: center; margin-bottom: 12px;">
                    <p style="color: #475569; font-size: 0.9rem; margin: 0 0 12px;">
                        ${donorName ? `ผู้บริจาค: <strong>${escapeHtml(donorName)}</strong>` : 'ข้อมูลการบริจาค'}
                    </p>
                    <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 16px;">
                        ${urls.map((u, i) => `
                            <a href="${u}" target="_blank" rel="noopener" title="คลิกดูภาพใหญ่">
                                <img src="${u}" style="width: 88px; height: 88px; object-fit: cover; border-radius: 10px; border: 1.5px solid #cbd5e1; box-shadow: 0 2px 6px rgba(0,0,0,0.08);" alt="รูปที่ ${i+1}">
                            </a>
                        `).join('')}
                    </div>
                    <p style="color: #64748b; font-size: 0.82rem; margin: 0 0 14px;">
                        ${isMultiple ? 'คุณสามารถบันทึกหรือเปิดรูปภาพทั้ง 2 รูปพร้อมกันได้ที่นี่' : 'คุณสามารถบันทึกหรือเปิดรูปภาพได้ที่นี่'}
                    </p>
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <button id="btnFallbackDownloadAll" class="swal2-confirm swal2-styled" style="background-color: #1a5c3a; margin: 0; width: 100%; font-size: 0.92rem; padding: 10px;">
                            💾 ดาวน์โหลด${isMultiple ? 'ทั้ง 2 รูป' : 'รูปภาพ'}
                        </button>
                        <button id="btnFallbackOpenAll" class="swal2-confirm swal2-styled" style="background-color: #0284c7; margin: 0; width: 100%; font-size: 0.92rem; padding: 10px;">
                            🌐 เปิดดู${isMultiple ? 'ทั้ง 2 รูป' : 'รูปภาพ'}ในแท็บใหม่
                        </button>
                        ${inLine ? `
                            <button id="btnFallbackLineExternal" class="swal2-confirm swal2-styled" style="background-color: #06c755; margin: 0; width: 100%; font-size: 0.92rem; padding: 10px;">
                                🚀 เปิดใน Safari/Chrome (แชร์ผ่านระบบมือถือ)
                            </button>
                        ` : ''}
                    </div>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            didOpen: () => {
                document.getElementById('btnFallbackDownloadAll')?.addEventListener('click', () => {
                    triggerBatchDownload(urls, files);
                    Swal.close();
                });
                document.getElementById('btnFallbackOpenAll')?.addEventListener('click', () => {
                    urls.forEach(u => window.open(u, '_blank'));
                    Swal.close();
                });
                document.getElementById('btnFallbackLineExternal')?.addEventListener('click', () => {
                    const separator = window.location.href.includes('?') ? '&' : '?';
                    window.location.href = window.location.href + separator + 'openExternalBrowser=1';
                });
            }
        });
    }

    function triggerBatchDownload(urls, files) {
        if (files && files.length > 0) {
            files.forEach((file, idx) => {
                setTimeout(() => {
                    const a = document.createElement('a');
                    a.href = URL.createObjectURL(file);
                    a.download = file.name || `csh_donation_${idx + 1}.jpg`;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    setTimeout(() => URL.revokeObjectURL(a.href), 1500);
                }, idx * 300);
            });
        } else {
            urls.forEach((u, idx) => {
                setTimeout(() => {
                    const a = document.createElement('a');
                    a.href = u;
                    a.download = `csh_donation_${idx + 1}.jpg`;
                    a.target = '_blank';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                }, idx * 300);
            });
        }
    }

    // View Mode Management (Table vs Card)
    let currentViewMode = localStorage.getItem('csh_admin_view_mode') || (window.innerWidth <= 992 ? 'card' : 'table');

    function switchViewMode(mode) {
        currentViewMode = mode;
        localStorage.setItem('csh_admin_view_mode', mode);
        applyViewModeUI();
    }

    function applyViewModeUI() {
        const tableView = document.querySelector('.desktop-table-view');
        const cardsView = document.getElementById('mobileCardsContainer');
        const btnTable = document.getElementById('btnViewTable');
        const btnCard = document.getElementById('btnViewCard');

        if (!tableView || !cardsView) return;

        if (currentViewMode === 'table') {
            tableView.style.setProperty('display', 'block', 'important');
            cardsView.style.setProperty('display', 'none', 'important');
            btnTable?.classList.add('active');
            btnCard?.classList.remove('active');
        } else {
            tableView.style.setProperty('display', 'none', 'important');
            cardsView.style.setProperty('display', 'flex', 'important');
            btnCard?.classList.add('active');
            btnTable?.classList.remove('active');
        }
    }

    window.addEventListener('resize', () => {
        if (!localStorage.getItem('csh_admin_view_mode')) {
            const detected = window.innerWidth <= 992 ? 'card' : 'table';
            if (detected !== currentViewMode) {
                currentViewMode = detected;
                applyViewModeUI();
            }
        }
    });

    function formatShortDate(dateVal) {
        if (!dateVal) return '—';
        const str = String(dateVal).trim();
        if (str.includes('/')) return str;
        try {
            const d = new Date(str);
            if (!isNaN(d.getTime())) {
                const day = d.toLocaleDateString('th-TH', { day: '2-digit', month: '2-digit', year: 'numeric' });
                const time = d.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
                return `${day} ${time}`;
            }
        } catch(e) {}
        return str;
    }

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
