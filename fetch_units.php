<?php
include 'db_connect.php';

if (isset($_POST['facility_id'])) {
    $facility_id = intval($_POST['facility_id']);
    $units = $conn->query("SELECT unit_id, unit_name FROM FacilityUnits WHERE facility_id = $facility_id ORDER BY unit_name ASC");

    echo '<option value="">--Select Unit--</option>';
    while ($row = $units->fetch_assoc()) {
        echo '<option value="' . $row['unit_id'] . '">' . $row['unit_name'] . '</option>';
    }
}
?>