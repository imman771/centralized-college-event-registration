<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>Kongunadu Arts And Science College</title>
        <?php require 'utils/styles.php'; ?>
    </head>
    <body>
    <?php require 'utils/header.php'; ?>

    <div class="content">
        <div class="container">
            <div class="col-md-6 col-md-offset-3">
                <?php
                // Check if the event has reached its registration limit before showing the form
                if(isset($_GET['id'])) {
                    include 'classes/db1.php';
                    $event_id = $_GET['id'];
                    
                    $check_limit_query = "SELECT e.reg_limit, 
                                         (SELECT COUNT(*) FROM registered WHERE event_id = ?) as current_registrations 
                                         FROM events e 
                                         WHERE e.event_id = ?";
                                         
                    $stmt = $conn->prepare($check_limit_query);
                    $stmt->bind_param('ss', $event_id, $event_id);
                    $stmt->execute();
                    $limit_result = $stmt->get_result();
                    
                    if ($limit_result->num_rows > 0) {
                        $limit_row = $limit_result->fetch_assoc();
                        $reg_limit = $limit_row['reg_limit'];
                        $current_registrations = $limit_row['current_registrations'];
                        
                        // If registration limit is set and reached, show message instead of form
                        if ($reg_limit !== null && intval($reg_limit) > 0 && intval($current_registrations) >= intval($reg_limit)) {
                            echo "<div class='alert alert-danger'>
                                <h3>Registration Closed</h3>
                                <p>Sorry, this event has reached its registration limit of $reg_limit participants.</p>
                                <a href='index.php' class='btn btn-primary'>Return to Events</a>
                            </div>";
                            // Close connection and exit the script to prevent showing the form
                            $stmt->close();
                            $conn->close();
                            echo "</div></div></div>";
                            require 'utils/footer.php';
                            echo "</body></html>";
                            exit();
                        }
                    }
                    $stmt->close();
                    $conn->close();
                }
                ?>
                
                <form method="POST">
                    <!-- Hidden input for event_id -->
                    <label>Event ID (Hidden for registration purposes):</label><br>
                    <input type="text" name="event_id" value="<?php echo isset($_GET['id']) ? $_GET['id'] : ''; ?>" class="form-control" readonly><br><br>

                    <label>Student USN:</label><br>
                    <input type="text" name="usn" class="form-control" required><br><br>

                    <label>Student Name:</label><br>
                    <input type="text" name="name" class="form-control" required><br><br>

                    <label>Branch:</label><br>
                    <input type="text" name="branch" class="form-control" required><br><br>

                    <label>Semester:</label><br>
                    <input type="text" name="sem" class="form-control" required><br><br>

                    <label>Email:</label><br>
                    <input type="email" name="email" class="form-control" required><br><br>

                    <label>Phone:</label><br>
                    <input type="text" name="phone" class="form-control" required><br><br>

                    <label>College:</label><br>
                    <input type="text" name="college" class="form-control" required><br><br>

                    <button type="submit" name="update" class="btn btn-primary">Submit</button><br><br>
                    <a href="usn.php"><u>Already registered?</u></a>
                </form>
            </div>
        </div>
    </div>

    <?php require 'utils/footer.php'; ?>

    </body>
</html>

<?php
// Include PHPMailer
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';
require 'PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST["update"])) {
    $usn = $_POST["usn"];
    $name = $_POST["name"];
    $branch = $_POST["branch"];
    $sem = $_POST["sem"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $college = $_POST["college"];
    $event_id = $_POST["event_id"];

    if (!empty($usn) && !empty($name) && !empty($branch) && !empty($sem) && !empty($email) && !empty($phone) && !empty($college)) {

        include 'classes/db1.php';
        
        // Check again if the event has reached its registration limit (double check for safety)
        $check_limit_query = "SELECT e.reg_limit, 
                             (SELECT COUNT(*) FROM registered WHERE event_id = ?) as current_registrations 
                             FROM events e 
                             WHERE e.event_id = ?";
                             
        $stmt = $conn->prepare($check_limit_query);
        $stmt->bind_param('ss', $event_id, $event_id);
        $stmt->execute();
        $limit_result = $stmt->get_result();
        
        if ($limit_result->num_rows > 0) {
            $limit_row = $limit_result->fetch_assoc();
            $reg_limit = $limit_row['reg_limit'];
            $current_registrations = $limit_row['current_registrations'];
            
            // For debugging purposes
            error_log("Event ID: $event_id, Limit: $reg_limit, Current: $current_registrations");
            
            // If registration limit is set and reached, prevent registration
            if ($reg_limit !== null && intval($reg_limit) > 0 && intval($current_registrations) >= intval($reg_limit)) {
                echo "<script>
                alert('Sorry, this event has reached its registration limit of " . $reg_limit . "!');
                window.location.href='index.php';
                </script>";
                exit();
            }
        }

        // Check if student is already in participent table
        $check_participent_query = "SELECT * FROM participent WHERE usn = ?";
        $stmt = $conn->prepare($check_participent_query);
        $stmt->bind_param('s', $usn);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows == 0) {
            $INSERT_PARTICIPENT = "INSERT INTO participent (usn, name, branch, sem, email, phone, college) 
                                   VALUES(?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($INSERT_PARTICIPENT);
            $stmt->bind_param('sssssss', $usn, $name, $branch, $sem, $email, $phone, $college);

            if (!$stmt->execute()) {
                error_log("Error in participent table: " . $stmt->error);
                echo "<script>
                alert('Error in saving participant information. Please try again.');
                window.location.href='register.php?id=$event_id';
                </script>";
                exit();
            }
        }

        // Check if student is already registered for the event
        $check_registered_query = "SELECT * FROM registered WHERE usn = ? AND event_id = ?";
        $stmt = $conn->prepare($check_registered_query);
        $stmt->bind_param('ss', $usn, $event_id);
        $stmt->execute();
        $check_result = $stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo "<script>
            alert('You are already registered for this event!');
            window.location.href='usn.php';
            </script>";
        } else {
            $INSERT_REGISTERED = "INSERT INTO registered (usn, event_id) VALUES(?, ?)";
            $stmt = $conn->prepare($INSERT_REGISTERED);
            $stmt->bind_param('ss', $usn, $event_id);

            if ($stmt->execute()) {
                // Update participents count in events table
                $update_count = "UPDATE events SET participents = (SELECT COUNT(*) FROM registered WHERE event_id = ?) WHERE event_id = ?";
                $stmt = $conn->prepare($update_count);
                $stmt->bind_param('ss', $event_id, $event_id);
                $stmt->execute();
                
                sendConfirmationEmail($email, $name, $event_id); // Pass event_id to the function
                echo "<script>
                alert('Registered for the event successfully! A confirmation email has been sent.');
                window.location.href='usn.php';
                </script>";
            } else {
                error_log("Error in registered table: " . $stmt->error);
                echo "<script>
                alert('Error in event registration. Please try again.');
                window.location.href='register.php?id=$event_id';
                </script>";
            }
        }

        $stmt->close();
        $conn->close();
    } else {
        echo "<script>
        alert('All fields are required');
        window.location.href='register.php?id=$event_id';
        </script>";
    }
}

// Function to send confirmation email with event name
function sendConfirmationEmail($email, $name, $event_id) {
    // First, get the event name from the database
    include 'classes/db1.php';
    
    $event_query = "SELECT event_title FROM events WHERE event_id = ?";
    $stmt = $conn->prepare($event_query);
    $stmt->bind_param('s', $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event_name = "the event";
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $event_name = $row['event_title'];
    }
    
    $stmt->close();
    $conn->close();
    
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->SMTPDebug = 2; // Enable debug output
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'krishhjall@gmail.com';
        $mail->Password = 'mnxz ubba mmwi szhr'; // Use the App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('krishhjall@gmail.com', 'KASC Events');
        $mail->addAddress($email, $name);
        $mail->Subject = "Registration Confirmation for $event_name";
        $mail->Body = "Hello $name,\n\nYou have successfully registered for the event \"$event_name\".\n\nThank you for your registration.\n\nBest Regards,\nTeam KASC";

        $mail->send();
        echo "Email sent successfully!";
    } catch (Exception $e) {
        echo "Mail error: " . $mail->ErrorInfo; // Display error message
    }
}
?>