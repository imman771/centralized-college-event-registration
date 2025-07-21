<div class="container">
    <div class="col-md-12">
        <hr>
    </div>

    <div class="row">
        <section>
            <div class="container">
                <div class="col-md-6">
                    <img src="<?php echo $row['img_link']; ?>" class="img-responsive">
                </div>
                <div class="subcontent col-md-6">                        
                    <h1 style="color:#003300; font-size:38px;"><u><strong><?php echo $row['event_title']; ?></strong></u></h1><!--title-->
                    <p style="color:#003300; font-size:20px;"> <!--content-->
                        <?php
                        echo 'Date: ' . $row['Date'] . '<br>'; 
                        echo 'Time: ' . $row['time'] . '<br>'; 
                        echo 'Location: ' . $row['location'] . '<br>'; 
                        echo 'Student Co-ordinator: ' . $row['st_name'] . '<br>'; 
                        echo 'Staff Co-ordinator: ' . $row['staff_name'] . '<br>'; // Changed from 'name' to 'staff_name'
                        echo 'Event Price: ' . $row['event_price'] . '<br>';
                        echo 'Department: ' . $row['department'] . '<br>'; // Display department
                        echo 'Event Details: ' . $row['event_details'] . '<br>'; // Display event details
                        ?>
                    </p>
                    
                    <br><br>
                    <?php echo "<a href='register.php?id=" . $row['event_id'] . "' class='btn btn-primary'>Register</a>"; ?>
                </div><!--subcontent div-->
            </div><!--container div-->
        </section>
    </div>
</div><!--row div-->
