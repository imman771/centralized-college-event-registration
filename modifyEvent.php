<?php
include_once 'classes/db1.php';

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

    // Fetch event details from events table (title) and event_info table (date, time, location)
    $result = mysqli_query($conn, "
        SELECT e.event_id, e.event_title, ei.Date, ei.time, ei.location 
        FROM events e
        INNER JOIN event_info ei ON e.event_id = ei.event_id
        WHERE e.event_id = '$event_id'
    ");
    
    // Fetch the result into an associative array
    $event = mysqli_fetch_array($result);

    // Check if the event was found
    if (!$event) {
        echo "Event not found!";
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Modify Event</title>
</head>
<body>
<?php include 'utils/adminHeader.php'?>

<div class="content">
    <div class="container">
        <h1>Modify Event</h1>
        <form action="updateEvent.php" method="POST">
            <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
            <div>
                <label for="event_title">Event Name:</label>
                <input type="text" id="event_title" name="event_title" value="<?php echo $event['event_title']; ?>" required>
            </div>
            <div>
                <label for="date">Date:</label>
                <input type="text" id="date" name="date" value="<?php echo $event['Date']; ?>" required placeholder="dd-mm-yyyy">
            </div>
            <div>
                <label for="time">Time:</label>
                <input type="text" id="time" name="time" value="<?php echo $event['time']; ?>" required placeholder="hh:mm">
            </div>
            <div>
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" value="<?php echo $event['location']; ?>" required>
            </div>
            <div>
                <button type="submit">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<?php require 'utils/footer.php'; ?>
</body>
</html>

