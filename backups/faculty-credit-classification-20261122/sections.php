<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';
require_once '../includes/helpers.php';

require_role(['Admin', 'StatStaff']);

$adminID = (int) $_SESSION['user_id'];

$securityLevel = one(
        $pdo,
        'SELECT security_Level FROM Admin WHERE user_ID = ?',
        [$adminID]
)['security_Level'] ?? 'Read-Only';

$isFullAccess = $securityLevel === 'Full-Access';

$error = '';

// Reference data for the forms
require_once __DIR__ . '/sections_data.php';

// Dispatch form submissions to the matching action file
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    csrf_verify();

    require_write_access();

    if (!$isFullAccess) {
        redirect_with_flash(
                url('admin/sections.php'),
                'error',
                'You have read-only access and cannot change sections.'
        );
    }

    $action = post_str('action', 20);

    if ($action === 'create') {
        require_once __DIR__ . '/actions/sections_create.php';
    } elseif ($action === 'cancel') {
        require_once __DIR__ . '/actions/sections_cancel.php';
    } elseif ($action === 'update') {
        require_once __DIR__ . '/actions/sections_update.php';
    }
}

// Filters and listing data
require_once __DIR__ . '/sections_query.php';

page_start(
        'Course Sections',
        'Admin',
        'sections.php'
);
?>

    <style>
        /* Ashford — Soft Bubble UI, Sections page */

        .form-grid input:not([type="hidden"]):not([type="submit"]),
        .form-grid select,
        .form-grid textarea {
            box-sizing: border-box !important;
            width: 100% !important;
            min-height: 52px !important;
            height: 52px !important;
            padding: 12px 20px !important;
            border: 1px solid #e0e7f0 !important;
            border-radius: 26px !important;
            background: #f8faff !important;
            color: #172b4d !important;
            font-size: 15px !important;
            box-shadow: 0 3px 9px rgba(26, 49, 89, .055),
            inset 0 1px 2px rgba(255,255,255,.9) !important;
            transition: border-color .2s ease,
            background-color .2s ease,
            box-shadow .2s ease,
            transform .2s ease !important;
        }

        .form-grid textarea {
            min-height: 110px !important;
            height: auto !important;
            border-radius: 20px !important;
        }

        .form-grid input:focus,
        .form-grid select:focus,
        .form-grid textarea:focus {
            outline: none !important;
            border-color: #8ba7d0 !important;
            background: #fff !important;
            box-shadow: 0 0 0 4px rgba(73,112,176,.12),
            0 6px 18px rgba(30,54,90,.08) !important;
        }

        .form-grid label {
            font-weight: 650 !important;
            color: #203455 !important;
        }

        .form-grid button,
        .form-grid input[type="submit"],
        .form-grid .btn {
            width: auto !important;
            min-height: 50px !important;
            padding: 12px 28px !important;
            border-radius: 999px !important;
            border: 1px solid transparent !important;
            background: #20365b !important;
            color: white !important;
            font-weight: 650 !important;
            box-shadow: 0 5px 14px rgba(32,54,91,.18) !important;
            cursor: pointer;
            transition: transform .2s ease,
            box-shadow .2s ease !important;
        }

        .form-grid button:not(:disabled):hover,
        .form-grid input[type="submit"]:not(:disabled):hover,
        .form-grid .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 9px 22px rgba(32,54,91,.24) !important;
        }

        .form-grid :is(button, select, input, textarea):focus-visible {
            outline: 2px solid #648bc2 !important;
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            .form-grid * {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>

<?php


?>

    <style>
        /* Modern, compact action buttons — Admin Sections */

        .sections-table td:last-child {
            white-space: nowrap;
        }

        .sections-table td:last-child a,
        .sections-table td:last-child button,
        .sections-table td:last-child input[type="submit"] {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            min-height: 38px !important;
            padding: 9px 17px !important;
            border-radius: 999px !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            line-height: 1.2 !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease,
            background-color .2s ease !important;
        }

        .sections-table td:last-child a:hover,
        .sections-table td:last-child button:hover,
        .sections-table td:last-child input[type="submit"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 14px rgba(0,0,0,.12);
        }

        .sections-table td:last-child a:focus-visible,
        .sections-table td:last-child button:focus-visible {
            outline: 2px solid #648bc2;
            outline-offset: 3px;
        }

        @media (prefers-reduced-motion: reduce) {
            .sections-table td:last-child a,
            .sections-table td:last-child button {
                transition: none !important;
                transform: none !important;
            }
        }
    </style>

<?php
flash();

require_once __DIR__ . '/partials/sections_view.php';

page_end();
