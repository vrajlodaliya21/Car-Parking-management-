<?php
session_start();
require('fpdf.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include 'Connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: Index.php');
    exit();
}

function toCamelCase($string)
{
    return ucwords(strtolower($string));
}

$user_name = toCamelCase($_SESSION['user']);
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : "";

// Check for expired bookings
$stmt = $conn->prepare(
    "SELECT b.*, s.location 
     FROM bookings b 
     JOIN slots s ON b.slot_id = s.id 
     WHERE b.end_time < NOW()"
);
$stmt->execute();
$result = $stmt->get_result();
$expired_bookings = $result->fetch_all(MYSQLI_ASSOC);

foreach ($expired_bookings as $booking) {
    $stmt = $conn->prepare('SELECT U_Email FROM users WHERE U_Name = ?');
    $stmt->bind_param('s', $booking['user_name']);
    $stmt->execute();
    $user_result = $stmt->get_result();
    $user_info = $user_result->fetch_assoc();
    $user_email = $user_info['U_Email'];

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'parkheaven777@gmail.com';
        $mail->Password = 'ytxwhtesrejqqkcd';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('parkheaven777@gmail.com', 'Park Heaven');
        $mail->addAddress($user_email, $booking['user_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Parking Slot Booking Expired';

        $booking_end = date('d-m-Y h:i A', strtotime($booking['end_time']));
        $mail->Body = "
            <h2>Parking Slot Booking Expired</h2>
            <p>Dear {$booking['user_name']},</p>
            <p>Your parking slot booking has expired.</p>
            <p><strong>Booking Details:</strong></p>
            <ul>
                <li>Location: {$booking['location']}</li>
                <li>Seat Number: {$booking['seat_number']}</li>
                <li>Vehicle Number: {$booking['vehicle_no']}</li>
                <li>End Time: {$booking_end}</li>
            </ul>
            <p>If you need to continue parking, please make a new booking.</p>
            <p>Thank you for choosing Park Heaven!</p>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Failed to send expiration email: " . $e->getMessage());
    }

    // Update slots available count
    $stmt = $conn->prepare('UPDATE slots SET available_slots = available_slots + 1 WHERE id = ?');
    $stmt->bind_param('i', $booking['slot_id']);
    $stmt->execute();

    // Delete expired booking
    $stmt = $conn->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->bind_param('i', $booking['id']);
    $stmt->execute();
}

if (isset($_POST['payment_success'])) {
    $location_id = $_POST['location_id'];
    $seat_number = $_POST['seat_number'];
    $user_name_input = toCamelCase($_POST['user_name']);
    $vehicle_no = $_POST['vehicle_no'];
    $payment_id = $_POST['razorpay_payment_id'];
    $payment_amount = $_POST['payment_amount'];

    $booking_time = date('Y-m-d H:i:s');
    $end_time = date('Y-m-d H:i:s', strtotime('+2 hours'));

    if (!empty($user_name_input) && !empty($vehicle_no) && !empty($seat_number)) {
        $stmt = $conn->prepare(
            'SELECT id FROM bookings 
             WHERE slot_id = ? 
               AND seat_number = ? 
               AND end_time > NOW()'
        );
        $stmt->bind_param('ii', $location_id, $seat_number);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows === 0) {
            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("SELECT city, area, location FROM slots WHERE id = ?");
                $stmt->bind_param('i', $location_id);
                $stmt->execute();
                $location_result = $stmt->get_result();
                $location_info = $location_result->fetch_assoc();

                $stmt = $conn->prepare(
                    'UPDATE slots 
                     SET available_slots = available_slots - 1 
                     WHERE id = ? 
                       AND available_slots > 0'
                );
                $stmt->bind_param('i', $location_id);
                $stmt->execute();

                $stmt = $conn->prepare(
                    'INSERT INTO bookings 
                     (slot_id, seat_number, user_name, vehicle_no, payment_id, amount_paid, booking_time, end_time, city, area, location) 
                     VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 2 HOUR), ?, ?, ?)'
                );
                $stmt->bind_param(
                    'iisssisss',
                    $location_id,
                    $seat_number,
                    $user_name_input,
                    $vehicle_no,
                    $payment_id,
                    $payment_amount,
                    $location_info['city'],
                    $location_info['area'],
                    $location_info['location']
                );
                $stmt->execute();

                $current_time = date('d-m-Y H:i:s');
                $formatted_end_time = date('d-m-Y H:i:s', strtotime($end_time));
                $message = "Hello $user_name_input,\n\nYour Parking slot (slot #$seat_number) has been successfully booked.\n\n"
                    . "Details:\n"
                    . "- City: {$location_info['city']}\n"
                    . "- Area: {$location_info['area']}\n"
                    . "- Location: {$location_info['location']}\n"
                    . "- Slot Number: $seat_number\n"
                    . "- Vehicle Number: $vehicle_no\n"
                    . "- Booking Time: $current_time\n"
                    . "- End Time: $formatted_end_time\n"
                    . "- Payment ID: $payment_id\n"
                    . "- Amount Paid: ₹" . ($payment_amount / 100) . "\n\n"
                    . "Thank you for choosing our service.";

                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'parkheaven777@gmail.com';
                    $mail->Password = 'ytxwhtesrejqqkcd';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('parkheaven777@gmail.com', 'Parking Management');
                    $mail->addAddress($user_email, $user_name_input);

                    $mail->isHTML(false);
                    $mail->Subject = 'Parking Slot Booking Confirmation';
                    $mail->Body = $message;
                    $mail->send();
                } catch (Exception $e) {
                    $conn->rollback();
                    echo json_encode(['status' => 'error', 'message' => 'Email sending failed']);
                    exit();
                }
                $conn->commit();
                echo json_encode(['status' => 'success', 'message' => 'Booking successful']);
                exit();
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['status' => 'error', 'message' => 'Database operation failed']);
                exit();
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'That Slot# is already booked!']);
            exit();
        }
    }
}

if (isset($_POST['extend_booking'])) {
    $booking_id = $_POST['booking_id'];
    $hours = $_POST['extend_hours'];

    $stmt = $conn->prepare('SELECT b.*, s.location FROM bookings b JOIN slots s ON b.slot_id = s.id WHERE b.id = ?');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();

    if (!$booking) {
        $_SESSION['success'] = "Booking not found!";
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    $current_end_time = new DateTime($booking['end_time']);
    $current_end_time->modify("+{$hours} hours");
    $new_end_time = $current_end_time->format('Y-m-d H:i:s');

    $stmt = $conn->prepare('UPDATE bookings SET end_time = ? WHERE id = ?');
    $stmt->bind_param('si', $new_end_time, $booking_id);
    $stmt->execute();

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'parkheaven777@gmail.com';
        $mail->Password = 'ytxwhtesrejqqkcd';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('parkheaven777@gmail.com', 'Parking Management');
        $mail->addAddress($user_email, $booking['user_name']);

        $mail->isHTML(false);
        $mail->Subject = 'Parking Time Extended';

        $message = "Hello {$booking['user_name']},\n\n"
            . "Your Parking slot (seat #{$booking['seat_number']}) has been extended.\n\n"
            . "Details:\n"
            . "- Location: {$booking['location']}\n"
            . "- Seat Number: {$booking['seat_number']}\n"
            . "- Extended by: {$hours} hours\n"
            . "- New End Time: " . date('d-m-Y H:i:s', strtotime($new_end_time)) . "\n\n"
            . "Thank you for choosing our service.";

        $mail->Body = $message;
        $mail->send();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Email sending failed']);
        exit();
    }

    $_SESSION['success'] = "Booking has been extended by {$hours} hours.";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];

    $stmt = $conn->prepare('SELECT slot_id FROM bookings WHERE id = ?');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if (!$row) {
        $_SESSION['success'] = 'Booking not found!';
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    $location_id = $row['slot_id'];

    $stmt = $conn->prepare(
        'UPDATE slots SET available_slots = available_slots + 1 WHERE id = ?'
    );
    $stmt->bind_param('i', $location_id);
    $stmt->execute();

    $stmt = $conn->prepare('DELETE FROM bookings WHERE id = ?');
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();

    $_SESSION['success'] = 'Booking has been cancelled successfully.';
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

if (isset($_GET['download_pdf']) && $_GET['download_pdf'] == 'true' && isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];
    $stmt = $conn->prepare(
        'SELECT bookings.*, slots.location 
        FROM bookings 
        JOIN slots ON bookings.slot_id = slots.id 
        WHERE bookings.id = ?'
    );
    $stmt->bind_param('i', $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    if (!$booking) {
        die('Booking not found!');
    }

    $pdf = new FPDF();
    $pdf->AddPage();

    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(190, 10, 'Park Heaven', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(190, 8, 'Parking Management System', 0, 1, 'C');
    $pdf->Cell(190, 8, 'Golden Empire, Surat', 0, 1, 'C');
    $pdf->Cell(190, 8, 'Phone: 07226000522', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(190, 10, 'Invoice', 0, 1, 'C');
    $pdf->Ln(5);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Bill No:', 0, 0);
    $pdf->Cell(140, 10, $booking['id'], 0, 1);

    $pdf->Cell(50, 10, 'Date:', 0, 0);
    $pdf->Cell(140, 10, date('d/m/Y H:i:s'), 0, 1);

    $pdf->Cell(50, 10, 'User Name:', 0, 0);
    $pdf->Cell(140, 10, $booking['user_name'], 0, 1);

    $pdf->Cell(50, 10, 'Vehicle No:', 0, 0);
    $pdf->Cell(140, 10, $booking['vehicle_no'], 0, 1);

    $pdf->Cell(50, 10, 'City:', 0, 0);
    $pdf->Cell(140, 10, $booking['city'], 0, 1);

    $pdf->Cell(50, 10, 'Area:', 0, 0);
    $pdf->Cell(140, 10, $booking['area'], 0, 1);

    $pdf->Cell(50, 10, 'Location:', 0, 0);
    $pdf->Cell(140, 10, $booking['location'], 0, 1);

    $pdf->Cell(50, 10, 'Seat Number:', 0, 0);
    $pdf->Cell(140, 10, $booking['seat_number'], 0, 1);

    $pdf->Cell(50, 10, 'Booking Time:', 0, 0);
    $pdf->Cell(140, 10, date('d/m/Y H:i:s', strtotime($booking['booking_time'])), 0, 1);

    $pdf->Cell(50, 10, 'End Time:', 0, 0);
    $pdf->Cell(140, 10, date('d/m/Y H:i:s', strtotime($booking['end_time'])), 0, 1);

    $pdf->Ln(5);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(95, 10, 'Description', 1);
    $pdf->Cell(50, 10, 'Location', 1);
    $pdf->Cell(45, 10, 'Amount', 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(95, 10, 'Parking Slot (Seat #' . $booking['seat_number'] . ')', 1);
    $pdf->Cell(50, 10, $booking['location'], 1);
    $pdf->Cell(45, 10, 'Rs. ' . number_format($booking['amount_paid'] / 100, 2), 1);
    $pdf->Ln();

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(145, 10, 'Total:', 1, 0, 'L');
    $pdf->Cell(45, 10, 'Rs. ' . number_format($booking['amount_paid'] / 100, 2), 1);
    $pdf->Ln(10);

    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Payment ID:', 0, 0);
    $pdf->Cell(140, 10, $booking['payment_id'], 0, 1);

    $pdf->Cell(50, 10, 'Payment Method:', 0, 0);
    $pdf->Cell(140, 10, 'Razorpay', 0, 1);

    $pdf->Ln(10);
    $pdf->SetFont('Arial', 'I', 10);
    $pdf->Cell(190, 10, 'Thank you for Parking with us!', 0, 1, 'C');

    $pdf->Output('D', 'Parking_Receipt_' . $booking_id . '.pdf');
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'fetch_booked_seats') {
    header('Content-Type: application/json');
    $location_id = (int) $_POST['location_id'];

    try {
        $stmt = $conn->prepare("SELECT id, total_slots, price, available_slots FROM slots WHERE id = ?");
        $stmt->bind_param('i', $location_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $location = $result->fetch_assoc();

        if (!$location) {
            throw new Exception('Location not found');
        }

        $stmt = $conn->prepare("SELECT DISTINCT seat_number FROM bookings WHERE slot_id = ? AND end_time > NOW()");
        $stmt->bind_param('i', $location_id);
        $stmt->execute();
        $result = $stmt->get_result();

        $booked_seats = [];
        while ($row = $result->fetch_assoc()) {
            $booked_seats[] = (int) $row['seat_number'];
        }

        echo json_encode([
            'success' => true,
            'totalSlots' => (int) $location['total_slots'],
            'bookedSeats' => $booked_seats,
            'price' => $location['price'],
            'available_slots' => (int) $location['available_slots']
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit();
}

$sql = 'SELECT 
          bookings.id AS booking_id,
          bookings.seat_number,
          bookings.user_name,
          bookings.vehicle_no,
          bookings.booking_time,
          bookings.end_time,
          bookings.city,
          bookings.area,
          bookings.location,
          slots.id AS location_id
        FROM bookings
        JOIN slots ON bookings.slot_id = slots.id
        WHERE bookings.user_name = ?';
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $user_name);
$stmt->execute();
$result = $stmt->get_result();
$slots = $result->fetch_all(MYSQLI_ASSOC);

$available_locations = $conn->query(
    'SELECT id, city, area, location, total_slots, available_slots, price FROM slots'
)->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset="UTF-8">
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <title>Parking Slots</title>
    <link rel='stylesheet' href='./Styles.css'>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <link rel='stylesheet' href='https://pro.fontawesome.com/releases/v5.10.0/css/all.css' />
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>

    <style>
        body {
            padding-top: 85px;
            font-size: 20px;
        }

        .form-control {
            height: 45px;
            font-size: 1.1rem;
        }

        #btn {
            font-size: 20px;
            padding: 8px 12px;
            height: auto;
            border-radius: 8px;
        }

        .text-danger {
            color: red;
            font-size: 15px;
            margin-top: -5px;
            display: block;
        }

        .btn-cancel,
        .btn-download,
        .btn-extend {
            font-size: 16px;
            padding: 8px 12px;
            height: auto;
            border-radius: 8px;
            margin-right: 5px;
        }

        .seat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 15px;
            padding: 20px;
            justify-items: center;
        }

        .seat {
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #ffffff;
            border: 2px solid #28a745;
            color: #333;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .seat.booked {
            background-color: #dc3545 !important;
            border-color: #bd2130 !important;
            color: white !important;
            cursor: not-allowed !important;
            opacity: 0.8 !important;
            pointer-events: none !important;
        }

        .seat.selected {
            background-color: #28a745 !important;
            border-color: #1e7e34 !important;
            color: white !important;
        }

        .legend-box {
            width: 25px;
            height: 25px;
            border-radius: 4px;
            border: 2px solid;
        }

        .legend-available {
            background-color: #ffffff;
            border-color: #28a745;
        }

        .legend-selected {
            background-color: #28a745;
            border-color: #1e7e34;
        }

        .legend-booked {
            background-color: #dc3545;
            border-color: #bd2130;
        }

        .modal-xl {
            max-width: 90% !important;
        }

        .modal-body {
            padding: 2rem;
            min-height: 500px;
        }

        #seat-map-container {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .seat-legend {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            padding: 20px;
            border-radius: 8px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .alert-danger i {
            display: block;
            margin-bottom: 15px;
        }

        .location-group {
            margin-bottom: 20px;
        }

        .location-group .inline-form-group {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }

        .location-group .inline-form-group .form-group {
            flex: 1;
            margin-bottom: 0;
        }

        .location-group select {
            width: 100%;
        }

        .table {
            font-size: 17px;
            width: 100%;
            margin-bottom: 1rem;
            table-layout: auto;
        }

        .table th, .table td {
            vertical-align: middle !important;
            padding: 12px 8px;
            text-align: center;
            line-height: 1.4;
            font-size: 17px;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            font-size: 18px;
            color: #333;
        }

        .table td:nth-child(1) { width: 5%; } 
        .table td:nth-child(2) { width: 10%; } 
        .table td:nth-child(3) { width: 10%; }
        .table td:nth-child(4) { width: 12%; } 
        .table td:nth-child(5) { width: 8%; }  
        .table td:nth-child(6) { width: 12%; } 
        .table td:nth-child(7) { width: 12%; } 
        .table td:nth-child(8) { width: 12%; } 
        .table td:nth-child(9) { width: 8%; }  
        .table td:nth-child(10) { width: 11%; } 

        .btn-group-table {
            display: flex;
            gap: 5px;
            justify-content: center;
            align-items: center;
        }

        .btn-group-table .btn {
            font-size: 15px;
            padding: 6px 10px;
        }

        .table-responsive {
            overflow-x: visible;
        }

        @media screen and (max-width: 1400px) {
            .table {
                font-size: 15px;
            }
            .table th {
                font-size: 16px;
            }
            .btn-group-table .btn {
                font-size: 14px;
                padding: 5px 8px;
            }
        }
    </style>
</head>

<body class='bg-content'>

    <div class='sidebar'>
        <?php include './Includes/Unavbar.php'; ?>
    </div>

    <div class='main-content'>
        <div class='container mt-1 col-11'>
            <h1 class='text-center'>Parking Slots</h1>
            <hr class='w-25 mx-auto border-dark'>

            <button type="button" class="btn btn-primary my-4" data-toggle="modal" data-target="#bookSlotModal">
                + Book New Slot
            </button>

            <div class="modal fade" id="bookSlotModal" tabindex="-1" role="dialog" aria-labelledby="bookSlotModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-xl" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="bookSlotModalLabel">Book a New Parking Slot</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6" id="seat-map-container">
                                    <h5 class="text-center">Slot# Map</h5>
                                    <div id="seat-map" class="seat-grid">
                                    </div>
                                    <div class="seat-legend">
                                        <div class="legend-item">
                                            <div class="legend-box legend-available"></div>
                                            <span>Available</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-box legend-selected"></div>
                                            <span>Selected</span>
                                        </div>
                                        <div class="legend-item">
                                            <div class="legend-box legend-booked"></div>
                                            <span>Booked</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <form method="POST" id="bookingForm">
                                        <div class="location-group">
                                            <div class="inline-form-group">
                                                <div class="form-group">
                                                    <label for="city">Select City</label>
                                                    <select class="form-control" name="city" id="city" onchange="updateAreas()">
                                                        <option value="">Select City</option>
                                                        <?php
                                                        $cities = array_unique(array_column($available_locations, 'city'));
                                                        foreach ($cities as $city):
                                                            ?>
                                                            <option value="<?= htmlspecialchars($city) ?>">
                                                                <?= htmlspecialchars($city) ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <span id="city_error" class="text-danger mt-1" style="display:none;">
                                                        Please select a city.
                                                    </span>
                                                </div>

                                                <div class="form-group">
                                                    <label for="area">Select Area</label>
                                                    <select class="form-control" name="area" id="area" onchange="updateLocations()" disabled>
                                                        <option value="">Select Area</option>
                                                    </select>
                                                    <span id="area_error" class="text-danger mt-1" style="display:none;">
                                                        Please select an area.
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <label for="location_id">Select Location</label>
                                                <select class="form-control" name="location_id" id="location_id"
                                                    onchange="fetchSeatMap()" disabled>
                                                    <option value="">Select Location</option>
                                                </select>
                                                <span id="location_error" class="text-danger mt-1" style="display:none;">
                                                    Please select a location.
                                                </span>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="user_name">Your Name</label>
                                            <input type="text" class="form-control" id="user_name" name="user_name"
                                                value="<?= htmlspecialchars($user_name) ?>" readonly>
                                        </div>

                                        <div class="form-group">
                                            <label for="vehicle_no">Vehicle Number</label>
                                            <input type="text" class="form-control" id="vehicle_no" name="vehicle_no"
                                                placeholder="Format: AB12CD1234">
                                            <span id="vehicle_no_error" class="text-danger mt-1"
                                                style="display:none;"></span>
                                        </div>

                                        <div class="form-group">
                                            <label>Selected Slot#:</label>
                                            <input type="text" class="form-control" id="selected_seat"
                                                name="seat_number" readonly>
                                            <span id="seat_error" class="text-danger mt-1" style="display:none;">
                                                Please select a Slot# from the left map.
                                            </span>
                                        </div>

                                        <div class="form-group">
                                            <strong>Price: ₹<span id="slot_price">--</span></strong>
                                            <p><small>(Booking duration: 2 hours)</small></p>
                                        </div>
                                        <div class="mt-3">
                                            <button type="button" id="payButton" class="btn btn-primary">Pay & Book
                                                Slot</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="extendTimeModal" tabindex="-1" role="dialog"
                aria-labelledby="extendTimeModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="extendTimeModalLabel">Extend Parking Time</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="POST" id="extendForm">
                            <div class="modal-body">
                                <input type="hidden" name="booking_id" id="extend_booking_id">
                                <div class="form-group">
                                    <label for="extend_hours">Select Additional Hours</label>
                                    <select class="form-control" name="extend_hours" id="extend_hours">
                                        <option value="1">1 Hour</option>
                                        <option value="2">2 Hours</option>
                                        <option value="3">3 Hours</option>
                                        <option value="4">4 Hours</option>
                                    </select>
                                </div>
                                <div id="extend_details"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                <button type="submit" name="extend_booking" class="btn btn-primary">Extend Time</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div id='success-message' class='alert alert-success'>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                </div>
                <script>
                    setTimeout(function () {
                        document.getElementById('success-message').style.display = 'none';
                    }, 2000);
                </script>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>

            <div class='table-responsive'>
                <?php if (empty($slots)): ?>
                    <div class="text-center">
                        <img src="../Car/Images/Empty.gif" width="250" height="250" alt="No slots available" />
                        <p>No booking slots available.</p>
                    </div>
                <?php else: ?>
                    <table class='table table-striped'>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>City</th>
                                <th>Area</th>
                                <th>Location</th>
                                <th>Slot #</th>
                                <th>User Name</th>
                                <th>Vehicle Number</th>
                                <th>Booking Time</th>
                                <th>End Time</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 1;
                            foreach ($slots as $slot): ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td><?= htmlspecialchars($slot['city']) ?></td>
                                    <td><?= htmlspecialchars($slot['area']) ?></td>
                                    <td><?= htmlspecialchars($slot['location']) ?></td>
                                    <td><?= htmlspecialchars($slot['seat_number']) ?></td>
                                    <td><?= htmlspecialchars($slot['user_name']) ?></td>
                                    <td><?= htmlspecialchars($slot['vehicle_no']) ?></td>
                                    <td style="min-width: 200px;"><?= date('d-m-Y h:i A', strtotime($slot['booking_time'])) ?></td>
                                    <td><?= date('h:i A', strtotime($slot['end_time'])) ?></td>
                                    <td>
                                        <div class="btn-group-table">
                                            <button type="button" class="btn btn-secondary btn-extend" onclick="prepareExtend(
                                              <?= $slot['booking_id'] ?>,
                                              '<?= htmlspecialchars($slot['vehicle_no']) ?>',
                                              '<?= htmlspecialchars($slot['end_time']) ?>'
                                            )">
                                                Extend Time
                                            </button>

                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="booking_id" value="<?= $slot['booking_id'] ?>">
                                                <button type="submit" name="cancel_booking" class="btn btn-danger btn-cancel">
                                                   <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>

                                            <a href="UserBookSlot.php?download_pdf=true&booking_id=<?= $slot['booking_id'] ?>"
                                                class="btn btn-info btn-download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function fetchSeatMap() {
            const locationId = document.getElementById("location_id").value;
            const seatMap = document.getElementById("seat-map");
            const priceSpan = document.getElementById("slot_price");
            const payButton = document.getElementById("payButton");

            if (!locationId) {
                seatMap.innerHTML = "";
                priceSpan.textContent = "--";
                return;
            }

            seatMap.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading seats...</div>';

            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: {
                    action: 'fetch_booked_seats',
                    location_id: locationId
                },
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        const isUnavailable = response.available_slots <= 0;
                        buildSeatMap(response.totalSlots, response.bookedSeats, isUnavailable);
                        priceSpan.textContent = response.price || '--';
                        payButton.disabled = isUnavailable;
                    } else {
                        seatMap.innerHTML = `<div class="alert alert-danger">${response.error || 'Failed to load seats'}</div>`;
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', { xhr, status, error });
                    seatMap.innerHTML = '<div class="alert alert-danger">Failed to connect to server. Please try again.</div>';
                }
            });
        }

        function buildSeatMap(totalSlots, bookedSeats, isUnavailable) {
            const seatMap = document.getElementById("seat-map");
            seatMap.innerHTML = "";
            seatMap.className = "seat-grid";

            for (let i = 1; i <= totalSlots; i++) {
                const seat = document.createElement("div");
                const isBooked = bookedSeats.includes(i);

                seat.className = "seat";
                seat.textContent = i;

                if (isBooked) {
                    seat.classList.add("booked");
                    seat.title = "Already Booked";
                    seat.style.pointerEvents = "none";
                } else {
                    seat.onclick = () => selectSeat(seat, i);
                    seat.title = "Click to select";
                }

                seatMap.appendChild(seat);
            }
        }

        function selectSeat(seat, num) {
            if (seat.classList.contains("booked")) return;
            document.querySelectorAll('.seat.selected').forEach(s => s.classList.remove('selected'));
            seat.classList.add('selected');
            document.getElementById("selected_seat").value = num;
        }
    </script>

    <script>
        document.getElementById('payButton').addEventListener('click', function (e) {
            e.preventDefault();

            // Reset error messages
            document.querySelectorAll('.text-danger').forEach(el => el.style.display = 'none');

            // Get form values
            const citySelect = document.getElementById("city");
            const areaSelect = document.getElementById("area");
            const locationId = document.getElementById("location_id").value;
            const seatNumber = document.getElementById("selected_seat").value;
            const vehicleNo = document.getElementById("vehicle_no").value.trim();
            let hasError = false;

            // Validate city
            if (!citySelect.value) {
                document.getElementById("city_error").style.display = "block";
                hasError = true;
            }

            // Validate area
            if (!areaSelect.value) {
                document.getElementById("area_error").style.display = "block";
                hasError = true;
            }

            // Validate location
            if (!locationId) {
                document.getElementById("location_error").style.display = "block";
                hasError = true;
            }

            // Validate seat selection
            if (!seatNumber) {
                document.getElementById("seat_error").style.display = "block";
                hasError = true;
            }

            // Validate vehicle number
            if (!vehicleNo) {
                document.getElementById("vehicle_no_error").textContent = "Please enter your vehicle number.";
                document.getElementById("vehicle_no_error").style.display = "block";
                hasError = true;
            } else {
                const vehiclePattern = /^[A-Z]{2}[0-9]{2}[A-Z]{2}[0-9]{4}$/i;
                if (!vehiclePattern.test(vehicleNo)) {
                    document.getElementById("vehicle_no_error").textContent = "Please enter a valid vehicle number (Format: AB12CD1234)";
                    document.getElementById("vehicle_no_error").style.display = "block";
                    hasError = true;
                }
            }

            if (hasError) {
                return;
            }

            // Get price and create Razorpay options
            let locationSelect = document.getElementById("location_id");
            let selectedOption = locationSelect.options[locationSelect.selectedIndex];
            let price = selectedOption.getAttribute("data-price");
            let amountInPaise = price * 100;

            let options = {
                key: "rzp_test_RrPdWaQRNrYGaT",
                amount: amountInPaise,
                currency: "INR",
                name: "Park Heaven",
                description: "Parking Slot Booking",
                image: "your-logo-url.png",
                handler: function (response) {
                    console.log("Payment successful, processing booking...");
                    let formData = new FormData(document.getElementById('bookingForm'));
                    formData.append('payment_success', 'true');
                    formData.append('razorpay_payment_id', response.razorpay_payment_id);
                    formData.append('payment_amount', amountInPaise);

                    // Debug logging
                    for (let pair of formData.entries()) {
                        console.log(pair[0] + ': ' + pair[1]);
                    }

                    fetch('UserBookSlot.php', {
                        method: 'POST',
                        body: formData
                    })
                        .then(response => {
                            if (!response.ok) {
                                throw new Error('Network response was not ok');
                            }
                            return response.json();
                        })
                        .then(data => {
                            console.log("Server response:", data);
                            if (data.status === 'success') {
                                const successMessage = document.createElement('div');
                                successMessage.className = 'alert alert-success alert-dismissible fade show';
                                successMessage.innerHTML = `
                                <strong>Success!</strong> Your slot has been booked successfully.
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            `;
                                document.querySelector('.container').insertBefore(successMessage, document.querySelector('.container').firstChild);

                                // Close the modal
                                $('#bookSlotModal').modal('hide');

                                // Scroll to top to show the message
                                window.scrollTo({ top: 0, behavior: 'smooth' });

                                // Remove the message and reload after 2 seconds
                                setTimeout(() => {
                                    successMessage.classList.remove('show');
                                    setTimeout(() => {
                                        location.reload();
                                    }, 500);
                                }, 2000);
                            } else {
                                const errorMessage = document.createElement('div');
                                errorMessage.className = 'alert alert-danger alert-dismissible fade show';
                                errorMessage.innerHTML = `
                                <strong>Error!</strong> ${data.message || 'Unknown error occurred.'}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            `;
                                document.querySelector('.container').insertBefore(errorMessage, document.querySelector('.container').firstChild);

                                // Remove the error message after 3 seconds
                                setTimeout(() => {
                                    errorMessage.classList.remove('show');
                                    setTimeout(() => {
                                        errorMessage.remove();
                                    }, 500);
                                }, 3000);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred during booking. Please try again.');
                        });
                },
                prefill: {
                    name: document.getElementById('user_name').value,
                    email: '<?= $user_email ?>'
                },
                theme: {
                    color: "#3399cc"
                }
            };

            let rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response) {
                console.error('Payment failed:', response.error);
                alert('Payment failed. Please try again.');
            });
            rzp1.open();
        });

        function prepareExtend(bookingId, vehicleNo, endTime) {
            document.getElementById('extend_booking_id').value = bookingId;
            document.getElementById('extend_details').innerHTML = `
        <p><strong>Vehicle Number:</strong> ${vehicleNo}</p>
        <p><strong>Current End Time:</strong> ${formatDateTime(endTime)}</p>
    `;
            $('#extendTimeModal').modal('show');
        }

        function formatDateTime(dateTimeStr) {
            let date = new Date(dateTimeStr);
            return date.toLocaleString('en-IN', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).replace(',', '');
        }

        function updateAreas() {
            const citySelect = document.getElementById('city');
            const areaSelect = document.getElementById('area');
            const locationSelect = document.getElementById('location_id');
            const selectedCity = citySelect.value;

            // Hide city error if a city is selected
            document.getElementById("city_error").style.display = selectedCity ? "none" : "block";

            // Reset dependent dropdowns
            areaSelect.innerHTML = '<option value="">Select Area</option>';
            locationSelect.innerHTML = '<option value="">Select Location</option>';
            areaSelect.disabled = true;
            locationSelect.disabled = true;

            // Reset error messages for dependent fields
            document.getElementById("area_error").style.display = "none";
            document.getElementById("location_error").style.display = "none";

            if (selectedCity) {
                // Get unique areas for selected city
                const areas = [...new Set(<?= json_encode($available_locations) ?>.filter(
                    loc => loc.city === selectedCity
                ).map(loc => loc.area))];

                // Populate areas dropdown
                areas.forEach(area => {
                    const option = document.createElement('option');
                    option.value = area;
                    option.textContent = area;
                    areaSelect.appendChild(option);
                });

                areaSelect.disabled = false;
            }
        }

        function updateLocations() {
            const citySelect = document.getElementById('city');
            const areaSelect = document.getElementById('area');
            const locationSelect = document.getElementById('location_id');
            const selectedCity = citySelect.value;
            const selectedArea = areaSelect.value;

            // Hide area error if an area is selected
            document.getElementById("area_error").style.display = selectedArea ? "none" : "block";

            // Reset location dropdown
            locationSelect.innerHTML = '<option value="">Select Location</option>';
            locationSelect.disabled = true;

            // Reset location error message
            document.getElementById("location_error").style.display = "none";

            if (selectedCity && selectedArea) {
                // Get locations for selected city and area
                const locations = <?= json_encode($available_locations) ?>.filter(
                    loc => loc.city === selectedCity && loc.area === selectedArea
                );

                // Populate locations dropdown
                locations.forEach(loc => {
                    const option = document.createElement('option');
                    option.value = loc.id;
                    option.setAttribute('data-price', loc.price);
                    option.setAttribute('data-available', loc.available_slots);
                    option.textContent = loc.location;
                    locationSelect.appendChild(option);
                });

                locationSelect.disabled = false;
            }
        }
    </script>
</body>

</html>