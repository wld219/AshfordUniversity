<?php
declare(strict_types=1);
// READ-ONLY report: compares room capacity with section seats and enrollment.
// Run from the Ashford-New project root: php ~/Downloads/ashford_capacity_review.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit('CLI only'); }
require_once getcwd() . '/config/database.php';
if (!isset($pdo) || !$pdo instanceof PDO) { exit("Could not load PDO from config/database.php\n"); }
$sql = "SELECT s.semester_Name, cs.CRN, cs.course_ID, cs.section_No,
    COALESCE(b.Bldg_Name, 'Unknown') AS building,
    r.room_Number, r.room_Type,
    COALESCE(l.avail_Seats, lab.no_Of_Workstations, 0) AS room_capacity,
    cs.available_Seats AS section_available,
    (SELECT COUNT(*) FROM Enrollment e WHERE e.CRN=cs.CRN) AS enrolled
  FROM Course_Section cs
  JOIN Semester s ON s.semester_ID=cs.semester_ID
  LEFT JOIN Room r ON r.room_ID=COALESCE(cs.lecture_ID,cs.lab_ID)
  LEFT JOIN Building b ON b.Bldg_ID=r.Bldg_ID
  LEFT JOIN Lecture l ON l.lecture_ID=cs.lecture_ID
  LEFT JOIN Lab lab ON lab.lab_ID=cs.lab_ID
  WHERE cs.is_Cancelled=0 AND cs.semester_ID IN (1,2)
  ORDER BY s.semester_Name, cs.CRN";
$rows=$pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
echo "READ-ONLY CAPACITY REVIEW — Fall 2026 / Spring 2027\n";
echo "Room capacity comes from Lecture.avail_Seats or Lab.no_Of_Workstations.\n";
echo "Section available seats come from Course_Section.available_Seats.\n\n";
$flagged=0;
foreach ($rows as $r) {
  $capacity=(int)$r['room_capacity']; $available=(int)$r['section_available']; $enrolled=(int)$r['enrolled'];
  if ($capacity!==30 && !($capacity===0 || $available+$enrolled>$capacity)) continue;
  ++$flagged;
  printf("%s | CRN %s | %s | %s Room %s (%s) | Room cap %d | Available %d | Enrolled %d%s\n",
    $r['semester_Name'],$r['CRN'],$r['course_ID'],$r['building'],$r['room_Number'],$r['room_Type'],
    $capacity,$available,$enrolled,$available+$enrolled>$capacity?' | CHECK: seats exceed room capacity':'');
}
echo "\nDisplayed {$flagged} sections (capacity 30, missing capacity, or over capacity). No changes made.\n";
