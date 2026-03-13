<?php
session_start(); require("fpdf.php");
use PHPMailer\PHPMailer\{PHPMailer, Exception};
require "PHPMailer/src/Exception.php"; require "PHPMailer/src/PHPMailer.php"; require "PHPMailer/src/SMTP.php";
include "Connection.php";
if (!isset($_SESSION["user"])) { header("Location: Index.php"); exit(); }
function toCamelCase($s) { return ucwords(strtolower($s)); }
$user_name = toCamelCase($_SESSION["user"]); $user_email = $_SESSION["user_email"] ?? "";

function generateReceiptContent($bid, $conn) {
    $stmt = $conn->prepare("SELECT b.*, s.location FROM bookings b JOIN slots s ON b.slot_id = s.id WHERE b.id = ?");
    $stmt->bind_param("i", $bid); $stmt->execute(); $b = $stmt->get_result()->fetch_assoc();
    if (!$b) return null;
    $pdf = new FPDF(); $pdf->AddPage();
    
    // Header
    $pdf->SetFont("Arial","B",20); $pdf->SetTextColor(37,99,235); $pdf->Cell(190,10,"PARK HEAVEN",0,1,"C");
    $pdf->SetFont("Arial","",10); $pdf->SetTextColor(100,116,139); $pdf->Cell(190,5,"Golden Empire, Surat, Gujarat",0,1,"C");
    $pdf->Cell(190,5,"Contact: +91 82009 54589 | Email: parkheaven777@gmail.com",0,1,"C");
    $pdf->Ln(5);
    $pdf->Line(10, 32, 200, 32);
    $pdf->Ln(10);

    // Invoice Title
    $pdf->SetFont("Arial","B",14); $pdf->SetTextColor(30,41,59); $pdf->Cell(190,10,"TAX INVOICE / RECEIPT",0,1,"L");
    $pdf->Ln(2);

    // Details Grid
    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Booking ID:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,$b["id"],0,0);
    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Payment ID:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,$b["payment_id"],0,1);

    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Customer Name:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,$b["user_name"],0,0);
    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Vehicle No:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,$b["vehicle_no"],0,1);

    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Location:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,$b["location"],0,0);
    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Slot Number:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,"#".$b["seat_number"],0,1);

    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"Start Time:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,date("d-m-Y h:i A", strtotime($b["booking_time"])),0,0);
    $pdf->SetFont("Arial","B",10); $pdf->Cell(40,8,"End Time:",0,0); 
    $pdf->SetFont("Arial","",10); $pdf->Cell(55,8,date("d-m-Y h:i A", strtotime($b["end_time"])),0,1);

    $pdf->Ln(10);

    // Table Header
    $pdf->SetFillColor(241,245,249); $pdf->SetFont("Arial","B",10);
    $pdf->Cell(100,10,"Description",1,0,"L",true); 
    $pdf->Cell(45,10,"Date",1,0,"C",true); 
    $pdf->Cell(45,10,"Amount (INR)",1,1,"R",true);

    // Amount Calculations
    $total = $b["amount_paid"]; 
    $base = $total / 1.18; 
    $gst_each = ($total - $base) / 2;

    $pdf->SetFont("Arial","",10);
    $pdf->Cell(100,10,"Parking Slot Reservation Fee",1,0,"L");
    $pdf->Cell(45,10,date("d-m-Y",strtotime($b["booking_time"])),1,0,"C");
    $pdf->Cell(45,10,number_format($base, 2),1,1,"R");

    // Tax Rows
    $pdf->Cell(145,8,"CGST (9%)",1,0,"R"); $pdf->Cell(45,8,number_format($gst_each, 2),1,1,"R");
    $pdf->Cell(145,8,"SGST (9%)",1,0,"R"); $pdf->Cell(45,8,number_format($gst_each, 2),1,1,"R");

    // Total Row
    $pdf->SetFont("Arial","B",11); $pdf->SetFillColor(239,246,255);
    $pdf->Cell(145,10,"GRAND TOTAL (Incl. Taxes)",1,0,"R",true); 
    $pdf->Cell(45,10,"Rs. ".number_format($total, 2),1,1,"R",true);

    $pdf->Ln(15);
    $pdf->SetFont("Arial","I",9); $pdf->SetTextColor(100,116,139);
    $pdf->Cell(190,5,"* This is a computer generated receipt and does not require a physical signature.",0,1,"C");
    $pdf->Cell(190,5,"* Thank you for choosing Park Heaven for your parking needs.",0,1,"C");

    return $pdf->Output("S");
}

$stmt = $conn->prepare("SELECT b.*, s.location, u.U_Email FROM bookings b JOIN slots s ON b.slot_id = s.id JOIN users u ON b.user_name = u.U_Name WHERE b.end_time BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 35 MINUTE) AND b.reminder_sent = 0");
$stmt->execute(); $reminders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach ($reminders as $r) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP(); $mail->Host = "smtp.gmail.com"; $mail->SMTPAuth = true; $mail->Username = "parkheaven777@gmail.com"; $mail->Password = "ytxwhtesrejqqkcd"; $mail->SMTPSecure = "tls"; $mail->Port = 587;
        $mail->setFrom("parkheaven777@gmail.com","Park Heaven"); $mail->addAddress($r["U_Email"],$r["user_name"]);
        $mail->isHTML(true); $mail->Subject = "Reminder: Session ends soon";
        $mail->Body = "<h2>Reminder</h2><p>Your session ends soon. Extend at: http://localhost/car/UserBookSlot.php</p>"; $mail->send();
        $conn->query("UPDATE bookings SET reminder_sent = 1 WHERE id = " . $r["id"]);
    } catch (Exception $e) {}
}

if (isset($_POST["extend_payment_success"])) {
    $bid = $_POST["booking_id"]; $hrs = (int)$_POST["extend_hours"];
    $stmt = $conn->prepare("SELECT b.*, s.price, u.U_Email FROM bookings b JOIN slots s ON b.slot_id = s.id JOIN users u ON b.user_name = u.U_Name WHERE b.id = ?");
    $stmt->bind_param("i",$bid); $stmt->execute(); $b = $stmt->get_result()->fetch_assoc();
    if($b) {
        $base_amt = $b["price"] * $hrs; $total_amt = $base_amt * 1.18;
        $new_end_time = date('Y-m-d H:i:s', strtotime($b["end_time"] . " + $hrs hours"));
        $stmt = $conn->prepare("UPDATE bookings SET end_time = ?, reminder_sent = 0, amount_paid = amount_paid + ? WHERE id = ?");
        $stmt->bind_param("sdi",$new_end_time,$total_amt,$bid);
        if($stmt->execute()) {
            $pdf = generateReceiptContent($bid, $conn);
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP(); $mail->Host = "smtp.gmail.com"; $mail->SMTPAuth = true; $mail->Username = "parkheaven777@gmail.com"; $mail->Password = "ytxwhtesrejqqkcd"; $mail->SMTPSecure = "tls"; $mail->Port = 587;
                $mail->setFrom("parkheaven777@gmail.com","Park Heaven"); $mail->addAddress($b["U_Email"],$b["user_name"]);
                $mail->isHTML(true); $mail->Subject = "Parking Extension Confirmed - Park Heaven";
                $mail->addStringAttachment($pdf, "Updated_Receipt.pdf");
                
                $start_fmt = date("d-m-Y h:i A", strtotime($b["booking_time"]));
                $end_fmt = date("d-m-Y h:i A", strtotime($new_end_time));
                $loc_full = $b['location'].", ".$b['area'].", ".$b['city'];
                
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h2 style='color: #2563eb;'>Parking Extension Confirmed</h2>
                    <p>Dear <strong>{$b['user_name']}</strong>,</p>
                    <p>Thank you for choosing Park Heaven. Your parking reservation has been <span style='color: #059669; font-weight: bold;'>successfully extended</span>.</p>
                    
                    <p><strong>Location:</strong> <span style='color: #2563eb;'>{$loc_full}</span></p>
                    <p><strong>Slot Number:</strong> <span style='color: #f59e0b; font-weight: bold;'>#{$b['seat_number']}</span></p>
                    <p><strong>Vehicle Number:</strong> <span style='color: #7c3aed; font-weight: bold;'>{$b['vehicle_no']}</span></p>
                    
                    <h3 style='border-bottom: 1px solid #eee; padding-bottom: 5px;'>Parking Session Schedule</h3>
                    <p><strong>Starts at:</strong> <span style='color: #dc2626;'>{$start_fmt}</span></p>
                    <p><strong>Ends at:</strong> <span style='color: #dc2626; font-weight: bold;'>{$end_fmt}</span></p>
                    <p><strong>Extended By:</strong> <span style='font-weight: bold;'>{$hrs} Hour(s)</span></p>
                    
                    <h3 style='border-bottom: 1px solid #eee; padding-bottom: 5px;'>Payment Summary</h3>
                    <p><strong>Amount Paid for Extension:</strong> <span style='color: #059669; font-weight: bold;'>₹".number_format($total_amt, 2)."</span></p>
                    <p><strong>Transaction ID:</strong> <code>{$_POST['razorpay_payment_id']}</code></p>
                    
                    <p style='background: #fffbeb; padding: 10px; border-left: 4px solid #f59e0b; font-style: italic;'>
                        <strong>Note:</strong> Your parking session is strictly scheduled for the times listed above. Please ensure your vehicle is parked in the designated slot #{$b['seat_number']}.
                    </p>
                    
                    <p>We look forward to serving you.</p>
                    <p>Best Regards,<br><strong>The Park Heaven Team</strong></p>
                </div>";
                $mail->send();
            } catch (Exception $e) {}
            echo json_encode(["status"=>"success"]);
        }
    }
    exit();
}

$stmt = $conn->prepare("SELECT slot_id, id FROM bookings WHERE end_time < NOW()");
$stmt->execute(); $exp = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
foreach($exp as $eb) {
    $conn->query("UPDATE slots SET available_slots = available_slots + 1 WHERE id = ".$eb["slot_id"]);
    $conn->query("DELETE FROM bookings WHERE id = ".$eb["id"]);
}

if (isset($_POST["payment_success"])) {
    $lid = $_POST["location_id"]; $sn = $_POST["seat_number"]; $vn = $_POST["vehicle_no"];
    $st = date("Y-m-d H:i:s", strtotime($_POST["start_time"])); $dur = (int)$_POST["duration"];
    $et = date("Y-m-d H:i:s", strtotime($_POST["start_time"]." + $dur hours"));
    $conn->begin_transaction();
    try {
        $stmt_loc = $conn->prepare("SELECT city, area, location FROM slots WHERE id = ?");
        $stmt_loc->bind_param("i",$lid); $stmt_loc->execute(); $li = $stmt_loc->get_result()->fetch_assoc();
        $conn->query("UPDATE slots SET available_slots = available_slots - 1 WHERE id = $lid AND available_slots > 0");
        $amt = ($_POST["payment_amount"] / 100);
        $stmt = $conn->prepare("INSERT INTO bookings (slot_id, seat_number, user_name, vehicle_no, payment_id, amount_paid, booking_time, end_time, city, area, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iisssdsssss", $lid, $sn, $user_name, $vn, $_POST["razorpay_payment_id"], $amt, $st, $et, $li["city"], $li["area"], $li["location"]);
        $stmt->execute(); $nbid = $conn->insert_id;
        $pdf = generateReceiptContent($nbid, $conn);
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP(); $mail->Host = "smtp.gmail.com"; $mail->SMTPAuth = true; $mail->Username = "parkheaven777@gmail.com"; $mail->Password = "ytxwhtesrejqqkcd"; $mail->SMTPSecure = "tls"; $mail->Port = 587;
            $mail->setFrom("parkheaven777@gmail.com","Park Heaven"); $mail->addAddress($user_email, $user_name);
            $mail->isHTML(true); $mail->Subject = "Booking Confirmation - Park Heaven";
            $mail->addStringAttachment($pdf, "Receipt.pdf");
            
            $start_fmt = date("d-m-Y h:i A", strtotime($st));
            $end_fmt = date("d-m-Y h:i A", strtotime($et));
            $loc_full = $li['location'].", ".$li['area'].", ".$li['city'];
            
            $mail->Body = "
            <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #2563eb;'>Booking Confirmation</h2>
                <p>Dear <strong>{$user_name}</strong>,</p>
                <p>Thank you for choosing Park Heaven. Your parking reservation has been <span style='color: #059669; font-weight: bold;'>successfully confirmed</span>.</p>
                
                <p><strong>Location:</strong> <span style='color: #2563eb;'>{$loc_full}</span></p>
                <p><strong>Slot Number:</strong> <span style='color: #f59e0b; font-weight: bold;'>#{$sn}</span></p>
                <p><strong>Vehicle Number:</strong> <span style='color: #7c3aed; font-weight: bold;'>{$vn}</span></p>
                
                <h3 style='border-bottom: 1px solid #eee; padding-bottom: 5px;'>Parking Session Schedule</h3>
                <p><strong>Starts at:</strong> <span style='color: #dc2626;'>{$start_fmt}</span></p>
                <p><strong>Ends at:</strong> <span style='color: #dc2626; font-weight: bold;'>{$end_fmt}</span></p>
                <p><strong>Total Duration:</strong> {$dur} Hour(s)</p>
                
                <h3 style='border-bottom: 1px solid #eee; padding-bottom: 5px;'>Payment Summary</h3>
                <p><strong>Amount Paid:</strong> <span style='color: #059669; font-weight: bold;'>₹".number_format($amt, 2)."</span></p>
                <p><strong>Transaction ID:</strong> <code>{$_POST['razorpay_payment_id']}</code></p>
                
                <p style='background: #fffbeb; padding: 10px; border-left: 4px solid #f59e0b; font-style: italic;'>
                    <strong>Note:</strong> Your parking session is strictly scheduled for the times listed above. Please ensure your vehicle is parked in the designated slot #{$sn}.
                </p>
                
                <p>We look forward to serving you.</p>
                <p>Best Regards,<br><strong>The Park Heaven Team</strong></p>
            </div>";
            $mail->send();
        } catch (Exception $e) {}
        $conn->commit(); echo json_encode(["status"=>"success"]);
    } catch (Exception $e) { $conn->rollback(); echo json_encode(["status"=>"error"]); }
    exit();
}

if (isset($_GET["download_pdf"])) {
    $pdf = generateReceiptContent($_GET["booking_id"], $conn);
    header("Content-Type: application/pdf"); echo $pdf; exit();
}

if (isset($_POST["cancel_booking"])) {
    $stmt = $conn->prepare("SELECT slot_id FROM bookings WHERE id = ?");
    $stmt->bind_param("i", $_POST["booking_id"]); $stmt->execute(); $r = $stmt->get_result()->fetch_assoc();
    if($r) { $conn->query("UPDATE slots SET available_slots = available_slots + 1 WHERE id = ".$r["slot_id"]); $conn->query("DELETE FROM bookings WHERE id = ".$_POST["booking_id"]); }
    header("Location: UserBookSlot.php"); exit();
}

$available_locations = [];
$res = $conn->query("SELECT * FROM slots");
while($row = $res->fetch_assoc()) $available_locations[] = $row;

$stmt = $conn->prepare("SELECT b.*, s.price FROM bookings b JOIN slots s ON b.slot_id = s.id WHERE b.user_name = ? ORDER BY b.booking_time DESC");
$stmt->bind_param("s", $user_name); $stmt->execute(); $slots = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (isset($_POST["action"]) && $_POST["action"] === "fetch_booked_seats") {
    $lid = (int)$_POST["location_id"];
    $st = date("Y-m-d H:i:s", strtotime($_POST["start_time"]));
    $et = date("Y-m-d H:i:s", strtotime($_POST["start_time"]." + ".(int)$_POST["duration"]." hours"));
    $stmt = $conn->prepare("SELECT DISTINCT seat_number FROM bookings WHERE slot_id = ? AND ((booking_time <= ? AND end_time > ?) OR (booking_time < ? AND end_time >= ?))");
    $stmt->bind_param("issss", $lid, $st, $st, $et, $et); $stmt->execute();
    $booked = []; $res = $stmt->get_result(); while($row = $res->fetch_assoc()) $booked[] = (int)$row["seat_number"];
    $stmt = $conn->prepare("SELECT total_slots, price FROM slots WHERE id = ?");
    $stmt->bind_param("i", $lid); $stmt->execute(); $loc = $stmt->get_result()->fetch_assoc();
    echo json_encode(["success"=>true, "totalSlots"=>(int)$loc["total_slots"], "bookedSeats"=>$booked, "price"=>$loc["price"], "available_slots"=>(int)$loc["total_slots"]-count($booked)]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><title>Parking Slots</title>
    <link rel="stylesheet" href="./bootstrap.min.css"><link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        body { padding-top: 85px; font-family: "Inter", sans-serif; background: #f8fafc; }
        .main-content { margin-left: 220px; padding: 2rem; }
        @media (max-width: 992px) { .main-content { margin-left: 0; } }
        .content-card { background: white; border-radius: 0.75rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .parking-container { background: #343a40; border-radius: 15px; padding: 20px; border: 8px solid #23272b; position: relative; margin-bottom: 20px; overflow-x: auto; display: flex; flex-direction: column; gap: 10px; }
        .row-bays { display: flex; justify-content: flex-start; gap: 15px; position: relative; z-index: 1; min-width: max-content; }
        .parking-bay { width: 90px; height: 130px; border: 2.5px solid #fff; border-radius: 10px; display: flex; align-items: center; justify-content: center; position: relative; cursor: pointer; transition: 0.3s; flex-shrink: 0; }
        .bay-top { border-bottom: 0; border-radius: 10px 10px 0 0; }
        .bay-bottom { border-top: 0; border-radius: 0 0 10px 10px; }
        .road-line-horizontal { height: 5px; border-top: 5px dashed #ffc107; margin: 15px 0; opacity: 0.7; width: 100%; min-width: 1100px; }
        .car-icon { font-size: 35px; transition: 0.3s; color: #fff; opacity: 0.15; }
        .parking-bay:hover { background: rgba(255,255,255,0.1); }
        .parking-bay.booked { background: rgba(239, 68, 68, 0.2); cursor: not-allowed; }
        .parking-bay.booked .car-icon { color: #ef4444; opacity: 1; }
        .parking-bay.selected { background: rgba(16, 185, 129, 0.2); }
        .parking-bay.selected .car-icon { color: #10b981; opacity: 1; transform: scale(1.1) rotate(90deg); }
        .slot-label { position: absolute; font-size: 10px; color: #fff; opacity: 0.6; bottom: 2px; right: 5px; font-weight: bold; }
        .modal-xl { max-width: 95% !important; }
    </style>
</head>
<body>
<div class="sidebar"><?php include "./Includes/Unavbar.php"; ?></div>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold">Parking Reservations</h2>
        <button class="btn btn-primary px-4 py-2" data-toggle="modal" data-target="#bookSlotModal">+ Book New Slot</button>
    </div>
    <div class="content-card">
        <table class="table table-hover mb-0">
            <thead class="bg-light"><tr><th class="px-4">Location</th><th>Slot</th><th>Vehicle</th><th>Time</th><th class="text-center">Action</th></tr></thead>
            <tbody>
                <?php foreach($slots as $s): ?>
                <tr>
                    <td class="px-4 align-middle"><strong><?= htmlspecialchars($s["location"]) ?></strong><br><small class="text-muted"><?= $s["city"] ?></small></td>
                    <td class="align-middle"><span class="badge badge-light border">#<?= $s["seat_number"] ?></span></td>
                    <td class="align-middle"><code><?= $s["vehicle_no"] ?></code></td>
                    <td class="align-middle text-muted small"><?= date("d M, h:i A", strtotime($s["booking_time"])) ?></td>
                    <td class="align-middle text-center">
                        <button class="btn btn-sm btn-warning mr-1" onclick="prepareExtend('<?= $s['id'] ?>', '<?= $s['vehicle_no'] ?>', '<?= $s['end_time'] ?>', '<?= $s['price'] ?>')">Extend</button>
                        <a href="?download_pdf=true&booking_id=<?= $s['id'] ?>" class="btn btn-sm btn-info mr-1"><i class="fas fa-file-pdf"></i></a>
                        <form method="POST" class="d-inline" onsubmit="return confirm('Cancel?')"><input type="hidden" name="booking_id" value="<?= $s['id'] ?>"><button name="cancel_booking" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="bookSlotModal" tabindex="-1" role="dialog"><div class="modal-dialog modal-xl"><div class="modal-content"><div class="modal-header"><h5 class="modal-title font-weight-bold">New Reservation</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body p-4">
    <div class="parking-container">
        <div id="seat-map" class="seat-grid-horizontal" style="min-height: 250px;">
            <div style="color: white; text-align: center; width: 100%; padding: 50px;">Select a location to generate road map...</div>
        </div>
    </div>

    <form id="bookingForm"><input type="hidden" name="user_name" value="<?= $user_name ?>">
        <div class="row">
            <div class="col-md-3">
                <label class="small font-weight-bold">CITY</label>
                <select id="city" class="form-control" onchange="updateAreas()"><option value="">City</option><?php foreach(array_unique(array_column($available_locations, 'city')) as $c) echo "<option value='$c'>$c</option>"; ?></select>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">AREA</label>
                <select id="area" class="form-control" onchange="updateLocations()" disabled><option value="">Area</option></select>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold">LOCATION</label>
                <select id="location_id" name="location_id" class="form-control" onchange="fetchSeatMap()" disabled><option value="">Location</option></select>
            </div>
            <div class="col-md-3">
                <label class="small font-weight-bold text-danger">VEHICLE NO *</label>
                <input type="text" id="vehicle_no" name="vehicle_no" class="form-control border-danger" placeholder="GJ 05 MH 1234">
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-4">
                <label class="small font-weight-bold">START TIME</label>
                <div class="d-flex align-items-center">
                    <input type="text" class="form-control bg-light text-dark font-weight-bold mr-1" style="flex: 2.5;" value="<?=date("d-m-Y")?>" readonly>
                    <input type="hidden" id="start_date" value="<?=date("Y-m-d")?>">
                    <select id="start_hour" class="form-control mr-1" style="flex: 1;"><?php for($h=1;$h<=12;$h++) echo "<option value='".sprintf("%02d",$h)."'>".sprintf("%02d",$h)."</option>"; ?></select>
                    <select id="start_minute" class="form-control mr-1" style="flex: 1;"><?php for($m=0;$m<=55;$m+=5) echo "<option value='".sprintf("%02d",$m)."'>".sprintf("%02d",$m)."</option>"; ?></select>
                    <select id="start_period" class="form-control" style="flex: 1;"><option>AM</option><option>PM</option></select>
                </div>
                <input type="hidden" id="start_time" name="start_time">
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">DURATION</label>
                <select id="duration" name="duration" class="form-control" onchange="fetchSeatMap()">
                    <option value="1">1 Hour</option><option value="2" selected>2 Hours</option><option value="3">3 Hours</option><option value="4">4 Hours</option><option value="6">6 Hours</option><option value="8">8 Hours</option><option value="12">12 Hours</option><option value="24">24 Hours</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="small font-weight-bold">SLOT #</label>
                <input type="text" id="selected_seat" name="seat_number" class="form-control bg-white font-weight-bold text-primary" readonly>
            </div>
            <div class="col-md-4">
                <label class="small font-weight-bold">PAYMENT SUMMARY</label>
                <div class="alert alert-info py-2 px-3 mb-0">Price: <i class="fas fa-rupee-sign"></i><span id="base_price">0.00</span> + 18% GST = <strong><i class="fas fa-rupee-sign"></i><span id="total_amount">0.00</span></strong></div>
            </div>
        </div>
        <button type="button" id="payButton" class="btn btn-primary btn-lg btn-block font-weight-bold mt-4">PAY & CONFIRM BOOKING</button>
    </form>
</div></div></div></div>

<div class="modal fade" id="extendTimeModal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title font-weight-bold">Extend Time</h5><button class="close" data-dismiss="modal">&times;</button></div><div class="modal-body"><input type="hidden" id="extend_booking_id"><input type="hidden" id="extend_unit_price"><label class="small font-weight-bold">ADDITIONAL HOURS</label><select id="extend_hours" class="form-control" onchange="calculateExtendPrice()"><option value="1">1 Hour</option><option value="2">2 Hours</option></select><div id="extend_details" class="mt-3 text-muted"></div><div class="alert alert-warning mt-3">Extension Cost: <i class="fas fa-rupee-sign"></i><span id="ext_base">0.00</span> + 18% GST = <strong><i class="fas fa-rupee-sign"></i><span id="extend_total_amount">0.00</span></strong></div></div><div class="modal-footer"><button type="button" id="payExtendButton" class="btn btn-primary btn-block">Pay & Extend</button></div></div></div></div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script><script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script>
    const locations = <?= json_encode($available_locations) ?>;
    function updateAreas() { 
        const city = $('#city').val(); $('#area').val('').prop('disabled', !city).html('<option value="">Area</option>'); 
        $('#location_id').val('').prop('disabled', true).html('<option value="">Location</option>'); 
        if(city) { [...new Set(locations.filter(l => l.city === city).map(l => l.area))].forEach(a => $('#area').append('<option value="' + a + '">' + a + '</option>')); $('#area').prop('disabled', false); } 
    }
    function updateLocations() { 
        const city = $('#city').val(), area = $('#area').val(); 
        $('#location_id').val('').prop('disabled', !area).html('<option value="">Location</option>'); 
        if(area) { locations.filter(l => l.city === city && l.area === area).forEach(l => $('#location_id').append('<option value="' + l.id + '" data-price="' + l.price + '">' + l.location + '</option>')); $('#location_id').prop('disabled', false); } 
    }
    function calculatePrice() { const p = parseFloat($('#location_id option:selected').data('price') || 0), d = parseInt($('#duration').val() || 0); const base = p * d; const total = base * 1.18; $('#base_price').text(base.toFixed(2)); $('#total_amount').text(total.toFixed(2)); }
    function calculateExtendPrice() { const p = parseFloat($('#extend_unit_price').val() || 0), h = parseInt($('#extend_hours').val() || 0); const base = p * h; const total = base * 1.18; $('#ext_base').text(base.toFixed(2)); $('#extend_total_amount').text(total.toFixed(2)); }
    function updateStartTime() { 
        const ds = $('#start_date').val(); 
        let h = parseInt($('#start_hour').val()); if($('#start_period').val()==='PM' && h<12) h+=12; if($('#start_period').val()==='AM' && h===12) h=0; 
        const f = ds + ' ' + h.toString().padStart(2,'0') + ':' + $('#start_minute').val() + ':00'; $('#start_time').val(f); return f; 
    }
    function fetchSeatMap() { 
        const id = $('#location_id').val(); if(!id) return; 
        $.post('UserBookSlot.php', { action: 'fetch_booked_seats', location_id: id, start_time: updateStartTime(), duration: $('#duration').val() }, function(res) { 
            const m = $('#seat-map').empty(); 
            const total = res.totalSlots;
            const numRows = Math.ceil(total / 10);
            const perRow = 10;
            
            let currentSlot = 1;
            for(let r=0; r<numRows; r++) {
                const isTop = (r % 2 === 0);
                const rowDiv = $('<div class="row-bays"></div>');
                
                for(let i=0; i<perRow && currentSlot <= total; i++) {
                    const b = res.bookedSeats.includes(currentSlot);
                    const bay = $('<div class="parking-bay ' + (isTop?'bay-top':'bay-bottom') + ' ' + (b?'booked':'') + '" onclick="selectSeat(this, ' + currentSlot + ')"></div>');
                    bay.append('<i class="fas fa-car car-icon"></i><span class="slot-label">' + currentSlot + '</span>');
                    rowDiv.append(bay);
                    currentSlot++;
                }
                m.append(rowDiv);
                if (isTop && r < numRows - 1) {
                    m.append('<div class="road-line-horizontal"></div>');
                } else if (!isTop && r < numRows - 1) {
                    m.append('<div style="height: 25px; width: 100%;"></div>');
                }
            }
            calculatePrice(); 
        }, 'json'); 
    }
    function selectSeat(el, n) { if($(el).hasClass('booked')) return; $('.parking-bay').removeClass('selected'); $(el).addClass('selected'); $('#selected_seat').val(n); }
    function prepareExtend(id, v, e, p) { $('#extend_booking_id').val(id); $('#extend_unit_price').val(p); $('#extend_details').html('<p>Vehicle: ' + v + '<br>Current End: ' + e + '</p>'); calculateExtendPrice(); $('#extendTimeModal').modal('show'); }
    $(function() {
        $('#bookSlotModal').on('shown.bs.modal', function() { 
            const n = new Date(); let h = n.getHours(); const m = Math.ceil(n.getMinutes()/5)*5, p = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12; 
            $('#start_hour').val(h.toString().padStart(2,'0')); $('#start_minute').val((m>=60?0:m).toString().padStart(2,'0')); $('#start_period').val(p); 
            updateStartTime(); 
        });
        $('#start_hour, #start_minute, #start_period').on('change', function() { updateStartTime(); fetchSeatMap(); });
        $('#vehicle_no').on('input', function() { let v = $(this).val().toUpperCase().replace(/[^A-Z0-9]/g, ''), p = []; if (v.length > 0) p.push(v.slice(0, 2)); if (v.length > 2) p.push(v.slice(2, 4)); if (v.length > 4) { let r = v.slice(4), m = r.match(/^([A-Z]{1,2})([0-9]{0,4})/); if (m) { p.push(m[1]); if (m[2]) p.push(m[2]); } else p.push(r); } $(this).val(p.join(' ').substring(0, 13)); });
        $('#payButton').click(function() { 
            const vn = $('#vehicle_no').val().trim(); if(!vn) return alert('Error: Please enter your vehicle number.');
            const stStr = updateStartTime().replace(/-/g, '/'); const sel = new Date(stStr); const now = new Date(); if(sel < new Date(now.getTime() - 300000)) return alert('Error: You cannot book for a past time!');
            const amt = parseFloat($('#total_amount').text()); if(!amt || !$('#selected_seat').val()) return alert('Error: Please select a parking slot.');
            const o = { key: "rzp_test_RrPdWaQRNrYGaT", amount: Math.round(amt * 100), currency: "INR", name: "Park Heaven", handler: function(res) { const data = $('#bookingForm').serialize() + '&payment_success=1&razorpay_payment_id=' + res.razorpay_payment_id + '&payment_amount=' + Math.round(amt*100) + '&start_time=' + $('#start_time').val() + '&duration=' + $('#duration').val(); $.post('UserBookSlot.php', data, function() { location.reload(); }); } }; new Razorpay(o).open(); 
        });
        $('#payExtendButton').click(function() { const amt = parseFloat($('#extend_total_amount').text()); const o = { key: "rzp_test_RrPdWaQRNrYGaT", amount: Math.round(amt * 100), currency: "INR", name: "Park Heaven", handler: function(res) { $.post('UserBookSlot.php', { extend_payment_success: 1, booking_id: $('#extend_booking_id').val(), extend_hours: $('#extend_hours').val(), razorpay_payment_id: res.razorpay_payment_id }, function() { location.reload(); }); } }; new Razorpay(o).open(); });
    });
</script>
</body></html>
