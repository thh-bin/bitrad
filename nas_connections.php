<!DOCTYPE html>
<html>
<head>
  <title>Connections Made Through NAS</title>
  <style>
    /* CSS styles for nas_connections.php */
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

  // Check if calling_station_id or framed_ip is provided in the URL
  if (isset($_GET['calling_station_id']) || isset($_GET['framed_ip'])) {
      $callingStationID = isset($_GET['calling_station_id']) ? $_GET['calling_station_id'] : null;
      $framedIP = isset($_GET['framed_ip']) ? $_GET['framed_ip'] : null;

      try {
          // Connect to the database
          $conn = new PDO("mysql:host=$servername;dbname=$dbname", $radusername, $password);
          // Set the PDO error mode to exception
          $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

          // Prepare the SQL statement based on provided parameters
          $sql = "SELECT DISTINCT calledstationid AS called_station_id, nasipaddress AS nas_ip_address
                  FROM radacct
                  WHERE";

          $whereClauses = [];

          if ($callingStationID) {
              $whereClauses[] = " callingstationid = :callingstationid";
          }

          if ($framedIP) {
              $whereClauses[] = " framedipaddress = :framedipaddress";
          }

          $sql .= implode(" OR", $whereClauses);

          // Prepare the statement
          $stmt = $conn->prepare($sql);

          // Bind parameters if provided
          if ($callingStationID) {
              $stmt->bindParam(':callingstationid', $callingStationID);
          }

          if ($framedIP) {
              $stmt->bindParam(':framedipaddress', $framedIP);
          }

          // Execute the statement
          $stmt->execute();

          // Display the connections made through the NAS
          echo "<h2>Connections Made Through NAS:</h2>";
          echo "<table>";
          echo "<tr><th>Called Station ID</th><th>NAS IP Address</th></tr>";
          foreach ($stmt as $row) {
              echo "<tr>";
              echo "<td>" . $row['called_station_id'] . "</td>";
              echo "<td>" . $row['nas_ip_address'] . "</td>";
              echo "</tr>";
          }
          echo "</table>";
      } catch (PDOException $e) {
          echo "Connection failed: " . $e->getMessage();
      }
  } else {
      echo "No NAS ID or IP Address provided.";
  }
  ?>

  <br>
  <a href="index.php">Back to Index</a>
</body>
</html>
