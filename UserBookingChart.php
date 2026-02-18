<?php
session_start();
require('Connection.php');

if (!isset($_SESSION['user'])) {
    header('Location: Index.php');
    exit();
}

$user_name = $_SESSION['user'];

$sql = "SELECT 
            b.booking_time,
            b.amount_paid,
            s.location,
            DATE_FORMAT(b.booking_time, '%Y-%m') as month
        FROM bookings b
        JOIN slots s ON b.slot_id = s.id
        WHERE b.user_name = ?
        ORDER BY b.booking_time DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_name);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);

$monthlyBookings = [];
$locationStats = [];
$totalSpent = 0;

foreach ($bookings as $booking) {
    $month = $booking['month'];
    if (!isset($monthlyBookings[$month])) {
        $monthlyBookings[$month] = 0;
    }
    $monthlyBookings[$month]++;

    $location = $booking['location'];
    if (!isset($locationStats[$location])) {
        $locationStats[$location] = 0;
    }
    $locationStats[$location]++;

    $totalSpent += $booking['amount_paid'] / 100; 
}

ksort($monthlyBookings);

$monthLabels = json_encode(array_keys($monthlyBookings));
$monthData = json_encode(array_values($monthlyBookings));
$locationLabels = json_encode(array_keys($locationStats));
$locationData = json_encode(array_values($locationStats));
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Booking Statistics</title>
    <link rel='stylesheet' href='./Styles.css'>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding-top: 85px;
        }

        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stats-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stats-card h3 {
            color: #333;
            margin-bottom: 10px;
        }

        .stats-card .number {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
        }
    </style>
</head>

<body class='bg-content'>
    <div class='sidebar'>
        <?php include './Includes/Unavbar.php'; ?>
    </div>

    <div class='main-content'>
        <div class='container mt-4'>
            <h1 class='text-center mb-4'>Your Booking Statistics</h1>

            <div class='row mb-4'>
                <div class='col-md-4'>
                    <div class='stats-card'>
                        <h3>Total Bookings</h3>
                        <div class='number'><?php echo count($bookings); ?></div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='stats-card'>
                        <h3>Total Amount Spent</h3>
                        <div class='number'>₹<?php echo number_format($totalSpent, 2); ?></div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='stats-card'>
                        <h3>Unique Locations</h3>
                        <div class='number'><?php echo count($locationStats); ?></div>
                    </div>
                </div>
            </div>

            <div class='row'>
                <div class='col-md-6'>
                    <div class='chart-container'>
                        <canvas id='monthlyBookingsChart'></canvas>
                    </div>
                </div>

                <div class='col-md-6'>
                    <div class='chart-container'>
                        <canvas id='locationChart'></canvas>
                    </div>
                </div>
            </div>

            <?php if (empty($bookings)): ?>
                <div class='text-center mt-5'>
                    <img src='../Car/Images/Empty.gif' width='250' height='250' alt='No bookings' />
                    <p class='mt-3'>No booking history available.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js'></script>
    <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>

    <script>
        new Chart(document.getElementById('monthlyBookingsChart'), {
            type: 'line',
            data: {
                labels: <?php echo $monthLabels; ?>,
                datasets: [{
                    label: 'Monthly Bookings',
                    data: <?php echo $monthData; ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    borderWidth: 2,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Monthly Booking Trends'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        new Chart(document.getElementById('locationChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo $locationLabels; ?>,
                datasets: [{
                    data: <?php echo $locationData; ?>,
                    backgroundColor: [
                        '#007bff',
                        '#28a745',
                        '#dc3545',
                        '#ffc107',
                        '#17a2b8'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Bookings by Location'
                    }
                }
            }
        });
    </script>
</body>

</html> 