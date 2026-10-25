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

        <?php foreach ($rooms as $room): ?>

            <p>

                <?= e($room['Bldg_Name']) ?>

                -

                <strong>
                    <?= e($room['room_Number']) ?>
                </strong>

                <span class="badge">
                    <?= e($room['room_Type']) ?>
                </span>

            </p>

        <?php endforeach; ?>

    </div>

</div>


<?php

page_end();

?>