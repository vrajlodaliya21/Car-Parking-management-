<?php
include 'Connection.php';

$checkColumn = $conn->query("SHOW COLUMNS FROM bookings LIKE 'created_at'");
if ($checkColumn->num_rows == 0) {
    if ($conn->query("ALTER TABLE bookings ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP")) {
        echo "Column 'created_at' added successfully.";
        // Update existing rows to match their booking_time or current_timestamp
        $conn->query("UPDATE bookings SET created_at = booking_time WHERE created_at IS NULL OR created_at = '0000-00-00 00:00:00'");
    } else {
        echo "Error adding column: " . $conn->error;
    }
} else {
    echo "Column 'created_at' already exists.";
}
?>