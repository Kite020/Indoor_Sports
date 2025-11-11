<?php include 'db_connect.php'; ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>All Facilities</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      /* Light blue background */
      margin: 0;
      padding: 0;
      text-align: center;
      background: url('facilities.jpg') no-repeat center center fixed;
      background-size: cover;
      position: relative;
    }

    h2 {
      margin-top: 50px;
      font-size: 32px;
      color: #000000;
    }

    table {
      margin: 30px auto;
      border-collapse: collapse;
      width: 80%;
      max-width: 800px;
      background-color: #B6D0E2;
      /* White table background */
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      border-radius: 15px;
      /* Rounded corners */
      overflow: hidden;
      border: 5px solid #4CAF50;
    }

    th,
    td {
      padding: 25px 20px;
      border: 1px solid #ddd;
      text-align: center;
      font-weight: bold;
    }

    th {
      background-color: #6495ED;
      color: white;
      font-size: 18px;
    }

    tr:nth-child(even) {
      background-color: #A7C7E7;
      /* Alternating row color */
    }
  </style>
</head>

<body>

  <h2>All Facilities</h2>

  <?php
  $result = mysqli_query($conn, "SELECT * FROM Facilities");

  if (mysqli_num_rows($result) > 0) {
    echo "<table>
    <tr><th>Facility ID</th><th>Name</th><th>Description</th></tr>";

    while ($row = mysqli_fetch_assoc($result)) {
      echo "<tr>
                <td>{$row['facility_id']}</td>
                <td>{$row['facility_name']}</td>
                <td>{$row['description']}</td>
              </tr>";
    }

    echo "</table>";
  } else {
    echo "<p>No facilities found.</p>";
  }

  mysqli_close($conn);
  ?>

</body>

</html>