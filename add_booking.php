<?php
include 'db_connect.php';

$error_message = "";
$success_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id']);
    $unit_id = intval($_POST['unit_id']);
    $facility_id = intval($_POST['facility_id']);
    $booking_date = $_POST['booking_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];

    $participants = isset($_POST['participants']) ? $_POST['participants'] : [];
    $equipments = isset($_POST['equipments']) ? $_POST['equipments'] : [];

    // Get facility name
    $facility_query = $conn->prepare("SELECT facility_name FROM Facilities WHERE facility_id = ?");
    $facility_query->bind_param("i", $facility_id);
    $facility_query->execute();
    $facility_query->bind_result($facility_name);
    $facility_query->fetch();
    $facility_query->close();

    // Facility min participant rules
    $facility_rules = [
        'Badminton' => 4,
        'Carrom' => 4,
        'Chess' => 2,
        'Table Tennis' => 2,
        'Squash' => 2,
        'Gym' => 1
    ];

    $min_required = isset($facility_rules[$facility_name]) ? $facility_rules[$facility_name] : 1;
    $participant_count = count($participants);

    if ($participant_count < $min_required) {
        $error_message = "❌ Minimum requirement of $min_required participant(s) for $facility_name is not fulfilled.";
    } else {
        // Check if unit booked
        $check_unit = $conn->prepare("
            SELECT COUNT(*) FROM Bookings
            WHERE unit_id = ? AND booking_date = ?
            AND (
                (start_time < ? AND end_time > ?) OR
                (start_time < ? AND end_time > ?) OR
                (start_time >= ? AND end_time <= ?)
            )
        ");
        $check_unit->bind_param("isssssss", $unit_id, $booking_date, $end_time, $start_time, $start_time, $end_time, $start_time, $end_time);
        $check_unit->execute();
        $check_unit->bind_result($unit_conflict);
        $check_unit->fetch();
        $check_unit->close();

        if ($unit_conflict > 0) {
            $error_message = "❌ This facility unit is already booked for the selected time.";
        } else {
            // Check equipment availability
            $equipment_conflicts = [];
            foreach ($equipments as $equip_id) {
                $equip_id = intval($equip_id);
                $check_equip = $conn->prepare("
                    SELECT COUNT(*) FROM BookedEquipments be
                    JOIN Bookings b ON be.booking_id = b.booking_id
                    WHERE be.unit_id = ? AND b.booking_date = ?
                    AND (
                        (b.start_time < ? AND b.end_time > ?) OR
                        (b.start_time < ? AND b.end_time > ?) OR
                        (b.start_time >= ? AND b.end_time <= ?)
                    )
                ");
                $check_equip->bind_param("isssssss", $equip_id, $booking_date, $end_time, $start_time, $start_time, $end_time, $start_time, $end_time);
                $check_equip->execute();
                $check_equip->bind_result($equip_conflict);
                $check_equip->fetch();
                $check_equip->close();

                if ($equip_conflict > 0) {
                    $ename_query = $conn->prepare("SELECT unit_name FROM FacilityUnits WHERE unit_id = ?");
                    $ename_query->bind_param("i", $equip_id);
                    $ename_query->execute();
                    $ename_query->bind_result($equip_name);
                    $ename_query->fetch();
                    $ename_query->close();

                    $equipment_conflicts[] = $equip_name;
                }
            }

            if (!empty($equipment_conflicts)) {
                $error_message = "❌ The following equipment(s) are not available for the selected time: <b>" . implode(", ", $equipment_conflicts) . "</b>";
            } else {
                // Insert booking
                $stmt = $conn->prepare("INSERT INTO Bookings (user_id, unit_id, booking_date, start_time, end_time) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("iisss", $user_id, $unit_id, $booking_date, $start_time, $end_time);
                $stmt->execute();
                $booking_id = $stmt->insert_id;
                $stmt->close();

                // Insert equipments
                // Insert equipments
                foreach ($equipments as $equip_id) {
                    $equip_id = intval($equip_id);
                    $quantity = 1; // default quantity
                    $stmt2 = $conn->prepare("INSERT INTO BookedEquipments (booking_id, unit_id, quantity) VALUES (?, ?, ?)");
                    $stmt2->bind_param("iii", $booking_id, $equip_id, $quantity);
                    $stmt2->execute();
                    $stmt2->close();
                }


                // Insert participants
                foreach ($participants as $pid) {
                    $pid = intval($pid);
                    $stmt3 = $conn->prepare("INSERT INTO BookingParticipants (booking_id, participant_id) VALUES (?, ?)");
                    $stmt3->bind_param("ii", $booking_id, $pid);
                    $stmt3->execute();
                    $stmt3->close();
                }

                $success_message = "✅ Booking added successfully! Booking ID: $booking_id";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Add Booking</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial;
            background-color: #CBC3E3;
            text-align: center;
            margin: 0;
            padding: 0;
        }

        h2 {
            margin-top: 40px;
            font-size: 32px;
            color: #000;
        }

        form {
            background-color: #D8BFD8;
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        select,
        input[type="date"],
        input[type="time"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        input[type="submit"] {
            background-color: #915F6D;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #333;
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        .success {
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        input[type="checkbox"] {
            margin-right: 10px;
            transform: scale(1.2);
        }

        .disabled-label {
            color: gray;
        }
    </style>
</head>

<body>

    <h2>Add New Booking</h2>
    <form method="post" action="">
        <label for="user_id">Select User:</label>
        <select name="user_id" id="user_id" required>
            <option value="">--Select User--</option>
            <?php
            $users = $conn->query("SELECT user_id, name FROM Users ORDER BY name ASC");
            while ($row = $users->fetch_assoc()) {
                echo "<option value='{$row['user_id']}'>{$row['name']}</option>";
            }
            ?>
        </select>

        <label for="facility_id">Select Facility:</label>
        <select name="facility_id" id="facility_id" required>
            <option value="">--Select Facility--</option>
            <?php
            $facilities = $conn->query("SELECT facility_id, facility_name FROM Facilities ORDER BY facility_name ASC");
            while ($row = $facilities->fetch_assoc()) {
                echo "<option value='{$row['facility_id']}'>{$row['facility_name']}</option>";
            }
            ?>
        </select>

        <label for="unit_id">Select Court/Unit:</label>
        <select name="unit_id" id="unit_id" required>
            <option value="">--Select Unit--</option>
        </select>

        <label for="booking_date">Booking Date:</label>
        <input type="date" name="booking_date" id="booking_date" required>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" id="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" id="end_time" required>

        <h3>Select Equipments (if any)</h3>
        <div id="equipments_container"></div>

        <h3>Select Participants (if any)</h3>
        <?php
        $participants = $conn->query("SELECT user_id, name FROM Users ORDER BY name ASC");
        while ($row = $participants->fetch_assoc()) {
            echo "<input type='checkbox' name='participants[]' value='{$row['user_id']}'> {$row['name']}<br>";
        }
        ?>

        <input type="submit" value="Add Booking">
    </form>

    <?php
    if (!empty($error_message))
        echo "<p class='error'>$error_message</p>";
    if (!empty($success_message))
        echo "<p class='success'>$success_message</p>";
    ?>

    <script>
        function fetchEquipments() {
            var facility_id = $('#facility_id').val();
            var booking_date = $('#booking_date').val();
            var start_time = $('#start_time').val();
            var end_time = $('#end_time').val();

            if (facility_id && booking_date && start_time && end_time) {
                $.ajax({
                    url: 'fetch_equipments.php',
                    method: 'POST',
                    data: { facility_id, booking_date, start_time, end_time },
                    success: function (data) {
                        $('#equipments_container').html(data);
                    }
                });
            }
        }

        $('#facility_id').change(function () {
            var facility_id = $(this).val();
            if (facility_id != '') {
                $.ajax({
                    url: 'fetch_units.php',
                    method: 'POST',
                    data: { facility_id },
                    success: function (data) {
                        $('#unit_id').html(data);
                    }
                });
                fetchEquipments();
            }
        });

        $('#booking_date, #start_time, #end_time').on('change', fetchEquipments);
    </script>

</body>

</html>