<?php
include 'db_connect.php';

// Handle form submission for updating booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['booking_id'], $_POST['check_user'])) {
    $booking_id = intval($_POST['booking_id']);
    $selected_user = intval($_POST['check_user']);

    // Verify booking belongs to selected user
    $check = $conn->query("SELECT booking_id FROM Bookings WHERE booking_id=$booking_id AND user_id=$selected_user");
    if ($check->num_rows == 0) {
        echo "<script>alert('Cannot edit due to restriction: user mismatch');</script>";
    } else {
        // Booking can be edited, show the edit form
        $booking = $conn->query("
            SELECT b.*, fu.unit_name, f.facility_id
            FROM Bookings b
            JOIN FacilityUnits fu ON b.unit_id = fu.unit_id
            JOIN Facilities f ON fu.facility_id = f.facility_id
            WHERE b.booking_id = $booking_id
        ")->fetch_assoc();

        // Fetch booked equipments
        $equipments = [];
        $eq_res = $conn->query("SELECT unit_id, quantity FROM BookedEquipments WHERE booking_id = $booking_id");
        while ($row = $eq_res->fetch_assoc())
            $equipments[$row['unit_id']] = $row['quantity'];

        // Fetch participants
        $participants = [];
        $p_res = $conn->query("SELECT participant_id FROM BookingParticipants WHERE booking_id = $booking_id");
        while ($row = $p_res->fetch_assoc())
            $participants[] = $row['participant_id'];
    }
}

// Fetch all bookings for dropdown
$bookings = $conn->query("
    SELECT b.booking_id, fu.unit_name, f.facility_name, b.booking_date, u.name as user_name
    FROM Bookings b
    JOIN FacilityUnits fu ON b.unit_id = fu.unit_id
    JOIN Facilities f ON fu.facility_id = f.facility_id
    JOIN Users u ON b.user_id = u.user_id
    ORDER BY b.booking_id ASC
");

// Fetch all users for dropdown
$users = $conn->query("SELECT user_id, name FROM Users ORDER BY name ASC");
?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Booking</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #CBC3E3;
            /* light mauve background */
            text-align: center;
            padding: 30px;
        }

        h2,
        h3,
        h4 {
            color: #000000;
            /* mauve shade */
            margin-bottom: 15px;
        }

        form {
            background: #D8BFD8;
            display: inline-block;
            padding: 20px 30px;
            border-radius: 12px;
            box-shadow: 0px 4px 12px rgba(107, 76, 122, 0.2);
            margin-bottom: 30px;
        }

        label {
            font-weight: bold;
            color: #5a3f66;
            margin-right: 10px;
        }

        select,
        input[type="date"],
        input[type="time"],
        input[type="number"] {
            padding: 8px 10px;
            border-radius: 8px;
            border: 1px solid #c9a0d9;
            font-size: 14px;
            margin-bottom: 10px;
        }

        input[type="submit"],
        button {
            padding: 10px 20px;
            border-radius: 8px;
            background-color: #6b4c7a;
            color: white;
            border: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: #5a3f66;
        }

        table {
            margin: 20px auto;
            width: 80%;
            border-collapse: separate;
            border-spacing: 0;
            border: 3px solid #6b4c7a;
            border-radius: 12px;
            overflow: hidden;
            background-color: #fdf6ff;
            font-weight: bold;
        }

        th,
        td {
            border: 1px solid #a47fbf;
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #9b6db0;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f0e6f5;
        }

        tr:hover {
            background-color: #e0d0eb;
        }

        h4 {
            margin-top: 20px;
        }

        input[type="checkbox"] {
            margin-right: 8px;
        }
    </style>
</head>

<body>
    <h2>Edit Booking</h2>

    <form method="post" id="select_booking_form">
        <label for="booking_id">Select Booking ID:</label>
        <select name="booking_id" required>
            <option value="">--Select Booking--</option>
            <?php while ($row = $bookings->fetch_assoc()): ?>
                <option value="<?= $row['booking_id'] ?>">ID <?= $row['booking_id'] ?> | <?= $row['user_name'] ?> |
                    <?= $row['facility_name'] ?> | <?= $row['unit_name'] ?> | <?= $row['booking_date'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <label for="check_user">Select User:</label>
        <select name="check_user" required>
            <option value="">--Select User--</option>
            <?php while ($row = $users->fetch_assoc()): ?>
                <option value="<?= $row['user_id'] ?>"><?= $row['name'] ?></option>
            <?php endwhile; ?>
        </select>
        <br><br>

        <input type="submit" value="Go">
    </form>

    <?php if (isset($booking) && $booking): ?>
        <hr>
        <h3>Edit Booking ID: <?= $booking['booking_id'] ?></h3>

        <form method="post" action="update_booking.php">
            <input type="hidden" name="booking_id" value="<?= $booking['booking_id'] ?>">

            <!-- User -->
            <label>User:</label>
            <select name="user_id" required>
                <?php
                $users2 = $conn->query("SELECT user_id, name FROM Users ORDER BY name ASC");
                while ($u = $users2->fetch_assoc()) {
                    $selected = $u['user_id'] == $booking['user_id'] ? 'selected' : '';
                    echo "<option value='" . $u['user_id'] . "' $selected>" . $u['name'] . "</option>";
                }
                ?>
            </select>
            <br><br>

            <!-- Facility -->
            <label>Facility:</label>
            <select name="facility_id" id="facility_id" required>
                <?php
                $facilities = $conn->query("SELECT facility_id, facility_name FROM Facilities ORDER BY facility_name ASC");
                while ($f = $facilities->fetch_assoc()) {
                    $selected = $f['facility_id'] == $booking['facility_id'] ? 'selected' : '';
                    echo "<option value='" . $f['facility_id'] . "' $selected>" . $f['facility_name'] . "</option>";
                }
                ?>
            </select>
            <br><br>

            <!-- ✅ Unit (added this missing dropdown) -->
            <label>Unit:</label>
            <select name="unit_id" id="unit_id" required>
                <option value="">--Select Unit--</option>
            </select>
            <br><br>



            <!-- Date & Time -->
            <label>Booking Date:</label>
            <input type="date" name="booking_date" value="<?= $booking['booking_date'] ?>" required><br><br>
            <label>Start Time:</label>
            <input type="time" name="start_time" value="<?= $booking['start_time'] ?>" required><br><br>
            <label>End Time:</label>
            <input type="time" name="end_time" value="<?= $booking['end_time'] ?>" required><br><br>

            <!-- Equipments -->
            <h4>Equipments:</h4>
            <div id="equipments_container"></div>
            <br>

            <!-- Participants -->
            <h4>Participants:</h4>
            <?php
            $participants_all = $conn->query("SELECT user_id, name FROM Users ORDER BY name ASC");
            while ($p = $participants_all->fetch_assoc()) {
                $checked = in_array($p['user_id'], $participants) ? 'checked' : '';
                echo "<input type='checkbox' name='participants[]' value='" . $p['user_id'] . "' $checked> " . $p['name'] . "<br>";
            }
            ?>
            <br>
            <input type="submit" value="Update Booking">
        </form>

        <script>
            $(document).ready(function () {
                function load_units_and_equipments(facility_id, selected_unit_id = null, booked_equipments = {}) {
                    if (facility_id != '') {
                        $.ajax({
                            url: 'fetch_units.php',
                            method: 'POST',
                            data: { facility_id: facility_id },
                            success: function (data) {
                                $('#unit_id').html(data);
                                if (selected_unit_id) $('#unit_id').val(selected_unit_id);
                            }
                        });

                        $.ajax({
                            url: 'fetch_equipments.php',
                            method: 'POST',
                            data: { facility_id: facility_id },
                            success: function (html) {
                                $('#equipments_container').html(html);

                                // ✅ Ensure equipment checkboxes are marked *after* HTML loads
                                for (const id in booked_equipments) {
                                    const checkbox = $('input[type="checkbox"][name="equipments[]"][value="' + id + '"]');
                                    if (checkbox.length) {
                                        checkbox.prop('checked', true);
                                    }

                                    // Optional: If you also have quantity fields tied to these, fill them too:
                                    const qtyInput = $('input[type="number"][name="quantity[' + id + ']"]');
                                    if (qtyInput.length && booked_equipments[id]) {
                                        qtyInput.val(booked_equipments[id]);
                                    }
                                }
                            }
                        });
                    }
                }

                // Load initial units and equipments
                load_units_and_equipments($('#facility_id').val(), <?= $booking['unit_id'] ?>, <?= json_encode($equipments) ?>);

                // Change facility dynamically
                $('#facility_id').change(function () {
                    load_units_and_equipments($(this).val());
                });
            });
        </script>

    <?php endif; ?>
</body>

</html>