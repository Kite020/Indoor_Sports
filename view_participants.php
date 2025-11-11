<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>View Participants</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #CBC3E3;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 60px auto;
            background-color: #D8BFD8;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        h2 {
            text-align: center;
            color: #000000;
            margin-bottom: 25px;
        }

        form {
            text-align: center;
            margin-bottom: 30px;
        }

        select,
        input[type="submit"] {
            padding: 10px;
            border-radius: 8px;
            border: 2px solid #795F8A;
            font-size: 15px;
            margin: 10px;
        }

        input[type="submit"] {
            background-color: #795F8A;
            color: white;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #C197DB;
        }

        table {
            width: 100%;
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

        p {
            text-align: center;
            font-size: 16px;
            color: #555;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>View Participants for a Booking</h2>

        <form method="POST">
            <label for="booking_id"><strong>Select Booking:</strong></label>
            <select name="booking_id" required>
                <option value="">--Select Booking--</option>
                <?php
                // Fetch bookings (sorted by booking_id ASC)
                $bookings = $conn->query("
                    SELECT b.booking_id, fu.unit_name, f.facility_name, b.booking_date, u.name as user_name
                    FROM Bookings b
                    JOIN FacilityUnits fu ON b.unit_id = fu.unit_id
                    JOIN Facilities f ON fu.facility_id = f.facility_id
                    JOIN Users u ON b.user_id = u.user_id
                    ORDER BY b.booking_id ASC
                ");

                while ($row = $bookings->fetch_assoc()) {
                    echo "<option value='" . $row['booking_id'] . "'>ID " . $row['booking_id'] . " | " . htmlspecialchars($row['user_name']) . " | " . htmlspecialchars($row['facility_name']) . " | " . htmlspecialchars($row['unit_name']) . "</option>";
                }
                ?>
            </select>
            <input type="submit" value="View">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $booking_id = intval($_POST['booking_id']);

            $query = "SELECT bp.participant_id, u.name, u.role
                      FROM BookingParticipants bp
                      JOIN Users u ON bp.participant_id = u.user_id
                      WHERE bp.booking_id = '$booking_id'";

            $result = mysqli_query($conn, $query);

            if (mysqli_num_rows($result) > 0) {
                echo "<table>
                        <tr><th>ID</th><th>Name</th><th>Role</th></tr>";
                while ($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>
                            <td>{$row['participant_id']}</td>
                            <td>" . htmlspecialchars($row['name']) . "</td>
                            <td>" . htmlspecialchars($row['role']) . "</td>
                          </tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No participants found for this booking.</p>";
            }
        }

        mysqli_close($conn);
        ?>
    </div>
</body>

</html>