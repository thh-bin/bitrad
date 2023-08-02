<!DOCTYPE html>
<html>
<head>
  <title>Monitoring Interface</title>
  <style>
    /* CSS styles for index.php */
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f2f2;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
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

    /* CSS styles for the pagination links */
    .pagination {
      margin-top: 10px;
      display: flex;
      justify-content: center;
    }

    .pagination a {
      padding: 6px 10px;
      border: 1px solid #007bff;
      background-color: #fff;
      color: #007bff;
      margin: 0 5px;
      text-decoration: none;
    }

    .pagination a.active {
      background-color: #007bff;
      color: #fff;
    }

    /* CSS styles for the items-per-page form */
    .items-per-page-form {
      margin-top: 10px;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .items-per-page-form label {
      margin-right: 5px;
    }
  </style>
</head>
<body>
<?php
$servername = "localhost";
$radusername = "radupd";
$password = "radusrpwd";
$dbname = "radius";

// Number of records to display per page (default to 10)
$recordsPerPage = isset($_GET['records_per_page']) ? intval($_GET['records_per_page']) : 10;

// Current page number (default to 1 if not provided)
$page = isset($_GET['page']) ? $_GET['page'] : 1;

try {
    // Connect to the database
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $radusername, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Sample query to fetch most recent session information for each username
    $sql = "SELECT DISTINCT username, acctsessiontime AS session_time, framedipaddress AS ip_address, calledstationid AS called_station_id, callingstationid AS calling_station_id
            FROM radacct
            WHERE (username, radacctid) IN (SELECT username, MAX(radacctid) FROM radacct WHERE acctstoptime IS NULL GROUP BY username)
            ORDER BY username";

    // Execute the query to get the total number of records
    $result = $conn->query($sql);
    $totalRecords = $result->rowCount();

    // Calculate total number of pages
    $totalPages = ceil($totalRecords / $recordsPerPage);

    // Adjust page number if it exceeds total pages
    if ($page > $totalPages) {
        $page = $totalPages;
    }

    // Calculate the starting record for the current page
    $startingRecord = ($page - 1) * $recordsPerPage;

    // Adjust the SQL query with pagination
    $sql .= " LIMIT $startingRecord, $recordsPerPage";

    // Execute the updated query
    $result = $conn->query($sql);

    // Display the items-per-page form
    echo "<div class='items-per-page-form'>";
    echo "<label for='records_per_page'>Items per page:</label>";
    echo "<select name='records_per_page' id='records_per_page' onchange='changeRecordsPerPage()'>";
    echo "<option value='5'" . ($recordsPerPage == 5 ? " selected" : "") . ">5</option>";
    echo "<option value='10'" . ($recordsPerPage == 10 ? " selected" : "") . ">10</option>";
    echo "<option value='15'" . ($recordsPerPage == 15 ? " selected" : "") . ">15</option>";
    echo "<option value='20'" . ($recordsPerPage == 20 ? " selected" : "") . ">20</option>";
    echo "<option value='25'" . ($recordsPerPage == 25 ? " selected" : "") . ">25</option>";
    echo "</select>";
    echo "</div>";

    // Display the results
    echo "<table>";
    echo "<tr><th>Username</th><th>Acct. Session Time</th><th>IP Address</th><th>Called Station ID</th><th>Client MAC</th><th>Client IP</th></tr>";
    foreach ($result as $row) {
        echo "<tr>";
        echo "<td><a href='sessions.php?username=" . urlencode($row['username']) . "'>" . $row['username'] . "</a></td>";
        echo "<td>" . $row['session_time'] . " seconds</td>";
        echo "<td>" . $row['ip_address'] . "</td>";
        echo "<td>" . $row['called_station_id'] . "</td>";
        echo "<td><a href='nas_connections.php?calling_station_id=" . urlencode($row['calling_station_id']) . "'>" . $row['calling_station_id'] . "</a></td>";
        echo "<td><a href='nas_connections.php?framed_ip=" . urlencode($row['ip_address']) . "'>" . $row['ip_address'] . "</a></td>";
        echo "</tr>";
    }
    echo "</table>";

    // Display pagination links
    echo "<div class='pagination'>";
    for ($i = 1; $i <= $totalPages; $i++) {
        echo "<a href='index.php?page=$i&records_per_page=$recordsPerPage'" . ($i == $page ? " class='active'" : "") . ">$i</a>";
    }
    echo "</div>";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<script>
  // Function to change the number of records per page
  function changeRecordsPerPage() {
    const recordsPerPage = document.getElementById('records_per_page').value;
    window.location.href = `index.php?records_per_page=${recordsPerPage}`;
  }
</script>
</body>
</html>
