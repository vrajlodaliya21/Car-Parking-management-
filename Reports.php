<?php
require_once 'Connection.php';

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

$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

function getTotalBookings($conn, $start_date, $end_date)
{
    $sql = "SELECT COUNT(*) as total FROM bookings 
            WHERE DATE(booking_time) BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

function getTotalRevenue($conn, $start_date, $end_date)
{
    $sql = "SELECT SUM(amount_paid) as total FROM bookings 
            WHERE DATE(booking_time) BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $total = $row['total'] ?? 0;
    return number_format($total / 100, 2);
}

function getActiveUsers($conn, $start_date, $end_date)
{
    $sql = "SELECT COUNT(DISTINCT user_name) as total FROM bookings 
            WHERE DATE(booking_time) BETWEEN '$start_date' AND '$end_date'";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return $row['total'] ?? 0;
}

function getSpotUtilization($conn)
{
    $sql = "SELECT 
            (COUNT(CASE WHEN end_time > NOW() THEN 1 END) * 100.0 / COUNT(*)) as utilization 
            FROM bookings";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    return round($row['utilization'] ?? 0);
}

$total_bookings = getTotalBookings($conn, $start_date, $end_date);
$total_revenue = getTotalRevenue($conn, $start_date, $end_date);
$active_users = getActiveUsers($conn, $start_date, $end_date);
$spot_utilization = getSpotUtilization($conn);
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reports & Analytics</title>
    <link rel='stylesheet' href='Styles.css'>
    <link rel='stylesheet' href='bootstrap.min.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>

    <style>
        .sidebar {
            height: 100vh;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
        }

        .profile-container {
            width: 100%;
            padding: 20px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-header h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .stats-card {
            padding: 25px;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .stats-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .stats-value {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .stats-label {
            font-size: 1rem;
            opacity: 0.8;
        }

        .table-section {
            background: #fff;
            border-radius: 10px;
            padding: 20px;
            margin-top: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }

        .table-section h4 {
            color: #333;
            font-size: 20px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }

        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: 600;
        }

        .date-filter-section {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class='bg-content'>
    <div class='sidebar'>
        <?php include './Includes/sidebar.php';
        ?>
    </div>
    <div class='main-content'>
        <div class='container'>
            <div class='content-wrapper'>
                <div class='container py-4'>
                    <div class='d-flex justify-content-between align-items-center mb-4'>
                        <h2>Reports & Analytics </h2>
                        <button class='btn btn-primary' onclick='window.print()'>
                            <i class='fas fa-print'></i> Print Report
                        </button>
                    </div>

                    <div class='row'>
                        <div class='col-md-3'>
                            <div class='card shadow-sm rounded-3 mb-4 transition-transform hover-lift'>
                                <div class='card-body text-center bg-primary text-white rounded-3'>
                                    <i class='fas fa-car fa-2x mb-4'></i>
                                    <div class='fs-2 fw-bold'>
                                        <?php echo $total_bookings;
                                        ?>
                                    </div>
                                    <div class='fs-6 text-white-50'>Total Bookings</div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='card shadow-sm rounded-3 mb-4 transition-transform hover-lift'>
                                <div class='card-body text-center bg-success text-white rounded-3'>
                                    <i class='fas fa-rupee-sign fa-2x mb-4'></i>
                                    <div class='fs-2 fw-bold'>Rs.
                                        <?php echo $total_revenue;
                                        ?>
                                    </div>
                                    <div class='fs-6 text-white-50'>Total Revenue</div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='card shadow-sm rounded-3 mb-4 transition-transform hover-lift'>
                                <div class='card-body text-center bg-info text-white rounded-3'>
                                    <i class='fas fa-users fa-2x mb-4'></i>
                                    <div class='fs-2 fw-bold'>
                                        <?php echo $active_users;
                                        ?>
                                    </div>
                                    <div class='fs-6 text-white-50'>Active Users</div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='card shadow-sm rounded-3 mb-4 transition-transform hover-lift'>
                                <div class='card-body text-center bg-warning text-white rounded-3'>
                                    <i class='fas fa-chart-pie fa-2x mb-4'></i>
                                    <div class='fs-2 fw-bold'>
                                        <?php echo $spot_utilization;
                                        ?>%
                                    </div>
                                    <div class='fs-6 text-white-50'>Spot Utilization</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class='card mt-4'>
                        <div class='card-header'>
                            <h5 class='mb-0'>Recent Bookings</h5>
                        </div>
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Booking ID</th>
                                            <th>User Name</th>
                                            <th>Vehicle Number</th>
                                            <th>Location</th>
                                            <th>Slot#</th>
                                            <th>Booking Time</th>
                                            <th>End Time</th>
                                            <th>Amount Paid</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1;
                                        $sql = "SELECT b.*, b.seat_number, s.location 
                                        FROM bookings b 
                                        LEFT JOIN slots s ON b.slot_id = s.id 
                                        WHERE DATE(b.booking_time) BETWEEN '$start_date' AND '$end_date' 
                                        ORDER BY b.booking_time DESC 
                                        LIMIT 10";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $current_time = strtotime('now');
                                            $end_time = strtotime($row['end_time']);

                                            if ($end_time < $current_time) {
                                                $status = 'Completed';
                                                $status_class = 'success';
                                            } else {
                                                $status = 'Active';
                                                $status_class = 'warning';
                                            }

                                            echo '<tr>';
                                            echo '<td>' . $i++ . '</td>';
                                            echo "<td>{$row['id']}</td>";
                                            echo "<td>{$row['user_name']}</td>";
                                            echo "<td>{$row['vehicle_no']}</td>";
                                            echo "<td>{$row['location']}</td>";
                                            echo "<td>{$row['seat_number']}</td>";
                                            echo '<td>' . date('Y-m-d h:i', strtotime($row['booking_time'])) . '</td>';
                                            echo '<td>' . date('Y-m-d h:i', strtotime($row['end_time'])) . '</td>';
                                            echo '<td>₹' . number_format($row['amount_paid'] / 100, 2) . '</td>';
                                            echo "<td><span class='badge bg-{$status_class}'>{$status}</span></td>";
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class='card mt-4'>
                        <div class='card-header'>
                            <h5 class='mb-0'>slots Statistics</h5>
                        </div>
                        <div class='card-body'>
                            <div class='table-responsive'>
                                <table class='table table-striped'>
                                    <thead>
                                        <tr>
                                            <th>No.</th>
                                            <th>Location</th>
                                            <th>Total Slots</th>
                                            <th>Available Slots</th>
                                            <th>Price</th>
                                            <th>Occupancy Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $i = 1;
                                        $sql = "SELECT s.location, 
                                                s.total_slots,
                                                s.available_slots,
                                                b.seat_number,
                                                s.price,
                                                ((s.total_slots - s.available_slots) * 100.0 / s.total_slots) as occupancy_rate
                                                FROM slots s
                                                LEFT JOIN bookings b ON s.id = b.slot_id 
                                                WHERE b.end_time > NOW() OR b.end_time IS NULL
                                                GROUP BY s.location, b.seat_number";
                                        $result = mysqli_query($conn, $sql);
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo '<tr>';
                                            echo '<td>' . $i++ . '</td>';
                                            echo "<td>{$row['location']}</td>";
                                            echo "<td>{$row['total_slots']}</td>";
                                            echo "<td>{$row['available_slots']}</td>";
                                            echo "<td>₹{$row['price']}</td>";
                                            echo '<td>' . round($row['occupancy_rate'], 1) . '%</td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src='https://code.jquery.com/jquery-3.2.1.slim.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js'></script>
    <script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js'></script>

</body>

</html>