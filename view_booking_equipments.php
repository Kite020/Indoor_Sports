<?php
include 'db_connect.php';

// Fetch all bookings for the dropdown
$bookings = $conn->query("SELECT booking_id, user_id, booking_date FROM Bookings ORDER BY booking_id ASC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Booking Details</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #CBC3E3;
            color: #222;
            text-align: center;
            margin: 0;
            padding: 40px;
        }

        h2,
        h3 {
            color: #000000;
            margin-bottom: 10px;
        }

        form {
            background: #D8BFD8;
            display: inline-block;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
            color: #333;
        }

        select,
        input[type="submit"] {
            padding: 10px 12px;
            margin-left: 10px;
            border-radius: 8px;
            border: 1px solid #915F6D;
            font-size: 14px;
        }

        input[type="submit"] {
            background-color: #915F6D;
            margin-top: 10px;
            color: white;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #1429d3;
        }

        table {
            margin: 20px auto;
            width: 70%;
            border-collapse: separate;
            /* Change from collapse to separate */
            border-spacing: 0;
            /* Remove spacing between cells */
            text-align: center;
            font-weight: bold;
            border-radius: 12px;
            overflow: hidden;
            background-color: #C197DB;
        }

        th,
        td {
            border: 1px solid #000000;
            /* Inner cell borders */
            padding: 12px;
        }

        th {
            background-color: #795F8A;
            color: white;
        }

        /* Optional: smooth corners and visual polish */
        table tr:first-child th:first-child {
            border-top-left-radius: 12px;
        }

        table tr:first-child th:last-child {
            border-top-right-radius: 12px;
        }

        table tr:last-child td:first-child {
            border-bottom-left-radius: 12px;
        }

        table tr:last-child td:last-child {
            border-bottom-right-radius: 12px;
        }

        tr:hover {
            background-color: #e0e7ff;
        }

        p {
            margin-top: 20px;
            color: #555;
            font-style: italic;
        }
    </style>
</head>

<body>
    <h2>View Booking Details</h2>

    <form method="get" action="">
        <label for="booking_id">Select Booking:</label>
        <select name="booking_id" id="booking_id" required>
            <option value="">--Select Booking--</option>
            <?php
            while ($row = $bookings->fetch_assoc()) {
                $selected = (isset($_GET['booking_id']) && $_GET['booking_id'] == $row['booking_id']) ? 'selected' : '';
                echo "<option value='" . $row['booking_id'] . "' $selected>
                    Booking ID: " . $row['booking_id'] . " | User ID: " . $row['user_id'] . " | Date: " . $row['booking_date'] . "
                </option>";
            }
            ?>
        </select>
        <input type="submit" value="View Booking">
    </form>

    <?php
    if (isset($_GET['booking_id']) && !empty($_GET['booking_id'])) {
        $booking_id = intval($_GET['booking_id']);

        // Fetch booking details
        $sql_booking = "
            SELECT b.booking_id, b.user_id, b.booking_date, b.start_time, b.end_time,
                   fu.unit_name AS court_name, f.facility_name
            FROM Bookings b
            JOIN FacilityUnits fu ON b.unit_id = fu.unit_id
            JOIN Facilities f ON fu.facility_id = f.facility_id
            WHERE b.booking_id = ?
        ";

        $stmt = $conn->prepare($sql_booking);
        $stmt->bind_param("i", $booking_id);
        $stmt->execute();
        $booking_result = $stmt->get_result();

        if ($booking_result->num_rows > 0) {
            $booking = $booking_result->fetch_assoc();

            echo "<h3>Booking Details</h3>";
            echo "<table>";
            echo "<tr><th>Booking ID</th><td>" . $booking['booking_id'] . "</td></tr>";
            echo "<tr><th>User ID</th><td>" . $booking['user_id'] . "</td></tr>";
            echo "<tr><th>Booking Date</th><td>" . $booking['booking_date'] . "</td></tr>";
            echo "<tr><th>Start Time</th><td>" . $booking['start_time'] . "</td></tr>";
            echo "<tr><th>End Time</th><td>" . $booking['end_time'] . "</td></tr>";
            echo "<tr><th>Court/Unit</th><td>" . $booking['court_name'] . "</td></tr>";
            echo "<tr><th>Facility</th><td>" . $booking['facility_name'] . "</td></tr>";
            echo "</table>";
        } else {
            echo "<p>No details found for this booking.</p>";
        }

        // Fetch booked equipments
        $sql_equipments = "
            SELECT fu.unit_id, fu.unit_name, be.quantity
            FROM BookedEquipments be
            JOIN FacilityUnits fu ON be.unit_id = fu.unit_id
            WHERE be.booking_id = ?
        ";

        $stmt2 = $conn->prepare($sql_equipments);
        $stmt2->bind_param("i", $booking_id);
        $stmt2->execute();
        $equipments_result = $stmt2->get_result();

        if ($equipments_result->num_rows > 0) {
            echo "<h3>Booked Equipments</h3>";
            echo "<table>";
            echo "<tr><th>Unit ID</th><th>Equipment Name</th><th>Quantity</th></tr>";
            while ($row = $equipments_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row['unit_id'] . "</td>";
                echo "<td>" . $row['unit_name'] . "</td>";
                echo "<td>" . $row['quantity'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No equipments booked for this booking.</p>";
        }
    }

    $conn->close();
    ?>
</body>

</html>