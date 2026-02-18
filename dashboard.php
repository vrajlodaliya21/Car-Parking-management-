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

$sql = 'SELECT * FROM users';
$res = $conn->query($sql);
$total_Users = $res->num_rows;

$sql_slots = 'SELECT COUNT(*) AS total_slots FROM slots';
$res_slots = $conn->query($sql_slots);
$total_slots = $res_slots->fetch_assoc()['total_slots'];

$sql_total_vehicle = 'SELECT COUNT(*) AS total_vehicle FROM bookings';
$res_total_vehicle = $conn->query($sql_total_vehicle);
$total_vehicle = $res_total_vehicle->fetch_assoc()['total_vehicle'];

$sql_today = 'SELECT * FROM bookings WHERE DATE(booking_time) = CURDATE()';
$result_today = $conn->query($sql_today);
$count_today_vehentries = $result_today->num_rows;

$sql_yesterday = 'SELECT * FROM bookings WHERE DATE(booking_time) = CURDATE() - INTERVAL 1 DAY';
$result_yesterday = $conn->query($sql_yesterday);
$count_yesterday_vehentries = $result_yesterday->num_rows;

$sql_total_vendors = 'SELECT COUNT(*) AS total_vendors FROM vendors'; 
$res_total_vendors = $conn->query($sql_total_vendors);
$total_vendors = $res_total_vendors->fetch_assoc()['total_vendors'];


$sql_total_earnings = 'SELECT SUM(amount_paid) AS total_earnings FROM bookings';
$res_total_earnings = $conn->query($sql_total_earnings);
$total_earnings = $res_total_earnings->fetch_assoc()['total_earnings'];
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel="shortcut icon" type="image/x-icon" href="../Car/Images/Main_Image.png">
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Dashboard</title>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <link rel='stylesheet' href='./Styles.css'>
    <link rel='stylesheet' href='https://pro.fontawesome.com/releases/v5.10.0/css/all.css' />
    <style>
        .card {
            height: 220px;
            width: 284px;
            padding: 20px;
            border-radius: 15px;
        }
        .main-content {
            margin-left: 220px;
        }
        .sidebar {
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
        }

        .d-flex.flex-column span {
            font-size: 18px;
            font-weight: 500;
        }

        .card__items--blue i {
            color: #0077ff;
            font-size: 42px;
            background: rgba(0, 119, 255, 0.1);
            padding: 15px;
            border-radius: 12px;
        }

        .nbr {
            font-size: 38px;
            position: absolute;
            right: 25px;
            bottom: 15px;
            color: #0066cc;
            font-weight: 700;
        }

        .card-title {
            margin-top: 10px;
            font-size: 20px;
            color: #333;
        }

        @media screen and (min-width: 400px) {
            .sidebar {
                padding: 0px;
            }
        }
    </style>
</head>

<body class='bg-content'>
    <div class='sidebar'>
        <?php include './Includes/sidebar.php'; ?>
    </div>

    <main class='container d-flex'>
        <div class='container-fluid main-content px-4'>
            <div class='row mt-5'>
                <div class='col-lg-4 col-md-4 col-12 mt-2 gap'>
                    <div class='card card__items--blue'>
                        <div class='card-body'>
                            <div class='d-flex flex-column'>
                                <i class='fal fa-parking h2'></i>
                                <span class='mt-2' style="font-size:20px;">Total Parking Slots</span>
                            </div>
                            <div class='mt-5'>
                                <span class='fw-bold nbr'><?php echo $total_slots ?: '0' ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-lg-4 col-md-4 col-12 mt-2 gap'>
                    <div class='card card__items--blue'>
                        <div class='card-body'>
                            <div class='d-flex flex-column'>
                                <i class='fal fa-car h2'></i>
                                <span class='mt-2' style="font-size:20px;">Today Vehicle Entries</span>
                            </div>
                            <div class='mt-5'>
                                <span class='fw-bold nbr'>
                                    <?php echo $count_today_vehentries ?: '0'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-lg-4 col-md-4 col-12 mt-2 gap'>
                    <div class='card card__items--blue'>
                        <div class='card-body'>
                            <div class='d-flex flex-column'>
                                <i class='fal fa-car-side h2'></i>
                                <span class='mt-2' style="font-size:20px;">Yesterday Entries</span>
                            </div>
                            <div class='mt-5'>
                                <span class='fw-bold nbr'>
                                    <?php echo $count_yesterday_vehentries ?: '0'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class='row mt-3'>
                <div class='col-lg-4 col-md-4 col-12 mt-2 gap'>
                    <div class='card card__items--blue'>
                        <div class='card-body'>
                            <div class='d-flex flex-column'>
                                <i class='fal fa-user h2'></i>
                                <span class='mt-2' style="font-size:20px;">Total Users</span>
                            </div>
                            <div class='mt-5'>
                                <span class='fw-bold nbr'>
                                    <?php echo $total_Users ?: '0'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class='col-lg-4 col-md-4 col-12 mt-2 gap'>
                    <div class='card card__items--blue'>
                        <div class='card-body'>
                            <div class='d-flex flex-column'>
                                <i class='fal fa-building h2'></i>
                                <span class='mt-2' style="font-size:20px;">Total Vendors</span>
                            </div>
                            <div class='mt-5'>
                                <span class='fw-bold nbr'><?php echo $total_vendors ?: '0' ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='col-lg-4 col-md-4 col-12 mt-2 gap'>
                    <div class='card card__items--blue'>
                        <div class='card-body'>
                            <div class='d-flex flex-column'>
                                <i class='fal fa-rupee-sign h2'></i>
                                <span class='mt-2' style="font-size:20px;">Total Earnings</span>
                            </div>
                            <div class='mt-5'>
                                <span class='fw-bold nbr'>
                                    ₹<?php echo number_format($total_earnings / 100, 2) ?: '0'; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <?php include './Includes/Adminfooter.php'; ?>
</body>

</html>
