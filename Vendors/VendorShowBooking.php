<?php
session_start();
if (!isset($_SESSION['vendor'])) {
    header('Location: Login.php');
    exit();
}

include '../Connection.php';

$vendor_id = $_SESSION['vendor_id'];
$sql = "SELECT b.user_name, b.seat_number, b.booking_time, b.vehicle_no, b.amount_paid
        FROM bookings b 
        JOIN slots s ON b.slot_id = s.id
        WHERE s.id = $vendor_id AND b.end_time > NOW()
        ORDER BY b.booking_time";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel="shortcut icon" type="image/x-icon" href="../Car/Images/Main_Image.png">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>View All Bookings</title>
    <link rel='stylesheet' href='../Styles.css'>
    <link rel='stylesheet' href='../bootstrap.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body class='bg-content'>
    <div class='sidebar'>
        <?php include '../Includes/Vsidebar.php'; ?>
    </div>
    <div class='main-content'>
        <div class='container mt-1 col-11'>
            <h2 class='text-center'>View User's Bookings</h2>
            <hr class='w-25 mx-auto border-dark'>

            <?php if (!$result || mysqli_num_rows($result) == 0): ?>
                <div class="text-center">
                    <img src="../../Car/Images/Empty.gif" width="250" height="250" alt="No slots available" />
                    <p>No booking slots available.</p>
                </div>
            <?php else: ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search bookings...">
                    </div>
                </div>

                <table class='table table-striped h5 mt-5'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>User Name</th>
                            <th>Parking No.</th>
                            <th>Vehicle Number</th>
                            <th>Amount Paid</th>
                            <th>Booking Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>" . $i++ . "</td>
                                <td>" . htmlspecialchars($row['user_name']) . "</td>
                                <td>" . htmlspecialchars($row['seat_number']) . "</td>
                                <td>" . htmlspecialchars($row['vehicle_no']) . "</td>
                                <td>₹" . htmlspecialchars($row['amount_paid'] / 100) . "</td>
                                <td>" . htmlspecialchars($row['booking_time']) . "</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#searchInput").on("keyup", function() {
                var searchText = $(this).val().toLowerCase();
                $("table tbody tr").each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
                });
            });
        });
    </script>
</body>

</html>