<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read all POSTed fields safely
    $booking_id = intval($_POST['booking_id']);
    $user_id = intval($_POST['user_id']);
    $unit_id = intval($_POST['unit_id']);
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    // Equipments (can be empty)
    $equipments = isset($_POST['equipments']) ? $_POST['equipments'] : [];
    $quantities = isset($_POST['quantity']) ? $_POST['quantity'] : [];

    // Participants (can be empty)
    $participants = isset($_POST['participants']) ? $_POST['participants'] : [];

    try {
        // Start transaction to ensure atomic update
        $conn->begin_transaction();

        // --- Update main Bookings table ---
        $update_booking = $conn->prepare("
            UPDATE Bookings 
            SET user_id = ?, unit_id = ?, booking_date = ?, start_time = ?, end_time = ?
            WHERE booking_id = ?
        ");
        $update_booking->bind_param("iisssi", $user_id, $unit_id, $booking_date, $start_time, $end_time, $booking_id);
        $update_booking->execute();

        // --- Update BookedEquipments ---
        // First clear existing equipments
        $conn->query("DELETE FROM BookedEquipments WHERE booking_id = $booking_id");

        // Insert selected equipments again
        if (!empty($equipments)) {
            $insert_eq = $conn->prepare("
                INSERT INTO BookedEquipments (booking_id, unit_id, quantity) 
                VALUES (?, ?, ?)
            ");
            foreach ($equipments as $eq_unit_id) {
                $qty = isset($quantities[$eq_unit_id]) ? intval($quantities[$eq_unit_id]) : 1;
                $insert_eq->bind_param("iii", $booking_id, $eq_unit_id, $qty);
                $insert_eq->execute();
            }
            $insert_eq->close();
        }

        // --- Update BookingParticipants ---
        // Clear existing participants
        $conn->query("DELETE FROM BookingParticipants WHERE booking_id = $booking_id");

        // Insert newly selected participants
        if (!empty($participants)) {
            $insert_part = $conn->prepare("
                INSERT INTO BookingParticipants (booking_id, participant_id) 
                VALUES (?, ?)
            ");
            foreach ($participants as $pid) {
                $pid = intval($pid);
                $insert_part->bind_param("ii", $booking_id, $pid);
                $insert_part->execute();
            }
            $insert_part->close();
        }

        // Commit all updates
        $conn->commit();

        echo "<script>
                alert('✅ Booking updated successfully!');
                window.location.href='edit_booking.php';
              </script>";

    } catch (Exception $e) {
        // Rollback changes if any failure occurs
        $conn->rollback();
        echo "<script>
                alert('❌ Update failed: " . addslashes($e->getMessage()) . "');
                window.history.back();
              </script>";
    }
} else {
    echo "<script>alert('Invalid Request'); window.location.href='edit_booking.php';</script>";
}
?>