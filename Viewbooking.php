<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header('Location: Index.php');
    exit();
}
include 'Connection.php';

if (isset($_SESSION['user'])) {
    header('Location: Index.php');
    exit();
}

$sql = 'SELECT b.user_name, s.location, b.booking_time, b.vehicle_no, b.amount_paid, v.name AS vendor_name
        FROM bookings b 
        JOIN slots s ON b.slot_id = s.id
        JOIN vendors v ON s.id = v.id
        ORDER BY b.booking_time';


$result = mysqli_query($conn, $sql);

mysqli_data_seek($result, 0);
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel="shortcut icon" type="image/x-icon" href="../Car/Images/Main_Image.png">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>View All Bookings</title>
    <link rel='stylesheet' href='./Styles.css'>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <style>
        .sidebar {
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
        }

        .d-flex.flex-column span {
            font-size: 18px;
        }
    </style>
</head>

<body class='bg-content'>
    <div class='sidebar'>
        <?php include './Includes/sidebar.php'; ?>
    </div>
    <div class='main-content'>
        <div class='container mt-1 col-11'>
            <h2 class='text-center'>View Bookings</h2>
            <hr class='w-25 mx-auto border-dark'>

            <div class="row mb-4">
                <div class="col-12">
                    <div class="input-group">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search...">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search btn"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div id="searchResults">
                <table class='table table-striped h5 mt-5'>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>User Name</th>
                            <th>Vehicle Number</th>
                            <th>Location</th>
                            <th>Vendor Name</th>
                            <th>Booking Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1;
                        while ($row = mysqli_fetch_assoc($result)) { ?>
                            <tr>
                                <td><?php echo $i++; ?></td>
                                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['vehicle_no']); ?></td>
                                <td><?php echo htmlspecialchars($row['location']); ?></td>
                                <td><?php echo htmlspecialchars($row['vendor_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['booking_time']); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function () {
            $("#searchInput").on("keyup", function () {
                var searchText = $(this).val().toLowerCase();

                $("#searchResults table tbody tr").each(function () {
                    var row = $(this);
                    var text = row.text().toLowerCase();

                    if (text.indexOf(searchText) === -1) {
                        row.hide();
                    } else {
                        row.show();
                    }
                });
            });
        });
    </script>
</body>

</html>