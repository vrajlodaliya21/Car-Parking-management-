<?php
session_start();
if (!isset($_SESSION['vendor'])) {
    header('Location: Login.php');
    exit();
}

include '../Connection.php';

$vendor_id = $_SESSION['vendor_id'];

$sql_vendor = "SELECT * FROM vendors WHERE id = ?";
$stmt_vendor = mysqli_prepare($conn, $sql_vendor);
mysqli_stmt_bind_param($stmt_vendor, 'i', $vendor_id);
mysqli_stmt_execute($stmt_vendor);
$vendor = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_vendor));

$sql_revenue = "SELECT SUM(amount_paid) AS total_revenue FROM bookings WHERE city = ? AND area = ? AND location = ?";
$stmt_revenue = mysqli_prepare($conn, $sql_revenue);
mysqli_stmt_bind_param($stmt_revenue, 'sss', $vendor['city'], $vendor['area'], $vendor['location']);
mysqli_stmt_execute($stmt_revenue);
$revenue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_revenue))['total_revenue'] ?? 0;

// Get today's revenue
$today = date('Y-m-d');
$sql_today_revenue = "SELECT SUM(amount_paid) AS today_revenue FROM bookings WHERE city = ? AND area = ? AND location = ? AND DATE(booking_time) = ?";
$stmt_today_revenue = mysqli_prepare($conn, $sql_today_revenue);
mysqli_stmt_bind_param($stmt_today_revenue, 'ssss', $vendor['city'], $vendor['area'], $vendor['location'], $today);
mysqli_stmt_execute($stmt_today_revenue);
$today_revenue = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_today_revenue))['today_revenue'] ?? 0;

// Get total bookings count
$sql_total_bookings = "SELECT COUNT(*) AS total_bookings FROM bookings WHERE city = ? AND area = ? AND location = ?";
$stmt_total_bookings = mysqli_prepare($conn, $sql_total_bookings);
mysqli_stmt_bind_param($stmt_total_bookings, 'sss', $vendor['city'], $vendor['area'], $vendor['location']);
mysqli_stmt_execute($stmt_total_bookings);
$total_bookings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_total_bookings))['total_bookings'] ?? 0;

// Get today's bookings count
$sql_today_bookings = "SELECT COUNT(*) AS today_bookings FROM bookings WHERE city = ? AND area = ? AND location = ? AND DATE(booking_time) = ?";
$stmt_today_bookings = mysqli_prepare($conn, $sql_today_bookings);
mysqli_stmt_bind_param($stmt_today_bookings, 'ssss', $vendor['city'], $vendor['area'], $vendor['location'], $today);
mysqli_stmt_execute($stmt_today_bookings);
$today_bookings = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_today_bookings))['today_bookings'] ?? 0;

$sql_slots = "SELECT total_slots, available_slots FROM slots WHERE city = ? AND area = ? AND location = ?";
$stmt_slots = mysqli_prepare($conn, $sql_slots);
mysqli_stmt_bind_param($stmt_slots, 'sss', $vendor['city'], $vendor['area'], $vendor['location']);
mysqli_stmt_execute($stmt_slots);
$slots = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_slots));

// Calculate occupancy rate
$total_slots = $slots['total_slots'] ?? 0;
$available_slots = $slots['available_slots'] ?? 0;
$occupied_slots = $total_slots - $available_slots;
$occupancy_rate = ($total_slots > 0) ? round(($occupied_slots / $total_slots) * 100) : 0;

$sql_bookings = "SELECT seat_number, user_name, booking_time, vehicle_no, amount_paid FROM bookings WHERE city = ? AND area = ? AND location = ? ORDER BY booking_time DESC LIMIT 5";
$stmt_bookings = mysqli_prepare($conn, $sql_bookings);
mysqli_stmt_bind_param($stmt_bookings, 'sss', $vendor['city'], $vendor['area'], $vendor['location']);
mysqli_stmt_execute($stmt_bookings);
$result_bookings = mysqli_stmt_get_result($stmt_bookings);
$bookings = mysqli_fetch_all($result_bookings, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../../Car/Images/Main_Image.png">
    <title>Vendor Dashboard</title>
    <link rel="stylesheet" href="../bootstrap.min.css">
    <link rel="stylesheet" href="../Styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <style>
        .dashboard-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            height: 100%;
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 15px;
        }

        .revenue-icon { color: #28a745; }
        .slots-icon { color: #007bff; }
        .bookings-icon { color: #6f42c1; }
        .occupancy-icon { color: #fd7e14; }
        .today-icon { color: #17a2b8; }
        .alert-icon { color: #dc3545; }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-label {
            color: #6c757d;
            font-size: 1rem;
            margin-top: 5px;
        }

        .welcome-section {
            background: linear-gradient(135deg, #3a7bd5, #00d2ff);
            color: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .small-text {
            font-size: 0.8rem;
            opacity: 0.8;
        }

        .progress {
            height: 10px;
            margin-top: 10px;
        }

        @media (max-width: 768px) {
            .stat-value {
                font-size: 1.5rem;
            }

            .stat-icon {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="sidebar">
        <?php include '../Includes/Vsidebar.php'; ?>
    </div>

    <main class="container d-flex">
        <div class="container-fluid main-content px-4 py-4">
            <div class="welcome-section">
                <h1><i class="fas fa-tachometer-alt mr-2"></i> Welcome, <?php echo htmlspecialchars($vendor['name']); ?>!</h1>
                <p class="mb-0">Dashboard overview for your parking facility at <?php echo htmlspecialchars($vendor['location'] . ', ' . $vendor['area'] . ', ' . $vendor['city']); ?></p>
            </div>

            <div class="row">
                <div class="col-md-4 col-sm-6">
                    <div class="dashboard-card text-center">
                        <i class="fas fa-wallet stat-icon revenue-icon"></i>
                        <div class="stat-value">₹<?php echo number_format($revenue/100, 2); ?></div>
                        <div class="stat-label">Total Revenue</div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6">
                    <div class="dashboard-card text-center">
                        <i class="fas fa-hand-holding-usd stat-icon today-icon"></i>
                        <div class="stat-value">₹<?php echo number_format($today_revenue/100, 2); ?></div>
                        <div class="stat-label">Today's Revenue</div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6">
                    <div class="dashboard-card text-center">
                        <i class="fas fa-parking stat-icon slots-icon"></i>
                        <div class="stat-value"><?php echo $slots['available_slots'] ?? 0; ?>/<?php echo $slots['total_slots'] ?? 0; ?></div>
                        <div class="stat-label">Available Slots</div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 mt-3">
                    <div class="dashboard-card text-center">
                        <i class="fas fa-ticket-alt stat-icon bookings-icon"></i>
                        <div class="stat-value"><?php echo $total_bookings; ?></div>
                        <div class="stat-label">Total Bookings</div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 mt-3">
                    <div class="dashboard-card text-center">
                        <i class="fas fa-calendar-day stat-icon today-icon"></i>
                        <div class="stat-value"><?php echo $today_bookings; ?></div>
                        <div class="stat-label">Today's Bookings</div>
                    </div>
                </div>
                
                <div class="col-md-4 col-sm-6 mt-3">
                    <div class="dashboard-card text-center">
                        <i class="fas fa-chart-pie stat-icon occupancy-icon"></i>
                        <div class="stat-value"><?php echo $occupancy_rate; ?>%</div>
                        <div class="stat-label">Occupancy Rate</div>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $occupancy_rate; ?>%" aria-valuenow="<?php echo $occupancy_rate; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php if (count($bookings) > 0): ?>
            <div class="row mt-4">
                <div class="col-12">
                    <div class="dashboard-card">
                        <h4><i class="fas fa-history mr-2"></i> Recent Bookings</h4>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Slot No.</th>
                                        <th>Customer</th>
                                        <th>Vehicle No.</th>
                                        <th>Booking Time</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($booking['seat_number']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['vehicle_no']); ?></td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($booking['booking_time'])); ?></td>
                                        <td>₹<?php echo number_format($booking['amount_paid']/100, 2); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>