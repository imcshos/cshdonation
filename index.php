<?php
require_once __DIR__ . '/api/config.php';
$config = get_app_config();
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <title>ร่วมส่งต่อโอกาส โรงพยาบาลเชียงแสน | e-Donation</title>
    <link rel="icon" type="image/png" href="images/hospital.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700&family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        :root {
            --teal: #1a5c3a;
            --teal-light: #2d7a52;
            --teal-bright: #3a9e6a;
            --teal-soft: #e8f5ee;
            --teal-pale: #f2faf6;
            --yellow: #f59e0b;
            --purple: #2e1c4a;
            --ink: #1f2937;
            --muted: #64748b;
            --shadow-card: 0 12px 36px rgba(26, 92, 58, 0.08);
            --radius-panel: 24px;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Sarabun', sans-serif;
            color: var(--ink);
            background: linear-gradient(150deg, #edf7f2 0%, #f7faf5 45%, #fbfdf9 100%);
            min-height: 100vh;
            line-height: 1.7;
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, .font-kanit {
            font-family: 'Kanit', sans-serif;
        }

        button, input, textarea {
            font-family: inherit;
        }

        .donation-page {
            max-width: 1080px;
            margin: 0 auto;
            padding: 24px 16px 50px;
        }

        /* Top Bar */
        .donation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            margin-bottom: 22px;
            background: rgba(255, 255, 255, 0.92);
            padding: 14px 20px;
            border-radius: 20px;
            box-shadow: 0 4px 18px rgba(26, 92, 58, 0.05);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }

        .brand-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-logo {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: linear-gradient(135deg, var(--teal), var(--teal-light));
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 22px;
            box-shadow: 0 6px 14px rgba(26, 92, 58, 0.22);
            flex-shrink: 0;
        }

        .brand-text h1 {
            font-size: clamp(1.05rem, 3vw, 1.25rem);
            color: var(--teal);
            font-weight: 700;
            line-height: 1.25;
            margin: 0;
        }

        .brand-text small {
            color: var(--muted);
            font-size: 0.8rem;
            display: block;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-sheet-link {
            background: #ffffff;
            border: 1px solid #d4e5dc;
            border-radius: 12px;
            padding: 8px 14px;
            color: #1a5c3a;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
            white-space: nowrap;
            cursor: pointer;
        }

        .btn-sheet-link:hover {
            background: var(--teal-soft);
            color: var(--teal);
            border-color: #b3d9c7;
        }

        /* Glass Panel */
        .glass-panel {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid #ffffff;
            border-radius: var(--radius-panel);
            padding: clamp(18px, 3.5vw, 32px);
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(8px);
        }

        /* Intro Banner */
        .intro-banner-card {
            margin-bottom: 22px;
            text-align: center;
            padding: clamp(18px, 3vw, 24px);
        }

        /* Hero Grid */
        .hero-layout {
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            gap: 22px;
            align-items: stretch;
        }

        /* Bank Account Card */
        .bank-info-card {
            background: #f4faf6;
            border: 1px solid #d4ece0;
            border-radius: 18px;
            padding: 16px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .bank-details small {
            color: var(--muted);
            font-size: 0.8rem;
            display: block;
        }

        .bank-details strong {
            font-size: 1.05rem;
            color: var(--teal);
            display: block;
            margin-top: 1px;
            font-family: 'Kanit', sans-serif;
        }

        .bank-account-num {
            font-family: 'Kanit', sans-serif;
            font-size: clamp(1.3rem, 4vw, 1.65rem);
            font-weight: 700;
            letter-spacing: 1px;
            color: var(--purple);
            margin: 2px 0;
            word-break: break-all;
        }

        .btn-copy-account {
            background: #ffffff;
            border: 1px solid #b8dfcd;
            color: var(--teal);
            padding: 8px 14px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
        }

        .btn-copy-account:hover {
            background: var(--teal);
            color: white;
            border-color: var(--teal);
        }

        /* QR Code Panel */
        .qr-side-card {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 24px 18px;
        }

        .qr-box-frame {
            background: #ffffff;
            border-radius: 18px;
            padding: 14px;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            border: 1px solid #e1ece6;
            width: 100%;
            max-width: 290px;
            margin: 0 auto;
        }

        .qr-box-frame img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            display: block;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .qr-box-frame img:hover {
            transform: scale(1.02);
        }

        .qr-caption {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--teal);
            margin: 14px 0 3px;
        }

        .qr-subcaption {
            color: var(--muted);
            font-size: 0.82rem;
            margin-bottom: 12px;
        }

        .qr-action-buttons {
            display: flex;
            gap: 8px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-qr-action {
            background: #eef7f2;
            color: var(--teal);
            border: 1px solid #cce5da;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            transition: 0.2s;
        }

        .btn-qr-action:hover {
            background: var(--teal);
            color: white;
            border-color: var(--teal);
        }

        /* Form Section */
        .form-section-panel {
            margin-top: 24px;
        }

        .section-heading {
            font-size: 1.35rem;
            color: var(--teal);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .section-subtext {
            color: var(--muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .form-row {
            margin-bottom: 18px;
        }

        .form-field-label {
            display: block;
            font-weight: 600;
            margin-bottom: 6px;
            color: #334155;
            font-size: 0.92rem;
        }

        .form-input-text {
            width: 100%;
            border: 1.5px solid #d4e2dc;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fafdfc;
            outline: none;
            font-size: 16px;
            color: #1f2937;
            transition: 0.2s;
        }

        .form-input-text:focus {
            border-color: var(--teal);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(26, 92, 58, 0.12);
        }

        textarea.form-input-text {
            min-height: 110px;
            resize: vertical;
            line-height: 1.6;
        }

        .character-counter {
            text-align: right;
            font-size: 0.78rem;
            color: var(--muted);
            margin-top: 4px;
        }

        /* Upload Section */
        .photo-upload-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .photo-upload-box {
            border: 2px dashed #b9dbce;
            border-radius: 16px;
            padding: 14px;
            background: #f8fbf9;
            text-align: center;
            transition: 0.2s;
        }

        .photo-upload-box:hover {
            border-color: var(--teal);
            background: #f2f8f5;
        }

        .photo-upload-box label {
            display: block;
            font-weight: 600;
            font-size: 0.88rem;
            color: var(--teal);
            margin-bottom: 6px;
            cursor: pointer;
        }

        .photo-upload-box input[type="file"] {
            width: 100%;
            font-size: 0.82rem;
            color: #64748b;
            cursor: pointer;
        }

        .uploaded-thumbnail {
            display: none;
            width: 100%;
            max-height: 180px;
            object-fit: contain;
            border-radius: 10px;
            margin-top: 10px;
            background: #ffffff;
            border: 1px solid #e2eee8;
            padding: 4px;
            cursor: pointer;
        }

        /* Action Buttons */
        .form-buttons-bar {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 22px;
        }

        .btn-send-donation {
            background: linear-gradient(135deg, var(--teal), var(--teal-light));
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 0.98rem;
            font-weight: 600;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 6px 18px rgba(26, 92, 58, 0.25);
            transition: 0.2s;
            flex-grow: 1;
        }

        .btn-send-donation:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(26, 92, 58, 0.35);
        }

        .btn-send-donation:disabled {
            opacity: 0.65;
            cursor: not-allowed;
            transform: none;
        }

        .btn-clear-form {
            background: #f1f5f9;
            color: #475569;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 0.92rem;
            font-weight: 500;
            border: none;
            cursor: pointer;
            transition: 0.2s;
        }

        .btn-clear-form:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        /* Wall of Kindness / Wishes Board */
        .kindness-board-panel {
            margin-top: 35px;
        }

        .kindness-board-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 16px;
        }

        .wishes-masonry {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
        }

        .wish-item-card {
            background: #ffffff;
            border: 1px solid #e6f0eb;
            border-radius: 16px;
            padding: 18px;
            box-shadow: 0 4px 14px rgba(26, 92, 58, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: 0.2s;
        }

        .wish-item-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(26, 92, 58, 0.08);
            border-color: #c5e3d4;
        }

        .wish-user {
            font-weight: 600;
            color: var(--teal);
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .wish-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--teal-soft), #d4ece0);
            color: var(--teal);
            display: grid;
            place-items: center;
            font-size: 0.85rem;
            font-weight: 700;
            flex-shrink: 0;
        }

        .wish-time {
            font-size: 0.78rem;
            color: #94a3b8;
        }

        .wish-quote {
            font-size: 0.9rem;
            color: #334155;
            line-height: 1.65;
            margin: 10px 0 12px;
            white-space: pre-wrap;
            word-break: break-word;
        }

        .wish-attachments {
            display: flex;
            gap: 8px;
            margin-top: auto;
            padding-top: 8px;
            border-top: 1px dashed #e2e8f0;
        }

        .wish-img-thumb {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            transition: 0.2s;
        }

        .wish-img-thumb:hover {
            transform: scale(1.1);
            border-color: var(--teal);
        }

        /* Page Footer & Admin Access */
        .donation-page-footer {
            margin-top: 45px;
            padding-top: 25px;
            border-top: 1px solid #dbe8e1;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
        }

        .donation-page-footer p {
            margin-bottom: 6px;
        }

        .footer-admin-link-wrapper {
            margin-top: 16px;
            display: flex;
            justify-content: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-bottom-admin {
            background: #ffffff;
            border: 1px solid #d4dfd9;
            color: #475569;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: 0.2s;
            box-shadow: 0 2px 6px rgba(0,0,0,0.03);
            cursor: pointer;
        }

        .btn-bottom-admin:hover {
            background: var(--teal-soft);
            color: var(--teal);
            border-color: #b8ded0;
        }

        /* Modal Image Viewer */
        .image-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.88);
            display: none;
            place-items: center;
            padding: 16px;
            z-index: 9999;
            backdrop-filter: blur(4px);
        }

        .image-modal-content {
            max-width: 95vw;
            max-height: 90vh;
            border-radius: 14px;
            overflow: hidden;
            position: relative;
        }

        .image-modal-content img {
            max-width: 100%;
            max-height: 85vh;
            display: block;
            border-radius: 10px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.4);
        }

        .btn-close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.65);
            color: white;
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
            display: grid;
            place-items: center;
            z-index: 10;
        }

        /* Setup / Config Modal */
        .config-modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            display: none;
            place-items: center;
            padding: 16px;
            z-index: 9998;
            backdrop-filter: blur(4px);
            overflow-y: auto;
        }

        .config-modal-card {
            background: #ffffff;
            border-radius: 20px;
            max-width: 650px;
            width: 100%;
            padding: 24px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            position: relative;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Spinner */
        .btn-spinner {
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #ffffff;
            border-radius: 50%;
            display: inline-block;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .receipt-note-mobile {
            display: inline;
            margin-left: 4px;
        }

        .brand-title-sub {
            display: inline;
        }

        .mobile-only-break {
            display: none;
        }

        /* 📱 Mobile Responsive Optimization */
        @media (max-width: 768px) {
            .donation-page {
                padding: 14px 12px 35px;
            }

            .hero-layout {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .qr-side-card {
                order: -1;
                padding: 20px 14px;
            }

            .qr-box-frame {
                max-width: 250px;
                padding: 10px;
            }

            .glass-panel {
                padding: 20px 16px;
                border-radius: 20px;
            }

            .photo-upload-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .bank-info-card {
                flex-direction: column;
                align-items: stretch;
                text-align: center;
                padding: 14px;
            }

            .btn-copy-account {
                justify-content: center;
                width: 100%;
                padding: 10px;
            }

            .form-buttons-bar {
                flex-direction: column;
            }

            .btn-send-donation, .btn-clear-form {
                width: 100%;
                padding: 13px;
            }

            .wishes-masonry {
                grid-template-columns: 1fr;
            }

            .receipt-note-mobile {
                display: block;
                margin-left: 0;
                margin-top: 3px;
                font-size: 0.88rem;
            }

            .brand-title-sub {
                display: block !important;
                margin-top: 2px;
            }

            .mobile-only-break {
                display: block !important;
            }
        }
    </style>
</head>
<body>

<div class="donation-page">
    
    <!-- Top Header -->
    <header class="donation-header">
        <div class="brand-box">
            <div class="brand-logo">
                <img src="images/hospital.png" alt="รพ.เชียงแสน" style="width: 26px; height: 26px; object-fit: contain;">
            </div>
            <div class="brand-text">
                <h1><span>ร่วมส่งต่อโอกาส</span><br class="mobile-only-break"><span class="brand-title-sub">โรงพยาบาลเชียงแสน</span></h1>
                <small><?php echo htmlspecialchars($config['hospital_subtitle']); ?></small>
            </div>
        </div>
        <div class="header-actions">
            <a href="admin.php" class="btn-sheet-link" title="จัดการข้อมูลบริจาค (Admin)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                <span>จัดการ (Admin)</span>
            </a>
            <?php if (!empty($config['google_sheet_url'])): ?>
                <a href="<?php echo htmlspecialchars($config['google_sheet_url']); ?>" target="_blank" rel="noopener" class="btn-sheet-link" title="เปิดดู Google Sheet">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    <span>Google Sheet</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- Intro Announcement Card -->
    <div class="glass-panel intro-banner-card">
        <p style="font-size: clamp(0.96rem, 2.5vw, 1.05rem); color: #1e293b; font-weight: 500; line-height: 1.75; margin: 0 0 12px 0;">
            ขอเชิญร่วมบริจาคเพื่อสนับสนุนการจัดซื้ออุปกรณ์การแพทย์และเพื่อการดำเนินงานของโรงพยาบาลเชียงแสน ทุกการให้ของท่านช่วยเพิ่มโอกาสในการเข้าถึงการรักษาที่มีคุณภาพ
        </p>
        <div style="display: inline-flex; align-items: center; justify-content: center; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 6px 18px; border-radius: 20px; font-size: 0.92rem; font-weight: 600; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
            สามารถใช้สิทธิลดหย่อนภาษีได้ 2 เท่า
        </div>
    </div>

    <!-- Main Hero -->
    <div class="hero-layout">
        
        <!-- Left Info Panel -->
        <div class="glass-panel">
            <div style="text-align: center; margin-bottom: 14px;">
                <div style="display: inline-flex; flex-direction: column; align-items: center; justify-content: center; background: #eef2ff; color: #3730a3; padding: 6px 16px; border-radius: 16px; font-size: 0.88rem; font-weight: 700; font-family: 'Kanit'; border: 1px solid #c7d2fe; text-align: center; line-height: 1.45;">
                    <div>ออกใบอนุโมทนาบัตร</div>
                    <div>บริจาคโดยใช้ชื่อผู้อื่น/นิติบุคคล</div>
                </div>
            </div>

            <div class="bank-info-card">
                <div class="bank-details">
                    <small>บัญชีธนาคารสำหรับรับเงินบริจาค</small>
                    <strong><?php echo htmlspecialchars($config['account_name']); ?></strong>
                    <div class="bank-account-num" id="accNumber"><?php echo htmlspecialchars($config['account_number']); ?></div>
                    <small style="color: #64748b;"><?php echo htmlspecialchars($config['bank_name']); ?></small>
                </div>
                <div>
                    <button type="button" class="btn-copy-account" onclick="copyAccountNumber()">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                        คัดลอกเลขบัญชี
                    </button>
                </div>
            </div>

            <!-- ความประสงค์ขอรับใบอนุโมทนาบัตร (กรณีบริจาคผ่านเลขบัญชีธนาคาร) -->
            <div style="background: #f8fbf9; border: 1.5px solid #d4ece0; border-radius: 16px; padding: 16px 18px; margin-top: 16px;">
                <label class="form-field-label" style="font-size: 0.95rem; color: #1a5c3a; margin-bottom: 10px;">
                    <span>ความประสงค์ขอรับใบอนุโมทนาบัตร</span><span class="receipt-note-mobile">(กรณีบริจาคผ่านเลขบัญชีธนาคาร)</span>
                </label>
                
                <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 6px;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; font-size: 0.95rem;">
                        <input type="radio" name="receipt_requested" value="0" checked onchange="toggleReceiptFields(false)" style="accent-color: var(--teal); width: 18px; height: 18px;">
                        <span>ไม่รับ</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #1a5c3a; font-size: 0.95rem;">
                        <input type="radio" name="receipt_requested" value="1" id="receiptRadioYes" onchange="toggleReceiptFields(true)" style="accent-color: var(--teal); width: 18px; height: 18px;">
                        <span>รับใบอนุโมทนาบัตร</span>
                    </label>
                </div>

                <!-- ช่องกรอกข้อมูลใบอนุโมทนาบัตร -->
                <div id="receiptFieldsBox" style="display: none; margin-top: 12px; padding-top: 14px; border-top: 1px dashed #cde5da;">
                    <div style="background: #ffffff; border: 1px solid #e1ece6; border-radius: 12px; padding: 16px;">
                        <div style="font-size: 0.82rem; color: #475569; margin-bottom: 14px; line-height: 1.5;">
                            กรุณากรอกข้อมูลให้ครบถ้วนเพื่อความถูกต้องในการออกและจัดส่งเอกสารใบอนุโมทนาบัตร
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            <div>
                                <label class="form-field-label" for="tax_name" style="font-size: 0.88rem; margin-bottom: 5px;">
                                    ชื่อ-สกุล / ชื่อนิติบุคคลที่จะนำไปลดหย่อนภาษี <span style="color: #dc2626;">*</span>
                                </label>
                                <input type="text" id="tax_name" name="tax_name" class="form-input-text" placeholder="ระบุชื่อ-นามสกุล หรือชื่อบริษัท/ห้างหุ้นส่วน">
                            </div>

                            <div>
                                <label class="form-field-label" for="tax_id" style="font-size: 0.88rem; margin-bottom: 5px;">
                                    เลขบัตรประชาชน / เลขผู้เสียภาษี <span style="color: #dc2626;">*</span>
                                </label>
                                <input type="text" id="tax_id" name="tax_id" class="form-input-text" placeholder="ระบุเลข 13 หลัก" maxlength="25">
                            </div>

                            <div>
                                <label class="form-field-label" for="phone" style="font-size: 0.88rem; margin-bottom: 5px;">
                                    เบอร์โทร <span style="color: #dc2626;">*</span>
                                </label>
                                <input type="tel" id="phone" name="phone" class="form-input-text" placeholder="เช่น 081-234-5678" maxlength="50">
                            </div>

                            <div>
                                <label class="form-field-label" for="address" style="font-size: 0.88rem; margin-bottom: 5px;">
                                    ที่อยู่เพื่อจัดส่งเอกสาร <span style="color: #dc2626;">*</span>
                                </label>
                                <textarea id="address" name="address" class="form-input-text" style="min-height: 80px;" placeholder="ระบุบ้านเลขที่ หมู่ ถนน ตำบล อำเภอ จังหวัด และรหัสไปรษณีย์"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right QR Code Panel -->
        <div class="glass-panel qr-side-card">
            <div style="background: #e1f5ec; color: #0c623d; padding: 6px 16px; border-radius: 16px; font-size: 0.88rem; font-weight: 700; font-family: 'Kanit'; margin-bottom: 12px; display: inline-flex; flex-direction: column; align-items: center; justify-content: center; border: 1px solid #b7e3cf; text-align: center; line-height: 1.45;">
                <div>ข้อมูลเข้าสรรพากรโดยตรง</div>
                <div>บริจาคโดยใช้ชื่อตนเอง</div>
            </div>
            <div class="qr-box-frame">
                <img src="images/donation-qr.jpg" id="qrPreviewImage" alt="QR Code สำหรับบริจาค e-Donation โรงพยาบาลเชียงแสน" onclick="openImageModal(this.src)">
            </div>
            <div class="qr-caption font-kanit">สแกนบริจาคผ่าน Mobile Banking</div>
            <div class="qr-subcaption">รองรับทุกแอปพลิเคชันธนาคารผ่าน PromptPay / e-Donation</div>
            
            <div class="qr-action-buttons">
                <button type="button" class="btn-qr-action" onclick="openImageModal('images/donation-qr.jpg')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    ดูภาพขยาย
                </button>
                <a href="images/donation-qr.jpg" download="ChiangSaen-Hospital-eDonation-QR.jpg" class="btn-qr-action" style="background: #ffffff;" onclick="handleQrDownload(event, 'images/donation-qr.jpg')">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    บันทึกรูป QR
                </a>
            </div>
        </div>

    </div>

    <!-- Form Section -->
    <div class="glass-panel form-section-panel">
        <h2 class="section-heading font-kanit">ฝากข้อความและแบ่งปันช่วงเวลาดี ๆ</h2>
        <p class="section-subtext">
            พิมพ์ข้อความให้กำลังใจ หรือฝากคำอวยพรถึงทีมแพทย์ พยาบาล และบุคลากรทางการแพทย์ พร้อมแนบภาพถ่ายหรือสลิปการโอน
        </p>

        <form id="donationForm" enctype="multipart/form-data">
            
            <div class="form-row">
                <label class="form-field-label" for="donor_name">ชื่อผู้ร่วมทำบุญ / ผู้ส่งข้อความ (ไม่บังคับ)</label>
                <input type="text" id="donor_name" name="donor_name" class="form-input-text" placeholder="เช่น คุณสมชาย ใจดี หรือ เว้นว่างเพื่อไม่ประสงค์ออกนาม" maxlength="150">
            </div>

            <div class="form-row">
                <label class="form-field-label" for="message">ข้อความของคุณ (ให้กำลังใจ / คำอวยพร)</label>
                <textarea id="message" name="message" class="form-input-text" placeholder="ขอร่วมส่งกำลังใจให้ทีมแพทย์ พยาบาล และเจ้าหน้าที่โรงพยาบาลเชียงแสนทุกท่าน..." maxlength="3000" oninput="updateCharCount(this)"></textarea>
                <div class="character-counter"><span id="charCount">0</span> / 3,000 ตัวอักษร</div>
            </div>

            <div class="form-row">
                <label class="form-field-label">แนบรูปภาพ</label>
                <div class="photo-upload-grid">
                    
                    <!-- Upload 1 -->
                    <div class="photo-upload-box">
                        <label for="img1">รูปภาพที่ 1: ภาพถ่าย</label>
                        <input id="img1" name="image1" type="file" accept="image/png,image/jpeg,image/webp" onchange="previewSelectedImage(this, 'prev1')">
                        <img class="uploaded-thumbnail" id="prev1" alt="ตัวอย่างรูปภาพที่ 1" onclick="openImageModal(this.src)">
                    </div>

                    <!-- Upload 2 -->
                    <div class="photo-upload-box">
                        <label for="img2">รูปภาพที่ 2: ภาพสลิปโอนเงินบริจาค</label>
                        <input id="img2" name="image2" type="file" accept="image/png,image/jpeg,image/webp" onchange="previewSelectedImage(this, 'prev2')">
                        <img class="uploaded-thumbnail" id="prev2" alt="ตัวอย่างรูปภาพที่ 2" onclick="openImageModal(this.src)">
                    </div>

                </div>
            </div>

            <!-- ตัวเลือกอนุญาตให้เผยแพร่ข้อมูลเพื่อการขอบคุณ -->
            <div class="form-row" style="margin-top: 18px; margin-bottom: 22px; background: #f8fbf9; border: 1.5px solid #d4ece0; border-radius: 14px; padding: 14px 18px;">
                <label class="form-field-label" style="font-size: 0.95rem; color: #1a5c3a; margin-bottom: 8px;">
                    อนุญาตให้เผยแพร่ข้อมูลเพื่อการขอบคุณ
                </label>
                <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 600; color: #1a5c3a; font-size: 0.95rem;">
                        <input type="radio" name="allow_social_post" value="1" checked style="accent-color: var(--teal); width: 18px; height: 18px;">
                        <span>อนุญาต</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 8px; cursor: pointer; font-weight: 500; font-size: 0.95rem;">
                        <input type="radio" name="allow_social_post" value="0" style="accent-color: var(--teal); width: 18px; height: 18px;">
                        <span>ไม่อนุญาต</span>
                    </label>
                </div>
            </div>

            <div class="form-buttons-bar">
                <button type="submit" id="saveBtn" class="btn-send-donation font-kanit">
                    <span id="btnText">บันทึก</span>
                </button>
                <button type="button" class="btn-clear-form" onclick="resetDonationForm()">
                    ล้างข้อมูล
                </button>
            </div>
        </form>
    </div>

    <!-- Wall of Kindness (กระดานข้อความความปรารถนาดี) -->
    <div class="glass-panel kindness-board-panel" id="kindnessBoardPanel">
        <div class="kindness-board-header">
            <div>
                <h3 class="font-kanit" style="font-size: 1.25rem; color: var(--teal); margin-bottom: 2px;">
                    ข้อความแห่งน้ำใจและการแบ่งปัน
                </h3>
                <small style="color: var(--muted);">คำอวยพรและกำลังใจจากผู้มีจิตศรัทธาที่ร่วมบริจาค</small>
            </div>
            <button type="button" onclick="loadWishes()" style="background: none; border: none; color: var(--teal); cursor: pointer; font-size: 0.82rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6"></path><path d="M1 20v-6h6"></path><path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path></svg>
                รีเฟรช
            </button>
        </div>
        <div class="wishes-masonry" id="wishesContainer">
            <!-- Loaded dynamically via JavaScript -->
            <div style="grid-column: 1 / -1; text-align: center; color: var(--muted); padding: 24px 0;">
                กำลังโหลดข้อความ...
            </div>
        </div>
    </div>

    <!-- Footer of the Page & Admin Management -->
    <footer class="donation-page-footer">
        <p><strong>ด้วยความขอบคุณจาก <?php echo htmlspecialchars($config['hospital_name']); ?></strong></p>
        <p><?php echo htmlspecialchars($config['hospital_address']); ?> · โทร: <?php echo htmlspecialchars($config['hospital_phone']); ?></p>
        
        <div class="footer-admin-link-wrapper">
            <a href="admin.php" class="btn-bottom-admin" style="background: #1a5c3a; color: white; border-color: #1a5c3a; font-weight: 600;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                จัดการข้อมูลการบริจาค (Admin)
            </a>
            <button type="button" class="btn-bottom-admin" onclick="openConfigModal()">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                ตั้งค่า Google Sheet / สถานะระบบ
            </button>
            <?php if (!empty($config['google_sheet_url'])): ?>
                <a href="<?php echo htmlspecialchars($config['google_sheet_url']); ?>" target="_blank" rel="noopener" class="btn-bottom-admin">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z"/></svg>
                    เปิดดูฐานข้อมูล Google Sheet
                </a>
            <?php endif; ?>
        </div>
    </footer>

</div>

<!-- Modal Fullscreen Image Viewer -->
<div class="image-modal-backdrop" id="imageModal" onclick="closeImageModal()">
    <div class="image-modal-content" onclick="event.stopPropagation()">
        <button type="button" class="btn-close-modal" onclick="closeImageModal()">✕</button>
        <img id="modalPreviewImg" src="" alt="ขยายรูปภาพ" style="-webkit-touch-callout: default !important; -webkit-user-select: auto !important; user-select: auto !important; pointer-events: auto !important; cursor: pointer;">
        <div style="color: #e2e8f0; font-size: 0.78rem; text-align: center; background: rgba(0,0,0,0.5); padding: 6px 12px; border-radius: 6px; margin-top: 8px;">
            📱 แตะค้างที่รูปภาพเพื่อเลือก <strong>"บันทึกรูปภาพ (Save Image)"</strong> ได้ทันทีค่ะ
        </div>
    </div>
</div>

<!-- Modal Google Sheet Setup & Status -->
<div class="config-modal-backdrop" id="configModal" onclick="closeConfigModal()">
    <div class="config-modal-card" onclick="event.stopPropagation()">
        <button type="button" class="btn-close-modal" onclick="closeConfigModal()" style="top: 16px; right: 16px; background: #e2e8f0; color: #334155;">✕</button>
        
        <h3 class="font-kanit" style="color: var(--teal); font-size: 1.35rem; margin-bottom: 6px;">
            ตั้งค่าเชื่อมต่อ Google Sheet
        </h3>
        <p style="color: var(--muted); font-size: 0.85rem; margin-bottom: 18px;">
            กำหนด URL ของ Google Apps Script Web App เพื่อใช้ Google Sheets เป็นฐานข้อมูลรับข้อมูลการบริจาค
        </p>

        <form id="configForm" onsubmit="saveConfiguration(event)">
            <div class="form-row">
                <label class="form-field-label" for="cfg_script_url">
                    Google Apps Script Web App URL
                </label>
                <input type="url" id="cfg_script_url" class="form-input-text" placeholder="https://script.google.com/macros/s/.../exec" value="<?php echo htmlspecialchars($config['google_script_url'] ?? ''); ?>" style="font-size: 0.88rem;">
                <small style="color: var(--muted); font-size: 0.78rem; display: block; margin-top: 4px;">
                    ได้จากการกด "ทำให้ใช้งานได้" (Deploy) > "เว็บแอป" (Web app) > สิทธิ์ "ทุกคน" (Anyone)
                </small>
            </div>

            <div class="form-row">
                <label class="form-field-label" for="cfg_sheet_url">
                    ลิงก์ Google Sheet สำหรับกดดูข้อมูล (ไม่บังคับ)
                </label>
                <input type="url" id="cfg_sheet_url" class="form-input-text" placeholder="https://docs.google.com/spreadsheets/d/.../edit" value="<?php echo htmlspecialchars($config['google_sheet_url'] ?? ''); ?>" style="font-size: 0.88rem;">
            </div>

            <div style="display: flex; gap: 10px; margin-top: 14px; margin-bottom: 20px; flex-wrap: wrap;">
                <button type="submit" id="btnSaveConfig" class="btn-send-donation font-kanit" style="padding: 10px 18px; font-size: 0.9rem;">
                    บันทึกการตั้งค่า
                </button>
                <button type="button" class="btn-clear-form" onclick="testConnection()" style="padding: 10px 16px; font-size: 0.9rem; background: #eef7f2; color: var(--teal); border: 1px solid #b8dfcd;">
                    ทดสอบการเชื่อมต่อ
                </button>
            </div>
        </form>

        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px; margin-top: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <strong style="color: #334155; font-size: 0.9rem;">โค้ด Google Apps Script (Code.gs)</strong>
                <button type="button" onclick="copyAppsScriptCode()" style="background: var(--teal); color: white; border: none; border-radius: 6px; padding: 4px 10px; font-size: 0.78rem; cursor: pointer;">
                    คัดลอกโค้ด
                </button>
            </div>
            <p style="color: #64748b; font-size: 0.8rem; line-height: 1.5; margin-bottom: 8px;">
                นำโค้ดในไฟล์ <code>google-apps-script/Code.gs</code> ไปวางใน Google Sheet (เมนู ส่วนขยาย > Apps Script) แล้วกด Deploy เป็น Web App
            </p>
            <div style="text-align: right;">
                <a href="google-apps-script/Code.gs" target="_blank" style="color: var(--teal); font-size: 0.82rem; font-weight: 600; text-decoration: none;">
                    เปิดดูไฟล์ Code.gs ↗
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Copy Account Number
    function copyAccountNumber() {
        const acc = document.getElementById('accNumber').innerText.trim();
        const cleanAcc = acc.replace(/-/g, '');
        
        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(cleanAcc).then(() => {
                showCopySuccess(acc);
            }).catch(() => fallbackCopy(cleanAcc, acc));
        } else {
            fallbackCopy(cleanAcc, acc);
        }
    }

    function fallbackCopy(cleanAcc, formattedAcc) {
        const tempInput = document.createElement('input');
        tempInput.value = cleanAcc;
        document.body.appendChild(tempInput);
        tempInput.select();
        try {
            document.execCommand('copy');
            showCopySuccess(formattedAcc);
        } catch (err) {
            Swal.fire('เลขที่บัญชี', formattedAcc, 'info');
        }
        document.body.removeChild(tempInput);
    }

    function showCopySuccess(acc) {
        Swal.fire({
            icon: 'success',
            title: 'คัดลอกเลขบัญชีแล้ว',
            text: acc,
            timer: 1800,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }

    // Update Char Count
    function updateCharCount(textarea) {
        document.getElementById('charCount').innerText = textarea.value.length;
    }

    // Preview Selected Image
    function previewSelectedImage(input, previewId) {
        const file = input.files && input.files[0];
        const previewEl = document.getElementById(previewId);

        if (!file) {
            previewEl.style.display = 'none';
            previewEl.src = '';
            return;
        }

        // Limit 5 MB
        if (file.size > 5 * 1024 * 1024) {
            input.value = '';
            previewEl.style.display = 'none';
            previewEl.src = '';
            Swal.fire({
                icon: 'warning',
                title: 'ไฟล์มีขนาดใหญ่เกินไป',
                text: 'กรุณาเลือกไฟล์รูปภาพขนาดไม่เกิน 5 MB ครับ'
            });
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewEl.src = e.target.result;
            previewEl.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }

    // Toggle Receipt Fields
    function toggleReceiptFields(show) {
        const box = document.getElementById('receiptFieldsBox');
        if (box) {
            box.style.display = show ? 'block' : 'none';
        }
    }

    // Reset Form
    function resetDonationForm() {
        document.getElementById('donationForm').reset();
        document.getElementById('charCount').innerText = '0';
        
        const rNo = document.querySelector('input[name="receipt_requested"][value="0"]');
        if (rNo) rNo.checked = true;
        ['tax_name', 'tax_id', 'phone', 'address'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
        toggleReceiptFields(false);

        ['prev1', 'prev2'].forEach(id => {
            const el = document.getElementById(id);
            el.src = '';
            el.style.display = 'none';
        });
    }

    // Fullscreen Image Modal
    function isLineApp() {
        const ua = (navigator.userAgent || navigator.vendor || window.opera || '').toLowerCase();
        return ua.includes('line/') || ua.includes('line ') || /Line/i.test(navigator.userAgent);
    }

    function handleQrDownload(e, src) {
        if (isLineApp()) {
            e.preventDefault();
            openImageModal(src);
            Swal.fire({
                icon: 'info',
                title: 'วิธีบันทึกรูป QR บนแอป LINE',
                html: '<div style="background:#e8f5ee; padding:12px; border-radius:10px; color:#166534; font-size:0.92rem; text-align:left;">👉 <b>แตะค้างที่รูปภาพ QR</b> ด้านบน แล้วกด <b>"บันทึกรูปภาพ (Save Image)"</b> ได้เลยค่ะ</div>',
                confirmButtonColor: '#1a5c3a',
                confirmButtonText: 'เข้าใจแล้ว'
            });
        }
    }

    function openImageModal(src) {
        if (!src) return;
        const modal = document.getElementById('imageModal');
        const img = document.getElementById('modalPreviewImg');
        img.src = src;
        modal.style.display = 'grid';
    }

    function closeImageModal() {
        document.getElementById('imageModal').style.display = 'none';
    }

    // Config Modal
    function openConfigModal() {
        document.getElementById('configModal').style.display = 'grid';
    }

    function closeConfigModal() {
        document.getElementById('configModal').style.display = 'none';
    }

    // ESC to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
            closeConfigModal();
        }
    });

    // Submit Donation Form
    document.getElementById('donationForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const btn = document.getElementById('saveBtn');
        const btnText = document.getElementById('btnText');
        const messageVal = document.getElementById('message').value.trim();
        const img1 = document.getElementById('img1').files[0];
        const img2 = document.getElementById('img2').files[0];

        const isReceiptYes = document.getElementById('receiptRadioYes') && document.getElementById('receiptRadioYes').checked;
        if (isReceiptYes) {
            const taxName = document.getElementById('tax_name').value.trim();
            const taxId = document.getElementById('tax_id').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const address = document.getElementById('address').value.trim();

            if (!taxName) {
                Swal.fire('ข้อมูลไม่ครบถ้วน', 'กรุณาระบุชื่อ-สกุล หรือชื่อนิติบุคคล สำหรับออกใบอนุโมทนาบัตร', 'warning');
                document.getElementById('tax_name').focus();
                return;
            }
            if (!taxId) {
                Swal.fire('ข้อมูลไม่ครบถ้วน', 'กรุณาระบุเลขประจำตัวประชาชน หรือเลขผู้เสียภาษี', 'warning');
                document.getElementById('tax_id').focus();
                return;
            }
            if (!phone) {
                Swal.fire('ข้อมูลไม่ครบถ้วน', 'กรุณาระบุเบอร์โทรศัพท์ติดต่อ', 'warning');
                document.getElementById('phone').focus();
                return;
            }
            if (!address) {
                Swal.fire('ข้อมูลไม่ครบถ้วน', 'กรุณาระบุที่อยู่สำหรับจัดส่งเอกสารใบอนุโมทนาบัตร', 'warning');
                document.getElementById('address').focus();
                return;
            }
        }

        if (!messageVal && !img1 && !img2 && !isReceiptYes) {
            Swal.fire({
                icon: 'warning',
                title: 'ข้อมูลไม่ครบถ้วน',
                text: 'กรุณากรอกข้อความ แนบรูปภาพ หรือระบุข้อมูลใบอนุโมทนาบัตรครับ'
            });
            return;
        }

        if ((img1 && img1.size > 5 * 1024 * 1024) || (img2 && img2.size > 5 * 1024 * 1024)) {
            Swal.fire('ขนาดไฟล์ใหญ่เกินไป', 'แต่ละรูปต้องมีขนาดไม่เกิน 5 MB', 'warning');
            return;
        }

        const formData = new FormData(this);
        formData.set('receipt_requested', isReceiptYes ? '1' : '0');
        if (isReceiptYes) {
            formData.set('tax_name', document.getElementById('tax_name').value.trim());
            formData.set('tax_id', document.getElementById('tax_id').value.trim());
            formData.set('phone', document.getElementById('phone').value.trim());
            formData.set('address', document.getElementById('address').value.trim());
        }

        try {
            btn.disabled = true;
            btnText.innerHTML = '<span class="btn-spinner" style="margin-right: 6px;"></span> กำลังบันทึกข้อมูล...';

            const response = await fetch("api/submit.php", {
                method: 'POST',
                headers: {
                    'Accept': 'application/json'
                },
                body: formData
            });

            const text = await response.text();
            let result = {};
            try {
                result = JSON.parse(text);
            } catch(e) {
                throw new Error('เซิร์ฟเวอร์ส่งการตอบกลับที่ไม่ถูกต้อง โปรดตรวจสอบการตั้งค่า (Server returned HTML instead of JSON)');
            }

            if (response.ok && result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกเรียบร้อยแล้ว',
                    text: result.message,
                    confirmButtonColor: '#1a5c3a',
                    confirmButtonText: 'ตกลง'
                }).then(() => {
                    resetDonationForm();
                    loadWishes();
                });
            } else {
                throw new Error(result.message || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล');
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'ไม่สามารถส่งข้อมูลได้',
                text: error.message || 'โปรดตรวจสอบการเชื่อมต่ออินเทอร์เน็ตแล้วลองใหม่อีกครั้ง'
            });
        } finally {
            btn.disabled = false;
            btnText.innerText = 'บันทึก';
        }
    });

    // Save Configuration
    async function saveConfiguration(e) {
        e.preventDefault();
        const scriptUrl = document.getElementById('cfg_script_url').value.trim();
        const sheetUrl = document.getElementById('cfg_sheet_url').value.trim();
        const btn = document.getElementById('btnSaveConfig');

        try {
            btn.disabled = true;
            btn.innerText = 'กำลังบันทึก...';

            const res = await fetch('api/save_config.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    google_script_url: scriptUrl,
                    google_sheet_url: sheetUrl
                })
            });

            const data = await res.json();
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'บันทึกสำเร็จ',
                    text: data.message,
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    closeConfigModal();
                    location.reload();
                });
            } else {
                Swal.fire('ข้อผิดพลาด', data.message, 'error');
            }
        } catch (err) {
            Swal.fire('ข้อผิดพลาด', err.message, 'error');
        } finally {
            btn.disabled = false;
            btn.innerText = 'บันทึกการตั้งค่า';
        }
    }

    // Test Connection to Google Apps Script
    async function testConnection() {
        const scriptUrl = document.getElementById('cfg_script_url').value.trim();
        if (!scriptUrl) {
            Swal.fire('แจ้งเตือน', 'กรุณาระบุ Google Apps Script Web App URL ก่อนทดสอบครับ', 'warning');
            return;
        }

        Swal.fire({
            title: 'กำลังทดสอบเชื่อมต่อ...',
            text: 'ส่งคำขอไปยัง Google Apps Script',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        try {
            const res = await fetch('api/test_connection.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ google_script_url: scriptUrl })
            });

            const data = await res.json();
            if (data.success) {
                const sheetInfo = data.details || {};
                Swal.fire({
                    icon: 'success',
                    title: 'เชื่อมต่อสำเร็จ!',
                    html: `<p style="font-size: 0.95rem;">${data.message}</p>
                           <div style="background: #f1f5f9; padding: 10px; border-radius: 8px; font-size: 0.85rem; margin-top: 10px; text-align: left;">
                               <div><b>สเปรดชีต:</b> ${sheetInfo.spreadsheetTitle || '-'}</div>
                               <div><b>ชื่อชีต:</b> ${sheetInfo.sheetName || '-'}</div>
                               <div><b>ยอดบริจาคปัจจุบัน:</b> ${sheetInfo.totalDonations ?? '-'} รายการ</div>
                           </div>`,
                    confirmButtonColor: '#1a5c3a'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'เชื่อมต่อไม่สำเร็จ',
                    text: data.message || 'โปรดตรวจสอบ URL และสิทธิ์การเข้าถึง (Anyone)',
                    confirmButtonColor: '#1a5c3a'
                });
            }
        } catch (err) {
            Swal.fire('เกิดข้อผิดพลาด', err.message, 'error');
        }
    }

    // Copy Apps Script Code to Clipboard
    async function copyAppsScriptCode() {
        try {
            const res = await fetch('google-apps-script/Code.gs');
            const code = await res.text();
            await navigator.clipboard.writeText(code);
            Swal.fire({
                icon: 'success',
                title: 'คัดลอกโค้ดแล้ว',
                text: 'นำไปวางใน Extensions > Apps Script ใน Google Sheet ของท่านได้เลยครับ',
                timer: 2200,
                showConfirmButton: false
            });
        } catch (err) {
            Swal.fire('คัดลอกไม่สำเร็จ', 'กรุณาเปิดไฟล์ google-apps-script/Code.gs แล้วคัดลอกด้วยตนเองครับ', 'info');
        }
    }

    // Convert Google Drive view URLs to direct image CDN URLs
    function formatImageUrl(url) {
        if (!url || typeof url !== 'string') return '';
        url = url.trim();
        if (!url) return '';
        
        // Extract Google Drive ID if present (e.g. drive.google.com/file/d/ID/view or ?id=ID)
        const driveMatch = url.match(/\/file\/d\/([a-zA-Z0-9_-]+)/) || url.match(/[?&]id=([a-zA-Z0-9_-]+)/);
        if (driveMatch && driveMatch[1]) {
            return `https://lh3.googleusercontent.com/d/${driveMatch[1]}`;
        }
        
        // Check if already an http(s) URL or data URL
        if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('data:image/')) {
            return url;
        }
        
        // Check if relative path starting with uploads/
        if (url.startsWith('uploads/')) {
            return url;
        }
        
        return '';
    }

    // Load Recent Wishes (ข้อความความปรารถนาดี)
    async function loadWishes() {
        const container = document.getElementById('wishesContainer');
        try {
            const res = await fetch('api/get_wishes.php');
            const data = await res.json();

            if (data.success && data.wishes && data.wishes.length > 0) {
                container.innerHTML = data.wishes.map(wish => {
                    const initial = (wish.name || 'ผ').charAt(0).toUpperCase();
                    const img1 = formatImageUrl(wish.image1);
                    const img2 = formatImageUrl(wish.image2);
                    let thumbs = '';
                    if (img1) {
                        thumbs += `<img src="${img1}" class="wish-img-thumb" alt="ภาพถ่าย" onerror="this.style.display='none'" onclick="openImageModal(this.src)">`;
                    }
                    if (img2) {
                        thumbs += `<img src="${img2}" class="wish-img-thumb" alt="สลิป" onerror="this.style.display='none'" onclick="openImageModal(this.src)">`;
                    }

                    return `
                        <div class="wish-item-card">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <div class="wish-user">
                                        <div class="wish-avatar">${initial}</div>
                                        <span>${escapeHtml(wish.name)}</span>
                                    </div>
                                    <div class="wish-time">${formatDate(wish.time)}</div>
                                </div>
                                <div class="wish-quote font-kanit">“${escapeHtml(wish.message)}”</div>
                            </div>
                            ${thumbs ? `<div class="wish-attachments">${thumbs}</div>` : ''}
                        </div>
                    `;
                }).join('');
            } else {
                container.innerHTML = `
                    <div style="grid-column: 1 / -1; text-align: center; color: var(--muted); padding: 24px 0; background: #fafdfc; border-radius: 12px; border: 1px dashed #d4ece0;">
                        ยังไม่มีข้อความแสดงความคิดเห็น เป็นคนแรกที่ร่วมส่งกำลังใจได้เลยค่ะ ✨
                    </div>
                `;
            }
        } catch (err) {
            container.innerHTML = `
                <div style="grid-column: 1 / -1; text-align: center; color: var(--muted); padding: 20px 0;">
                    ไม่สามารถโหลดข้อความได้ในขณะนี้
                </div>
            `;
        }
    }

    function escapeHtml(text) {
        if (!text) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return String(text).replace(/[&<>"']/g, m => map[m]);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        try {
            const d = new Date(dateStr);
            if (isNaN(d.getTime())) return dateStr;
            return d.toLocaleDateString('th-TH', { day: 'numeric', month: 'short', year: '2-digit' });
        } catch(e) {
            return dateStr;
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        loadWishes();
    });
</script>

</body>
</html>
