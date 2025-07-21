<?php
include 'classes/db1.php';
$id = $_GET['id']; // Get the staff coordinator ID from URL (stid)

// Check if ID is valid
if (!isset($id) || !is_numeric($id)) {
    echo "<script>alert('Invalid ID.'); window.location.href='stu_cordinator.php';</script>";
    exit;
}

if (isset($_POST["update"])) {
    $name = $_POST["st_name"];
    $phone = $_POST["phone"];
    
    // Debugging: Check if phone is being passed correctly
    var_dump($_POST); // Debugging
    
    // Ensure phone number is not empty
    if (empty($phone)) {
        echo "<script>alert('Phone number cannot be empty.'); window.location.href='updateStudent.php?id=$id';</script>";
        exit;
    }

    // Check if phone number is valid
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        echo "<script>alert('Please enter a valid 10-digit phone number.'); window.location.href='updateStudent.php?id=$id';</script>";
        exit;
    }

    // Prepare SQL to update the staff coordinator's details
    $sql = "UPDATE staff_coordinator SET phone = ?, name = ? WHERE event_id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameters and execute the statement
        $stmt->bind_param('ssi', $phone, $name, $id); // 'ssi' means string, string, integer
        
        if ($stmt->execute()) {
            echo "<script>alert('Updated Successfully'); window.location.href='stu_cordinator.php';</script>";
        } else {
            // Log SQL error message if update fails
            echo "<script>alert('Error updating record: " . $stmt->error . "'); window.location.href='updateStudent.php?id=$id';</script>";
        }
        
        $stmt->close();
    } else {
        // Log error if preparing the statement fails
        echo "<script>alert('Error preparing statement: " . $conn->error . "'); window.location.href='updateStudent.php?id=$id';</script>";
    }

    $conn->close();
}
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Kongunadu Arts And Science College</title>
    <?php require 'utils/styles.php'; ?>
</head>
<body>
<?php require 'utils/header.php'; ?>
<div class="content"><!--body content holder-->
    <div class="container">
        <div class="col-md-6 col-md-offset-3">
            <form method="POST">
                <label>Staff co-ordinator name</label><br>
                <input type="text" name="st_name" required class="form-control"><br><br>

                <label>Staff co-ordinator phone</label><br>
                <input type="text" name="phone" required class="form-control"><br><br>

                <button type="submit" name="update" class="btn btn-default">Update</button>
            </form>
        </div>
    </div>
</div>
<?php require 'utils/footer.php'; ?>
</body>
</html>
