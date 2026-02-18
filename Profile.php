<?php
session_start();
if (isset($_SESSION['admin'])) {
    header('Location: Index.php');
    exit();
}
include 'Connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: Index.php');
    exit();
}

include 'Connection.php';
$user_name = $_SESSION['user'];

// Fetch user details including phone number
$sql_user = 'SELECT U_Name, U_Email, Phone_number FROM users WHERE U_Name = ?';
$stmt_user = $conn->prepare($sql_user);
$stmt_user->bind_param('s', $user_name);

if (!$stmt_user->execute()) {
    echo 'Error executing user query: ' . $stmt_user->error;
    exit();
}

$result_user = $stmt_user->get_result();
$user_data = $result_user->fetch_assoc();

if ($user_data) {
    $sql_booking = "SELECT b.slot_id, s.location, b.vehicle_no, s.price, b.seat_number 
                    FROM bookings b 
                    JOIN slots s ON b.slot_id = s.id 
                    WHERE b.user_name = ?";
    $stmt_booking = $conn->prepare($sql_booking);
    $stmt_booking->bind_param('s', $user_name);

    if (!$stmt_booking->execute()) {
        echo 'Error executing booking query: ' . $stmt_booking->error;
        exit();
    }

    $result_booking = $stmt_booking->get_result();
    $bookings = $result_booking->fetch_all(MYSQLI_ASSOC);

    $has_bookings = !empty($bookings);

    $total_cost = 0;
    foreach ($bookings as $booking) {
        $total_cost += $booking['price'];
    }
} else {
    echo 'No user data found.';
}

$slot_booked = $has_bookings ? 'Yes' : 'No';
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>User Profile</title>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <link rel='stylesheet' href='./Styles.css'>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }

        .profile-container {
            width: 100%;
            padding: 20px;
            background-color: #ffffff;
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .profile-header h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .profile-header p {
            font-size: 16px;
            color: #666;
        }

        .profile-details {
            margin-bottom: 30px;
        }

        .profile-details h4 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .profile-details table {
            width: 100%;
            border-collapse: collapse;
        }

        .profile-details th,
        .profile-details td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        .profile-details th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #555;
            text-align: left;
        }

        .profile-details td {
            color: #333;
        }

        .booking-details {
            margin-top: 30px;
        }

        .booking-details h4 {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .booking-details table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .booking-details th,
        .booking-details td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        .booking-details th {
            background-color: #007bff;
            color: #fff;
            font-weight: bold;
            text-align: left;
        }

        .booking-details td {
            color: #333;
        }

        .no-bookings {
            font-size: 16px;
            color: #666;
            text-align: center;
            padding: 20px;
        }

        .total-cost {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-top: 20px;
            text-align: right;
        }
    </style>
</head>

<body>
    <div>
        <?php include './Includes/Unavbar.php';
        ?>
    </div>
    <div class='container col-11'>
        <div class='profile-container mt-3'>
            <div class='profile-header mt-5'>
                <h2>
                    <?php echo htmlspecialchars($user_data['U_Name']);
                    ?>
                </h2>
                <p>Welcome to your profile!</p>
            </div>
            <div class='profile-details'>
                <h4>Personal Information :</h4>
                <table>
                    <tr>
                        <th>Username</th>
                        <td>
                            <?php echo htmlspecialchars($user_data['U_Name']);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>
                            <?php echo htmlspecialchars($user_data['U_Email']);
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Phone Number</th>
                        <td>
                            <?php echo htmlspecialchars($user_data['Phone_number'] ?: 'N/A');
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Slot Booked</th>
                        <td>
                            <?php echo $slot_booked;
                            ?>
                        </td>
                    </tr>
                </table>
            </div>
            <?php if ($slot_booked == 'Yes'): ?>
                <div class='booking-details'>
                    <h4>Booked Slots :</h4>
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Location</th>
                                <th>Slot #</th>
                                <th>Vehicle No</th>
                                <th>Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <?php echo $i++;
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['location']);
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['seat_number']);
                                        ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['vehicle_no']);
                                        ?>
                                    </td>
                                    <td>Rs.
                                        <?php echo htmlspecialchars($booking['price']);
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach;
                            ?>
                            <tr style='font-weight: bold; background-color: #f8f9fa;'>
                                <td colspan='4' style='text-align: right;'>Total Cost</td>
                                <td>Rs.
                                    <?php echo htmlspecialchars($total_cost);
                                    ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class='no-bookings'>
                    <p>No slots booked yet.</p>
                </div>
            <?php endif;
            ?>

        </div>
    </div>
</body>

</html>

<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>