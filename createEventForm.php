<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Kongunadu Arts And Science College</title>
    <?php require 'utils/styles.php'; ?><!--css links. file found in utils folder-->
</head>
<body>
<?php require 'utils/adminHeader.php'; ?>
<form method="POST" enctype="multipart/form-data">
    <div class="w3-container">
        <div class="content">
            <div class="container">
                <div class="col-md-6 col-md-offset-3">
                    <label>Event ID:</label><br>
                    <input type="number" name="event_id" required class="form-control"><br><br>

                    <label>Event Name:</label><br>
                    <input type="text" name="event_title" required class="form-control"><br><br>

                    <label>Event Price:</label><br>
                    <input type="number" name="event_price" required class="form-control"><br><br>

                    <label>Registration Limit:</label><br>
                    <input type="number" name="reg_limit" required class="form-control" min="1"><br><br>

                    <label>Upload Event Image:</label><br>
                    <input type="file" name="event_image" required class="form-control"><br><br>

                    
                    <input type="number" name="type_id" value="1" style="display:none;" required class="form-control"><br><br>

                    <label>Event Date</label><br>
                    <input type="date" name="Date" required class="form-control"><br><br>

                    <label>Event Time</label><br>
                    <input type="text" name="time" required class="form-control"><br><br>

                    <label>Event Location</label><br>
                    <input type="text" name="location" required class="form-control"><br><br>

                    <label>Department:</label><br>
                    <input type="text" name="department" required class="form-control"><br><br>

                    <label>Event Details:</label><br>
                    <textarea name="event_details" required class="form-control" rows="4"></textarea><br><br>

                    <label>Staff co-ordinator name</label><br>
                    <input type="text" name="sname" required class="form-control"><br><br>

                    <label>Student co-ordinator name</label><br>
                    <input type="text" name="st_name" required class="form-control"><br><br>

                    <button type="submit" name="update" class="btn btn-default pull-right">Create Event <span class="glyphicon glyphicon-send"></span></button>

                    <a class="btn btn-default navbar-btn" href="adminPage.php"><span class="glyphicon glyphicon-circle-arrow-left"></span> Back</a>
                </div>
            </div>
        </div>
    </div>
</form>


<?php
if (isset($_POST["update"])) {
    $event_id = $_POST["event_id"];
    $event_title = $_POST["event_title"];
    $event_price = $_POST["event_price"];
    $reg_limit = $_POST["reg_limit"];
    $type_id = $_POST["type_id"];
    $name = $_POST["sname"];
    $st_name = $_POST["st_name"];
    $Date = $_POST["Date"];
    $time = $_POST["time"];
    $location = $_POST["location"];
    $department = $_POST["department"];
    $event_details = $_POST["event_details"];

    // Handle image upload
    if (isset($_FILES["event_image"])) {
        $image = $_FILES["event_image"];
        $image_name = $image["name"];
        $image_tmp_name = $image["tmp_name"];
        $image_error = $image["error"];
        $image_size = $image["size"];

        // Debugging output (remove after confirming it's working)
        echo "Image Error: " . $image_error . "<br>";
        echo "Image Size: " . $image_size . "<br>";

        // Set the target directory to store the uploaded images
        $target_dir = "uploads/";
        
        // Generate a unique name for the image to avoid overwriting files
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_image_name = uniqid('', true) . "." . $image_ext;
        $target_file = $target_dir . $new_image_name;

        // Check if there were no errors with the file upload
        if ($image_error === 0) {
            // Check if the file is too large
            if ($image_size < 5000000) { // 5MB limit
                // Move the uploaded file to the uploads folder
                if (move_uploaded_file($image_tmp_name, $target_file)) {
                    // Prepare the file path to be stored in the database
                    $img_link = $target_file;
                } else {
                    echo "<script>
                    alert('Error uploading file');
                    window.location.href='createEventForm.php';
                    </script>";
                    exit();
                }
            } else {
                echo "<script>
                alert('File is too large. Max size is 5MB.');
                window.location.href='createEventForm.php';
                </script>";
                exit();
            }
        } else {
            echo "<script>
            alert('Error uploading file.');
            window.location.href='createEventForm.php';
            </script>";
            exit();
        }
    }

    // Debugging output: Checking all fields
    echo "Event ID: $event_id<br>";
    echo "Event Title: $event_title<br>";
    echo "Event Price: $event_price<br>";
    echo "Registration Limit: $reg_limit<br>";
    echo "Image Path: $img_link<br>";
    echo "Type ID: $type_id<br>";
    echo "Department: $department<br>";
    echo "Event Details: $event_details<br>";

    // Check if all required fields are filled
    if (!empty($event_id) && !empty($event_title) && !empty($event_price) && !empty($img_link) && !empty($type_id) && !empty($name) && !empty($st_name) && !empty($reg_limit)) {
        include 'classes/db1.php';

        // Prepare the multi-query to insert data into events and event_info tables, including department and event details
        $INSERT = "INSERT INTO events(event_id, event_title, event_price, img_link, type_id, department, event_details, reg_limit) 
                   VALUES ($event_id, '$event_title', $event_price, '$img_link', $type_id, '$department', '$event_details', $reg_limit);";
        $INSERT .= "INSERT INTO event_info (event_id, Date, time, location) VALUES ($event_id, '$Date', '$time', '$location');";
        $INSERT .= "INSERT INTO student_coordinator(st_name, phone, event_id) VALUES ('$st_name', NULL, $event_id);";
        $INSERT .= "INSERT INTO staff_coordinator(name, phone, event_id) VALUES ('$name', NULL, $event_id);";

        // Execute the multi-query
        if ($conn->multi_query($INSERT)) {
            do {
                // Store first result set
                if ($result = $conn->store_result()) {
                    $result->free();
                }
            } while ($conn->more_results() && $conn->next_result());

            echo "<script>
            alert('Event Inserted Successfully!');
            window.location.href='adminPage.php';
            </script>";
        } else {
            // Log the error
            error_log("SQL Error: " . $conn->error);
            echo "<script>
            alert('Error: " . $conn->error . "');
            window.location.href='createEventForm.php';
            </script>";
        }

        $conn->close();
    } else {
        echo "<script>
        alert('All fields are required');
        window.location.href='createEventForm.php';
        </script>";
    }
}


?>

<?php require 'utils/footer.php'; ?>
</body>
</html>