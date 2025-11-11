<?php
include 'db_connect.php';
?>

<!DOCTYPE html>
<html>

<head>
    <title>User Bookings</title>
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
            margin-bottom: 15px;
        }

        form {
            background: #D8BFD8;
            display: inline-block;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            color: #333;
            margin-right: 10px;
        }

        select {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 14px;
            width: 180px;
        }

        button {
            padding: 10px 15px;
            border-radius: 8px;
            background-color: #915F6D;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #1429d3;
        }

        table {
            margin: 25px auto;
            width: 70%;
            border-collapse: separate;
            border-spacing: 0;
            border: 5px solid black;
            border-radius: 12px;
            overflow: hidden;
            background-color: #C292A1;
            box-shadow: 0px 6px 15px rgba(0, 0, 0, 0.1);
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #000000;
            padding: 12px 15px;
            text-align: center;
        }

        th {
            background-color: #915F6D;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #C292A1;
        }

        tr:hover {
            background-color: #e0e7ff;
        }

        p {
            color: #555;
            margin-top: 20px;
            font-style: italic;
        }
    </style>
</head>

<body>
    <h2>View All Bookings by a Specific User</h2>

    <form method="GET">
        <label>Select User:</label>
        <select name="user_id" required>
            <option value="">-- Select User --</option>
            <?php
            $user_query = mysqli_query($conn, "SELECT user_id, name FROM Users ORDER BY name ASC");
            while ($user = mysqli_fetch_assoc($user_query)) {
                $selected = (isset($_GET['user_id']) && $_GET['user_id'] == $user['user_id']) ? 'selected' : '';
                echo "<option value='{$user['user_id']}' $selected>{$user['name']}</option>";
            }
            ?>
        </select>
        <button type="submit">View Bookings</button>
    </form>

    <?php
    if (isset($_GET['user_id']) && !empty($_GET['user_id'])) {
        $user_id = $_GET['user_id'];

        $sql = "SELECT b.booking_id, b.booking_date, b.start_time, b.end_time, 
                       f.facility_name, u.unit_name
                FROM Bookings b
                JOIN FacilityUnits u ON b.unit_id = u.unit_id
                JOIN Facilities f ON u.facility_id = f.facility_id
                WHERE b.user_id = '$user_id'
                ORDER BY b.booking_date DESC, b.start_time ASC";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
            echo "<h3>Bookings by User:</h3>";
            echo "<table>
                <tr>
                    <th>Booking ID</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Facility</th>
                    <th>Unit</th>
                </tr>";
            while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>
                    <td>{$row['booking_id']}</td>
                    <td>{$row['booking_date']}</td>
                    <td>{$row['start_time']}</td>
                    <td>{$row['end_time']}</td>
                    <td>{$row['facility_name']}</td>
                    <td>{$row['unit_name']}</td>
                </tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No bookings found for this user.</p>";
        }
    }
    ?>
</body>

</html>