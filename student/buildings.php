<?php

declare(strict_types=1);

require_once '../config/database.php';
require_once '../includes/layout.php';
require_once '../includes/functions.php';

require_role(['Student']);


// Get all university buildings
$sql = "SELECT *
        FROM Building
        ORDER BY Bldg_Name";

$buildings = all_rows(
    $pdo,
    $sql
);


// Get all rooms and their building names
$sql = "SELECT
            Room.*,
            Building.Bldg_Name
        FROM Room
        JOIN Building
            ON Building.Bldg_ID = Room.Bldg_ID
        ORDER BY
            Building.Bldg_Name,
            Room.room_Number";

$rooms = all_rows(
    $pdo,
    $sql
);

// Room capacity details by room type
$roomTypes = [
    'Lecture' => 'Lecture Hall',
    'Office' => 'Faculty Office',
    'Lab' => 'Computer/Teaching Lab',
];

page_start(
    'Buildings & Rooms',
    'Student',
    'buildings.php'
);

?>

<div class="grid2">

    <div class="card">

        <h2>Buildings</h2>

        <?php foreach ($buildings as $building): ?>

            <p>

                <strong>
                    <?= e($building['Bldg_Name']) ?>
                </strong>

                (<?= e($building['Bldg_ID']) ?>)

                <br>

                <span class="muted">
                    <?= e($building['building_Usage']) ?>
                </span>

            </p>

        <?php endforeach; ?>

    </div>


    <div class="card">

        <h2>Rooms</h2>

        <?php foreach ($rooms as $room): $roomTypeLabel = $roomTypes[$room['room_Type']] ?? $room['room_Type']; ?>

            <p>

                <?= e($room['Bldg_Name']) ?>

                -

                <strong>
                    <?= e($room['room_Number']) ?>
                </strong>

                <span class="badge">
                    <?= e($roomTypeLabel) ?>
                </span>

                <?php if (!empty($room['available_Seats'])): ?>
                    <span class="badge" style="background:#eef2f6;color:#1e3366">
                        Seats: <?= e($room['available_Seats']) ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($room['No_Of_Desks'])): ?>
                    <span class="badge" style="background:#f0f7f0;color:#245b2b">
                        Desks: <?= e($room['No_Of_Desks']) ?>
                    </span>
                <?php endif; ?>

                <?php if (!empty($room['no_Of_Workstations'])): ?>
                    <span class="badge" style="background:#fdf6e3;color:#8a6d00">
                        Workstations: <?= e($room['no_Of_Workstations']) ?>
                    </span>
                <?php endif; ?>

            </p>

        <?php endforeach; ?>

    </div>

</div>


<?php

page_end();

?>