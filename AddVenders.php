<?php
session_start();
if (!isset($_SESSION['admin'])) {
  header('Location: Index.php');
  exit();
}

include 'Connection.php';

// Check if status column exists, if not add it
$checkColumn = $conn->query("SHOW COLUMNS FROM vendors LIKE 'status'");
if ($checkColumn->num_rows == 0) {
  $conn->query("ALTER TABLE vendors ADD COLUMN status VARCHAR(20) DEFAULT 'pending'");
  $conn->query("UPDATE vendors SET status = 'pending' WHERE status IS NULL OR status = ''");
}

use PHPMailer\PHPMailer\{PHPMailer, Exception};
require "PHPMailer/src/Exception.php"; 
require "PHPMailer/src/PHPMailer.php"; 
require "PHPMailer/src/SMTP.php";

function sendApprovalEmail($email, $name, $password = null) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "vrajlodaliya62@gmail.com"; 
        $mail->Password = "vpxx gvpw jqwm luea"; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom("vrajlodaliya62@gmail.com", "Park Heaven Admin");
        $mail->addAddress($email, $name);
        $mail->isHTML(true);
        $mail->Subject = "Your Vendor Account Status - Park Heaven";
        
        $msg = "<h2>Congratulations $name!</h2>";
        $msg .= "<p>Your vendor request has been approved. You can now log in to your dashboard and start adding parking slots.</p>";
        $msg .= "<p><strong>Login Credentials:</strong><br>";
        $msg .= "Email: $email<br>";
        if ($password) {
            $msg .= "Password: $password<br>";
        } else {
            $msg .= "Password: (The one you used during registration)<br>";
        }
        $msg .= "</p>";
        $msg .= "<p><a href='http://localhost/car/Vendors/Login.php'>Click here to Login</a></p>";
        $msg .= "<br><p>Best Regards,<br>Park Heaven Team</p>";

        $mail->Body = $msg;
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle AJAX requests for Approve/Deny/Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['action']) || isset($_POST['vendor_id_delete']) || isset($_POST['name']))) {
  header('Content-Type: application/json');
  
  if (isset($_POST['action']) && isset($_POST['vendor_id'])) {
    $v_id = $_POST['vendor_id'];
    $action = $_POST['action'];
    $status = ($action === 'approve') ? 'approved' : 'denied';
    
    // Get vendor details for email
    $getVendor = $conn->prepare("SELECT name, email FROM vendors WHERE id = ?");
    $getVendor->bind_param("i", $v_id);
    $getVendor->execute();
    $vendorData = $getVendor->get_result()->fetch_assoc();

    if (!$vendorData) {
      echo json_encode(['success' => false, 'message' => "Vendor ID $v_id not found"]);
      exit;
    }

    $stmt = $conn->prepare("UPDATE vendors SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $v_id);
    
    if ($stmt->execute()) {
      if ($status === 'approved') {
          sendApprovalEmail($vendorData['email'], $vendorData['name']);
      }
      echo json_encode(['success' => true, 'message' => "Vendor account $status successfully and email sent"]);
    } else {
      echo json_encode(['success' => false, 'message' => "SQL Error: " . $stmt->error]);
    }
    exit;
  }

  if (isset($_POST['vendor_id_delete'])) {
    $v_id = $_POST['vendor_id_delete'];
    $stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $stmt->bind_param("i", $v_id);
    if ($stmt->execute()) {
      echo json_encode(['success' => true, 'message' => "Vendor deleted successfully"]);
    } else {
      echo json_encode(['success' => false, 'message' => "Error deleting vendor"]);
    }
    exit;
  }

  // Handle adding new vendor via modal
  if (isset($_POST['name']) && isset($_POST['email'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $plain_password = $_POST['password'];
    $password = password_hash($plain_password, PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $location = $_POST['location'];

    $stmt = $conn->prepare("INSERT INTO vendors (name, email, password, phone, city, area, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->bind_param("sssssss", $name, $email, $password, $phone, $city, $area, $location);
    
    if ($stmt->execute()) {
      sendApprovalEmail($email, $name, $plain_password);
      echo json_encode(['success' => true, 'message' => "Vendor added successfully and credentials emailed"]);
    } else {
      echo json_encode(['success' => false, 'message' => "Error adding vendor: " . $conn->error]);
    }
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Vendor Management</title>
  <link rel="stylesheet" href="./bootstrap.min.css">
  <link rel="stylesheet" href="./Styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    .main-content { margin-left: 250px; padding: 20px; }
    @media (max-width: 992px) { .main-content { margin-left: 0; } }
    .badge-pending { background-color: #ffc107; color: #000; }
    .badge-approved { background-color: #28a745; color: #fff; }
    .badge-denied { background-color: #dc3545; color: #fff; }
    .badge-denied-status { background-color: #dc3545; color: #fff; }
    .action-buttons .btn { margin-right: 5px; }
  </style>
</head>
<body class="bg-content">
  <div class="sidebar">
    <?php include './Includes/sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="container col-11 mt-4">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Vendor Management</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addVendorModal">
          <i class="fas fa-plus mr-2"></i> Add New Vendor
        </button>
      </div>

      <div id="alertContainer" style="display: none;" class="mb-4">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <span id="alertMessage"></span>
          <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
      </div>

      <div class="card shadow-sm border-0">
        <div class="card-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead class="bg-light">
                <tr>
                  <th>No</th>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Location</th>
                  <th>Status</th>
                  <th class="text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $i = 1;
                $query = "SELECT * FROM vendors ORDER BY (status = 'pending') DESC, id DESC";
                $result = $conn->query($query);
                while ($row = $result->fetch_assoc()):
                  $status = $row['status'] ?: 'pending';
                ?>
                <tr>
                  <td><?= $i++ ?></td>
                  <td><?= htmlspecialchars($row['name']) ?></td>
                  <td><?= htmlspecialchars($row['email']) ?></td>
                  <td><?= htmlspecialchars($row['location']) ?></td>
                  <td><span class="badge badge-<?= $status ?>"><?= ucfirst($status) ?></span></td>
                  <td class="text-center">
                    <div class="action-buttons">
                      <button class="btn btn-sm btn-info" title="View Details" onclick='viewDetails(<?= json_encode($row) ?>)'><i class="fas fa-eye"></i></button>
                      <?php if($status === 'pending'): ?>
                        <button class="btn btn-sm btn-success" title="Approve" onclick="updateStatus(<?= $row['id'] ?>, 'approve')"><i class="fas fa-check"></i></button>
                        <button class="btn btn-sm btn-warning" title="Deny" onclick="updateStatus(<?= $row['id'] ?>, 'deny')"><i class="fas fa-times"></i></button>
                      <?php endif; ?>
                      <button class="btn btn-sm btn-danger" title="Delete" onclick="deleteVendor(<?= $row['id'] ?>)"><i class="fas fa-trash"></i></button>
                    </div>
                  </td>
                </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Details Modal -->
  <div class="modal fade" id="vendorDetailsModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content border-0">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title">Vendor Complete Profile</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <div class="modal-body" id="vendorDetailsBody"></div>
      </div>
    </div>
  </div>

  <!-- Add Vendor Modal -->
  <div class="modal fade" id="addVendorModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content border-0">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Add New Registered Vendor</h5>
          <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
        </div>
        <form id="vendorForm">
          <div class="modal-body p-4">
            <div class="row">
              <div class="col-md-6 form-group"><label>Name</label><input type="text" name="name" class="form-control" required></div>
              <div class="col-md-6 form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Password</label>
                <div class="input-group">
                  <input type="password" name="password" id="add_v_pwd" class="form-control" required>
                  <div class="input-group-append"><span class="input-group-text" style="cursor:pointer;" onclick="togglePassword('add_v_pwd', this)"><i class="fas fa-eye"></i></span></div>
                </div>
              </div>
              <div class="col-md-6 form-group"><label>Phone</label><input type="text" name="phone" class="form-control" required></div>
            </div>
            <div class="row">
              <div class="col-md-4 form-group"><label>City</label><input type="text" name="city" class="form-control" required></div>
              <div class="col-md-4 form-group"><label>Area</label><input type="text" name="area" class="form-control" required></div>
              <div class="col-md-4 form-group"><label>Location</label><input type="text" name="location" class="form-control" required></div>
            </div>
          </div>
          <div class="modal-footer"><button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Save Vendor</button></div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function togglePassword(id, el) {
      const x = document.getElementById(id);
      const icon = el.querySelector('i');
      if (x.type === "password") { x.type = "text"; icon.className = "fas fa-eye-slash"; }
      else { x.type = "password"; icon.className = "fas fa-eye"; }
    }

    function viewDetails(v) {
      const html = `
        <div class="p-2">
          <div class="row mb-3 border-bottom pb-2"><div class="col-4 font-weight-bold">Status:</div><div class="col-8"><span class="badge badge-${v.status}">${v.status.toUpperCase()}</span></div></div>
          <div class="row mb-2"><div class="col-4 font-weight-bold">Vendor Name:</div><div class="col-8">${v.name}</div></div>
          <div class="row mb-2"><div class="col-4 font-weight-bold">Email ID:</div><div class="col-8">${v.email}</div></div>
          <div class="row mb-2"><div class="col-4 font-weight-bold">Contact:</div><div class="col-8">${v.phone}</div></div>
          <div class="row mb-2"><div class="col-4 font-weight-bold">City:</div><div class="col-8">${v.city}</div></div>
          <div class="row mb-2"><div class="col-4 font-weight-bold">Area:</div><div class="col-8">${v.area}</div></div>
          <div class="row mb-2"><div class="col-4 font-weight-bold">Location:</div><div class="col-8">${v.location}</div></div>
        </div>`;
      $('#vendorDetailsBody').html(html);
      $('#vendorDetailsModal').modal('show');
    }

    function updateStatus(id, action) {
      if(confirm(`Are you sure you want to ${action} this vendor?`)) {
        $.post('AddVenders.php', {action: action, vendor_id: id}, function(res) {
          if(res.success) { alert(res.message); location.reload(); }
        }, 'json');
      }
    }

    function deleteVendor(id) {
      if(confirm('Permanently delete this vendor?')) {
        $.post('AddVenders.php', {vendor_id_delete: id}, function(res) {
          if(res.success) { alert(res.message); location.reload(); }
        }, 'json');
      }
    }

    $('#vendorForm').on('submit', function(e) {
      e.preventDefault();
      $.post('AddVenders.php', $(this).serialize(), function(res) {
        if(res.success) { 
          alert(res.message); 
          $('#addVendorModal').modal('hide');
          location.reload(); 
        }
        else { alert(res.message); }
      }, 'json');
    });
  </script>
</body>
</html>