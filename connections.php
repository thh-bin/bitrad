<!DOCTYPE html>
<html>
<head>
  <title>Connections Made Through NAS</title>
  <style>
    /* CSS styles for connections.php */
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

  // Check if calledstationid is provided in the URL
  if (isset($_GET['calledstationid'])) {
      $calledStationID = $_GET['calledstationid'];

      try {
          // Connect to the database
          $conn = new PDO("mysql:host=$servername;dbname=$dbname", $radusername, $password);
          // Set the PDO error mode to exception
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          // Query to fetch all connections made through the specified NAS
          $sql = "SELECT username, acctsessionid, acctstarttime, acctstoptime, acctsessiontime AS session_time, callingstationid AS calling_station_id, framedinterfaceid AS framed_interface_id
                  FROM radacct
                  WHERE calledstationid = :calledstationid AND acctstoptime IS NOT NULL
                  ORDER BY acctstarttime DESC";

          // Prepare the statement
          $stmt = $conn->prepare($sql);
          $stmt->bindParam(':calledstationid', $calledStationID);
          $stmt->execute();

          // Display the connections made through the NAS
          echo "<h2>Connections Made Through NAS: " . $calledStationID . "</h2>";
          echo "<table>";
          echo "<tr><th>Username</th><th>Session ID</th><th>Start Time</th><th>Stop Time</th><th>Acct. Session Time</th><th>Client MAC</th><th>Framed Interface ID</th></tr>";
          foreach ($stmt as $row) {
              echo "<tr>";
              echo "<td>" . $row['username'] . "</td>";
              echo "<td>" . $row['acctsessionid'] . "</td>";
              echo "<td>" . $row['acctstarttime'] . "</td>";
              echo "<td>" . $row['acctstoptime'] . "</td>";
              echo "<td>" . $row['session_time'] . " seconds</td>";
              echo "<td><a href='nas_connections.php?calling_station_id=" . urlencode($row['calling_station_id']) . "'>" . $row['calling_station_id'] . "</a></td>";
              echo "<td>" . $row['framed_interface_id'] . "</td>";
              echo "</tr>";
          }
          echo "</table>";
      } catch (PDOException $e) {
          echo "Connection failed: " . $e->getMessage();
      }
  } else {
      echo "No NAS ID provided.";
  }
  ?>

  <br>
  <a href="index.php">Back to Index</a>
</body>
</html>
