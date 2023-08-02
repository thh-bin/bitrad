<!DOCTYPE html>
<html>
<head>
  <title>Session Details</title>
  <style>
    /* CSS styles for sessions.php */
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
    }

    h2 {
      color: #007bff;
    }

    table {
      margin-top: 10px;
      width: 100%;
      border-collapse: collapse;
    }

    th, td {
      padding: 10px;
      text-align: left;
      border-bottom: 1px solid #ddd;
    }

    th {
      background-color: #007bff;
      color: #fff;
    }

    tr:hover {
      background-color: #f2f2f2;
    }

    a {
      color: #007bff;
      text-decoration: none;
    }

    a:hover {
      color: #0056b3;
    }
  </style>
</head>
<body>
  <?php
  // Replace these values with your actual database credentials
  $servername = "localhost";
  $radusername = "radupd";
  $password = "radusrpwd";
  $dbname = "radius";

  // Check if username is provided in the URL
  if (isset($_GET['username'])) {
      $username = $_GET['username'];

      try {
          // Connect to the database
          $conn = new PDO("mysql:host=$servername;dbname=$dbname", $radusername, $password);
          // Set the PDO error mode to exception
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          // Query to fetch all sessions for the selected username
          $sql = "SELECT username, acctsessionid, acctstarttime, acctstoptime, acctsessiontime AS session_time, framedipaddress AS ip_address, calledstationid AS called_station_id, callingstationid AS calling_station_id
                  FROM radacct
                  WHERE username = :username AND acctstoptime IS NOT NULL
                  ORDER BY acctstarttime DESC";

          // Prepare the statement
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':username', $username);
          $stmt->execute();

          // Display the sessions for the username
          echo "<h2>Sessions for Username: " . $username . "</h2>";
          echo "<table>";
          echo "<tr><th>Session ID</th><th>Start Time</th><th>Stop Time</th><th>Acct. Session Time</th><th>IP Address</th><th>Called Station ID</th><th>Client MAC</th></tr>";
          foreach ($stmt as $row) {
              echo "<tr>";
              echo "<td>" . $row['acctsessionid'] . "</td>";
              echo "<td>" . $row['acctstarttime'] . "</td>";
              echo "<td>" . $row['acctstoptime'] . "</td>";
              echo "<td>" . $row['session_time'] . " seconds</td>";
              echo "<td>" . $row['ip_address'] . "</td>";
              echo "<td>" . $row['called_station_id'] . "</td>";
              echo "<td><a href='nas_connections.php?calling_station_id=" . urlencode($row['calling_station_id']) . "'>" . $row['calling_station_id'] . "</a></td>";
              echo "</tr>";
          }
          echo "</table>";
      } catch (PDOException $e) {
          echo "Connection failed: " . $e->getMessage();
      }
  } else {
      echo "No username selected.";
  }
  ?>

  <br>
  <a href="index.php">Back to Index</a>
</body>
</html>
