<?php
include 'db_connect.php';

$facility_id = $_POST['facility_id'];
$booking_date = $_POST['booking_date'] ?? '';
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

$query = $conn->prepare("SELECT unit_id, unit_name FROM FacilityUnits WHERE facility_id = ?");
$query->bind_param("i", $facility_id);
$query->execute();
$result = $query->get_result();

while ($row = $result->fetch_assoc()) {
    $unit_id = $row['unit_id'];
    $available = true;

    if ($booking_date && $start_time && $end_time) {
        $check = $conn->prepare("
            SELECT COUNT(*) FROM BookedEquipments be
            JOIN Bookings b ON be.booking_id = b.booking_id
            WHERE be.unit_id = ? AND b.booking_date = ?
            AND (
                (b.start_time < ? AND b.end_time > ?) OR
                (b.start_time < ? AND b.end_time > ?) OR
                (b.start_time >= ? AND b.end_time <= ?)
            )
        ");
        $check->bind_param("isssssss", $unit_id, $booking_date, $end_time, $start_time, $start_time, $end_time, $start_time, $end_time);
        $check->execute();
        $check->bind_result($conflict);
        $check->fetch();
        $check->close();

        if ($conflict > 0)
            $available = false;
    }

    if ($available) {
        echo "<input type='checkbox' name='equipments[]' value='{$unit_id}'> {$row['unit_name']}<br>";
    } else {
        echo "<label class='disabled-label'><input type='checkbox' disabled> {$row['unit_name']} (Not Available)</label><br>";
    }
}
?>