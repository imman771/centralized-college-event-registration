<?php
include_once 'classes/db1.php';

// Include PHPMailer
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST['event_id'])) {
    $event_id = $_POST['event_id'];
    $event_title = mysqli_real_escape_string($conn, $_POST['event_title']);
    $date = mysqli_real_escape_string($conn, $_POST['date']);  
    $time = mysqli_real_escape_string($conn, $_POST['time']);  
    $location = mysqli_real_escape_string($conn, $_POST['location']); 

    // Update event details
    $query1 = "UPDATE events SET event_title = '$event_title' WHERE event_id = '$event_id'";
    $query2 = "UPDATE event_info SET date = '$date', time = '$time', location = '$location' WHERE event_id = '$event_id'";

    if (mysqli_query($conn, $query1) && mysqli_query($conn, $query2)) {
        echo "Event updated successfully.";

        // Fetch all registered participants for the event
        $email_query = "SELECT p.email, p.name 
                        FROM registered r 
                        JOIN participent p ON r.usn = p.usn 
                        WHERE r.event_id = '$event_id'";
        $result = mysqli_query($conn, $email_query);

        if ($result && mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $recipient_email = $row['email'];
                $recipient_name = $row['name'];
                sendUpdateEmail($recipient_email, $recipient_name, $event_title, $date, $time, $location);
            }
        }
    } else {
        echo "Error updating event: " . mysqli_error($conn);
    }
}

// Function to send update email
function sendUpdateEmail($email, $name, $event_title, $date, $time, $location) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'krishhjall@gmail.com'; // Replace with your email
        $mail->Password = 'mnxz ubba mmwi szhr'; // Replace with your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Email Details
        $mail->setFrom('krishhjall@gmail.com', 'Event Management System');
        $mail->addAddress($email, $name);
        $mail->Subject = "Event Updated: $event_title";
        $mail->Body = "Hello $name,\n\nThe event '$event_title' has been updated.\n\nNew Details:\n📅 Date: $date\n⏰ Time: $time\n📍 Location: $location\n\nBest Regards,\nEvent Management Team";

        // Send Email
        $mail->send();
    } catch (Exception $e) {
        error_log("Mail error: " . $mail->ErrorInfo);
    }
}
?>
