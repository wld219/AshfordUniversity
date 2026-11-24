<?php

declare(strict_types=1);

/** Show an explicit label when the isolated demo app displays Fall 2026. */
function demo_schedule_notice(int $semesterId): void
{
    if ($semesterId !== 1) {
        return;
    }
    ?>
    <aside class="demo-schedule-notice" role="note" aria-label="Proposed Fall 2026 demonstration schedule notice">
        <strong>DEMO DATA — PROPOSED FALL 2026 SCHEDULE</strong>
        <span>This demonstration environment contains proposed Fall 2026 course sections used for software testing. These sections have not been confirmed as academically authorized or supported by verified enrollment.</span>
    </aside>
    <style>
        .demo-schedule-notice {
            display: grid;
            gap: 4px;
            max-width: 1450px;
            margin: 0 auto 16px;
            padding: 12px 16px;
            border: 1px solid #b45309;
            border-left: 5px solid #b45309;
            border-radius: 8px;
            background: #fff7ed;
            color: #422006;
            font-size: .95rem;
            line-height: 1.45;
            overflow-wrap: anywhere;
        }
        .demo-schedule-notice strong { color: #7c2d12; letter-spacing: .02em; }
        @media (max-width: 600px) {
            .demo-schedule-notice { margin-bottom: 12px; padding: 11px 12px; font-size: .9rem; }
        }
    </style>
    <?php
}
