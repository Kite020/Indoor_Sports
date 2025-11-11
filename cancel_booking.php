<?php
include 'db_connect.php';

$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    $user_id = $_POST['user_id'] ?? '';

    if (empty($booking_id) || empty($user_id)) {
        $error_message = "❌ Please select both booking and user.";
    } else {
        // Verify that the selected user is the creator of the booking
        $check_stmt = $conn->prepare("
            SELECT b.user_id, u.name AS booked_by
            FROM Bookings b
            JOIN Users u ON b.user_id = u.user_id
            WHERE b.booking_id = ?
        ");
        $check_stmt->bind_param("i", $booking_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $booking = $result->fetch_assoc();
        $check_stmt->close();

        if (!$booking) {
            $error_message = "❌ Invalid booking selected.";
        } elseif ($booking['user_id'] != $user_id) {
            $error_message = "⚠️ This user cannot access or cancel this booking. Only " . htmlspecialchars($booking['booked_by']) . " can cancel it.";
        } else {
            // Proceed to cancel booking
            $delete_stmt = $conn->prepare("DELETE FROM Bookings WHERE booking_id = ?");
            $delete_stmt->bind_param("i", $booking_id);
            if ($delete_stmt->execute()) {
                $success_message = "✅ Booking ID $booking_id has been successfully cancelled by " . htmlspecialchars($booking['booked_by']) . ".";
            } else {
                $error_message = "❌ Failed to cancel booking. Please try again.";
            }
            $delete_stmt->close();
        }
    }
}

// Fetch all bookings (ascending order)
$bookings = $conn->query("
    SELECT 
        b.booking_id, 
        f.facility_name, 
        fu.unit_name, 
        b.booking_date, 
        b.start_time, 
        b.end_time, 
        u.name AS user_name
    FROM Bookings b
    JOIN FacilityUnits fu ON b.unit_id = fu.unit_id
    JOIN Facilities f ON fu.facility_id = f.facility_id
    JOIN Users u ON b.user_id = u.user_id
    ORDER BY b.booking_id ASC
");

// Fetch all users (ascending order)
$users = $conn->query("SELECT user_id, name FROM Users ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Cancel Booking</title>
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
            max-width: 700px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            text-align: left;
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 25px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 16px;
        }

        button {
            background-color: #915F6D;
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #333;
            transform: translateY(-3px);
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.2);
        }

        .success {
            color: green;
            font-weight: bold;
            margin-top: 20px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-top: 20px;
        }

        option {
            padding: 8px;
        }
    </style>

    <script>
        function confirmCancel() {
            const bookingSelect = document.getElementById("booking_id");
            const userSelect = document.getElementById("user_id");

            const booking = bookingSelect.value;
            const user = userSelect.value;

            if (!booking || !user) {
                alert("⚠️ Please select both booking and user before proceeding.");
                return false;
            }

            return confirm("Are you sure you want to cancel this booking?");
        }
    </script>
</head>

<body>
    <h2>Cancel a Booking</h2>

    <form method="POST" onsubmit="return confirmCancel()">
        <label for="booking_id">Select Booking:</label>
        <select name="booking_id" id="booking_id" required>
            <option value="">-- Select Booking --</option>
            <?php while ($row = $bookings->fetch_assoc()) { ?>
                <option value="<?= $row['booking_id'] ?>">
                    [ID: <?= $row['booking_id'] ?>] <?= htmlspecialchars($row['facility_name']) ?> -
                    <?= htmlspecialchars($row['unit_name']) ?> (<?= $row['booking_date'] ?>
                    <?= $row['start_time'] ?>–<?= $row['end_time'] ?>) by <?= htmlspecialchars($row['user_name']) ?>
                </option>
            <?php } ?>
        </select>

        <label for="user_id">Select User:</label>
        <select name="user_id" id="user_id" required>
            <option value="">-- Select User --</option>
            <?php while ($u = $users->fetch_assoc()) { ?>
                <option value="<?= $u['user_id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
            <?php } ?>
        </select>

        <button type="submit">Go</button>
    </form>

    <?php
    if (!empty($error_message)) {
        echo "<p class='error'>$error_message</p>";
    } elseif (!empty($success_message)) {
        echo "<p class='success'>$success_message</p>";
    }
    ?>

</body>

</html>

<?php
$conn->close();
?>