<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Indoor Sports Complex Booking System</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            text-align: center;
            /* Background image */
            background: url('background.jpg') no-repeat center center fixed;
            background-size: cover;
            position: relative;

        }

        h1 {
            margin-top: 50px;
            font-size: 36px;
            color: rgb(214, 234, 240);
        }

        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin: 50px auto;
            max-width: 900px;
        }

        .box {
            background-color: #4CAF50;
            color: white;
            width: 250px;
            height: 80px;
            margin: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .box:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 12px rgba(0, 0, 0, 0.2);
            background-color: #45a049;
        }

        /* Different colors for each box */
        .box:nth-child(1) {
            background-color: #FF6F61;
        }

        .box:nth-child(2) {
            background-color: #6A5ACD;
        }

        .box:nth-child(3) {
            background-color: #20B2AA;
        }

        .box:nth-child(4) {
            background-color: #FF8C00;
        }

        .box:nth-child(5) {
            background-color: #DC143C;
        }

        .box:nth-child(6) {
            background-color: #008080;
        }

        .box:nth-child(7) {
            background-color: #FF1493;
        }
    </style>
</head>

<body>

    <h1>Welcome to the Indoor Sports Complex Booking and Management System</h1>

    <div class="container">
        <a href="view_facilities.php" class="box">View Facilities</a>
        <a href="add_booking.php" class="box">Add a Booking</a>
        <a href="check_availability.php" class="box">Check Facility Availability</a>
        <a href="view_participants.php" class="box">View Booking Participants</a>
        <a href="view_booking_equipments.php" class="box">View Booking Equipments</a>
        <a href="check_bookings.php" class="box">Check User Bookings</a>
        <a href="edit_booking.php" class="box">Edit Bookings</a>
        <a href="cancel_booking.php" class="box">Cancel Booking</a>
    </div>

</body>

</html>