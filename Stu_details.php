<?php
include_once 'classes/db1.php';

// Get filter values from the URL (GET request)
$event_title_filter = isset($_GET['event_title']) ? mysqli_real_escape_string($conn, $_GET['event_title']) : '';
$usn_filter = isset($_GET['usn']) ? mysqli_real_escape_string($conn, $_GET['usn']) : '';

// Build the WHERE clause
$where_clauses = array();
if ($event_title_filter) {
    $where_clauses[] = "events.event_title LIKE '%$event_title_filter%'";
}
if ($usn_filter) {
    $where_clauses[] = "r.usn LIKE '%$usn_filter%'";
}

// Combine all where conditions
$where_sql = '';
if (count($where_clauses) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where_clauses);
}

// Handle delete request
if (isset($_GET['delete_id'])) {
  $delete_id = mysqli_real_escape_string($conn, $_GET['delete_id']);

  // Delete query (Adjust according to your table structure)
  $deleteQuery = "DELETE FROM registered WHERE usn = '$delete_id'";
  if (mysqli_query($conn, $deleteQuery)) {
      echo "Record deleted successfully.";
  } else {
      echo "Error deleting record: " . mysqli_error($conn);
  }

  // Redirect after deletion to avoid re-deleting on page refresh
  header("Location: Stu_details.php");
  exit;
}


// Check if export is requested
if (isset($_GET['export'])) {
    // SQL Query to fetch the data for export
    $query = "SELECT * FROM events
              INNER JOIN registered r ON events.event_id = r.event_id
              INNER JOIN participent p ON r.usn = p.usn
              $where_sql
              ORDER BY events.event_title";

    // Execute the query
    $result = mysqli_query($conn, $query);

    // Check for query failure
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }

    // Set headers for Excel file download
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="event_report.xls"');

    // Output column headers
    echo "USN\tName\tBranch\tSem\tEmail\tPhone\tCollege\tEvent Title\n";

    // Output data rows
    while ($row = mysqli_fetch_array($result)) {
        echo $row["usn"] . "\t" .
             $row["name"] . "\t" .
             $row["branch"] . "\t" .
             $row["sem"] . "\t" .
             $row["email"] . "\t" .
             $row["phone"] . "\t" .
             $row["college"] . "\t" .
             $row["event_title"] . "\n";
    }
    exit; // Terminate script after outputting the file
}

// SQL Query to fetch the data for displaying in table
$query = "SELECT * FROM events
          INNER JOIN registered r ON events.event_id = r.event_id
          INNER JOIN participent p ON r.usn = p.usn
          $where_sql
          ORDER BY events.event_title";

// Execute the query for display
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Kongunadu Arts And Science College</title>
    <?php require 'utils/styles.php'; ?>
</head>

<body><?php include 'utils/adminHeader.php'?>
<div class="content">
    <div class="container">
        <h1>Student details</h1>

        <!-- Filter Form -->
        <form method="get" action="">
            <label for="event_title">Select Event:</label>
            <select name="event_title" id="event_title">
                <option value="">--Select Event--</option>
                <?php
                // Fetch all events from the database
                $eventQuery = "SELECT event_title FROM events";
                $eventResult = mysqli_query($conn, $eventQuery);

                // Check if events are found and populate the dropdown
                while ($eventRow = mysqli_fetch_array($eventResult)) {
                    $selected = isset($_GET['event_title']) && $_GET['event_title'] == $eventRow['event_title'] ? 'selected' : '';
                    echo "<option value='{$eventRow['event_title']}' $selected>{$eventRow['event_title']}</option>";
                }
                ?>
            </select>

            <label for="usn">USN:</label>
            <input type="text" name="usn" id="usn" placeholder="Filter by USN" value="<?php echo isset($_GET['usn']) ? $_GET['usn'] : ''; ?>">

            <input type="submit" value="Filter">
        </form>

        <!-- Export to Excel Button -->
        <!-- Passing the filter values in the export link -->
        <form method="get" action="">
            <input type="hidden" name="event_title" value="<?php echo isset($_GET['event_title']) ? $_GET['event_title'] : ''; ?>">
            <input type="hidden" name="usn" value="<?php echo isset($_GET['usn']) ? $_GET['usn'] : ''; ?>">
            <input type="submit" name="export" value="Download Excel Report">
        </form>

        <!-- Display the results -->
        <?php
        if ($result && mysqli_num_rows($result) > 0) {
            ?>
            <table class="table table-hover">
                <tr>
                    <th>USN</th>
                    <th>Name</th>
                    <th>Branch</th>
                    <th>Sem</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>College</th>
                    <th>Event</th>
                    <th>Action</th>
                </tr>
            <?php
            while ($row = mysqli_fetch_array($result)) {
                ?>
                <tr>
                    <td><?php echo $row["usn"]; ?></td>
                    <td><?php echo $row["name"]; ?></td>
                    <td><?php echo $row["branch"]; ?></td>
                    <td><?php echo $row["sem"]; ?></td>
                    <td><?php echo $row["email"]; ?></td>
                    <td><?php echo $row["phone"]; ?></td>
                    <td><?php echo $row["college"]; ?></td>
                    <td><?php echo $row["event_title"]; ?></td>
                    <td>
                        <!-- Delete Button -->
                        <a href="Stu_details.php?delete_id=<?php echo $row['usn']; ?>" onclick="return confirm('Are you sure you want to delete this record?');">
                            <button class="btn btn-danger">Delete</button>
                        </a>
                    </td>
                </tr>
                <?php
            }
            ?>
            </table>
            <?php
        } else {
            echo "No results found or query failed.";
        }
        ?>
    </div>
</div>

<?php include 'utils/footer.php'; ?>
</body>
</html>
