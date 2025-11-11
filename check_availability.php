<?php include 'db_connect.php'; ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Check Facility Availability</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #CBC3E3;
            margin: 0;
            padding: 0;
            text-align: center;
        }

        h2 {
            margin-top: 40px;
            font-size: 32px;
            color: #000000;
        }

        form {
            background-color: #D8BFD8;
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
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
            padding: 14px 25px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }

        input[type="submit"]:hover {
            background-color: #0451a0;
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .message {
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }

        .success {
            color: green;
        }

        .error {
            color: red;
        }

        table {
            margin: 30px auto;
            border-collapse: collapse;
            width: 70%;
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 12px 18px;
            text-align: center;
        }

        th {
            background-color: #915F6D;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f3f3f3;
        }
    </style>
</head>

<body>
    <h2>Check Facility Availability</h2>

    <form method="POST">
        <label for="facility_id">Facility Name:</label>
        <select name="facility_id" id="facility_id" required>
            <option value="">-- Select Facility --</option>
            <?php
            $facilities = mysqli_query($conn, "SELECT facility_id, facility_name FROM Facilities ORDER BY facility_name");
            while ($row = mysqli_fetch_assoc($facilities)) {
                echo "<option value='{$row['facility_id']}'>{$row['facility_name']}</option>";
            }
            ?>
        </select>

        <label for="booking_date">Date:</label>
        <input type="date" name="booking_date" id="booking_date" required>

        <label for="start_time">Start Time:</label>
        <input type="time" name="start_time" id="start_time" required>

        <label for="end_time">End Time:</label>
        <input type="time" name="end_time" id="end_time" required>

        <input type="submit" value="Check Availability">
    </form>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $facility_id = $_POST['facility_id'];
        $date = $_POST['booking_date'];
        $start = $_POST['start_time'];
        $end = $_POST['end_time'];

        // Get all units under the selected facility
        $units_query = "SELECT unit_id, unit_name FROM FacilityUnits WHERE facility_id = '$facility_id'";
        $units_result = mysqli_query($conn, $units_query);

        if (mysqli_num_rows($units_result) == 0) {
            echo "<p class='message error'>❌ No units found under this facility.</p>";
        } else {
            // Get all booked units for the same date/time
            $booked_query = "
                SELECT DISTINCT unit_id FROM (

                    -- Units booked directly
                    SELECT B.unit_id
                    FROM Bookings B
                    WHERE B.booking_date = '$date'
                    AND (B.start_time < '$end' AND B.end_time > '$start')

                    UNION

                    -- Units booked as equipment
                    SELECT BE.unit_id
                    FROM BookedEquipments BE
                    JOIN Bookings B ON BE.booking_id = B.booking_id
                    WHERE B.booking_date = '$date'
                    AND (B.start_time < '$end' AND B.end_time > '$start')

                ) AS all_booked
                ";

            $booked_result = mysqli_query($conn, $booked_query);

            $booked_units = [];
            while ($b = mysqli_fetch_assoc($booked_result)) {
                $booked_units[] = $b['unit_id'];
            }

            $available_units = [];
            while ($u = mysqli_fetch_assoc($units_result)) {
                if (!in_array($u['unit_id'], $booked_units)) {
                    $available_units[] = $u;
                }
            }

            if (empty($available_units)) {
                echo "<p class='message error'>❌ No units are available for this facility at the selected time.</p>";
            } else {
                echo "<p class='message success'>✅ Available Units:</p>";
                echo "<table>
                        <tr><th>Unit ID</th><th>Unit Name</th></tr>";
                foreach ($available_units as $unit) {
                    echo "<tr>
                            <td>{$unit['unit_id']}</td>
                            <td>{$unit['unit_name']}</td>
                          </tr>";
                }
                echo "</table>";
            }
        }
    }

    mysqli_close($conn);
    ?>
</body>

</html>