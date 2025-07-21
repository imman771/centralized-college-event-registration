<?php
require 'classes/db1.php';

// Check if 'id' is set in the URL
if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Use intval to ensure it's an integer

    // Fetch event details based on type_id
    $result = mysqli_query($conn, "
        SELECT events.*, ef.Date, ef.time, ef.location, 
               s.st_name, st.name AS staff_name 
        FROM events 
        JOIN event_info ef ON ef.event_id = events.event_id 
        JOIN student_coordinator s ON s.event_id = events.event_id 
        JOIN staff_coordinator st ON st.event_id = events.event_id 
        WHERE events.type_id = $id
    ");

    // Check for SQL errors
    if (!$result) {
        die("Query failed: " . mysqli_error($conn));
    }
} else {
    die("No ID provided.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Kongunadu Arts And Science College</title>
    <?php require 'utils/styles.php'; ?><!--css links. file found in utils folder-->
</head>

<body>
    <?php require 'utils/header.php'; ?><!--header content. file found in utils folder-->
    
    <div class="content"><!--body content holder-->
        <div class="container">
            <div class="col-md-12"><!--body content title holder with 12 grid columns-->
                <h1>Event Details</h1>
            </div>
       
            <?php
            if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_array($result)) {
                    include 'events.php';  

                    // Add a link to register for the event
                   
                }
            } else {
                echo "<p>No events found for this type.</p>";
            }
            ?>
            <div class="container">
                <div class="col-md-12">
                    <hr>
                </div>
            </div>
            <a class="btn btn-default" href="index.php"><span class="glyphicon glyphicon-circle-arrow-left"></span> Back</a>
        </div><!--body content div-->
    </div>

    <?php require 'utils/footer.php'; ?><!--footer content. file found in utils folder-->
</body>
</html>
