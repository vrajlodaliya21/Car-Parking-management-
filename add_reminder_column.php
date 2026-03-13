<?php
include 'Connection.php';
$sql = "ALTER TABLE bookings ADD COLUMN reminder_sent TINYINT(1) DEFAULT 0";
if (mysqli_query($conn, $sql)) {
    echo "Column reminder_sent added successfully";
} else {
    echo "Error adding column: " . mysqli_error($conn);
}
?>