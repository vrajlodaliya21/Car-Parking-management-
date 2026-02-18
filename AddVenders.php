<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_SESSION['admin'])) {
  header('Location: Index.php');
  exit();
}
include 'Connection.php';

if (isset($_SESSION['user'])) {
  header('Location: Index.php');
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (isset($_POST['vendor_id'])) {
    $vendor_id = $_POST['vendor_id'];
    $delete_stmt = $conn->prepare("DELETE FROM vendors WHERE id = ?");
    $delete_stmt->bind_param("i", $vendor_id);
    
    if ($delete_stmt->execute()) {
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => 'Vendor deleted successfully']);
        exit;
      }
      $success_message = "Vendor deleted successfully";
    } else {
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Error deleting vendor: " . $delete_stmt->error]);
        exit;
      }
      $error_message = "Error deleting vendor: " . $delete_stmt->error;
    }
    $delete_stmt->close();
    exit;
  }

  // Handle add vendor request
  $name = $_POST['name'];
  $email = $_POST['email'];
  $original_password = $_POST['password'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $phone = $_POST['phone'];
  $city = ucfirst(strtolower($_POST['city']));
  $area = ucfirst(strtolower($_POST['area']));
  $location = ucfirst(strtolower($_POST['location']));

  $check_email = $conn->prepare("SELECT id FROM vendors WHERE email = ?");
  $check_email->bind_param("s", $email);
  $check_email->execute();
  $email_result = $check_email->get_result();
  
  $check_name = $conn->prepare("SELECT id FROM vendors WHERE name = ?");
  $check_name->bind_param("s", $name);
  $check_name->execute();
  $name_result = $check_name->get_result();
  
  // Add check for unique location
  $check_location = $conn->prepare("SELECT id FROM vendors WHERE LOWER(location) = LOWER(?)");
  $check_location->bind_param("s", $location);
  $check_location->execute();
  $location_result = $check_location->get_result();
  
  $check_area_location = $conn->prepare("SELECT id FROM vendors WHERE LOWER(area) = LOWER(?) AND LOWER(location) = LOWER(?)");
  $check_area_location->bind_param("ss", $area, $location);
  $check_area_location->execute();
  $area_location_result = $check_area_location->get_result();

  if ($email_result->num_rows > 0) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => "Email address already exists. Please use a different email."]);
      exit;
    }
    $error_message = "Email address already exists. Please use a different email.";
  } 
  else if ($name_result->num_rows > 0) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => "Vendor name already exists. Please use a different name."]);
      exit;
    }
    $error_message = "Vendor name already exists. Please use a different name.";
  }
  else if ($location_result->num_rows > 0) {
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => "This location already exists. Please use a different location."]);
      exit;
    }
    $error_message = "This location already exists. Please use a different location.";
  }
  else if ($area_location_result->num_rows > 0) {
    // For AJAX requests, return JSON response
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
      header('Content-Type: application/json');
      echo json_encode(['success' => false, 'message' => "A vendor already exists in this location (Area: $area, Location: $location)"]);
      exit;
    }
    $error_message = "A vendor already exists in this location (Area: $area, Location: $location)";
  } else {
    $stmt = $conn->prepare("INSERT INTO vendors (name, email, password, phone, city, area, location) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $name, $email, $password, $phone, $city, $area, $location);

    if ($stmt->execute()) {
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
          $mail->addAddress($email, $name);

          $mail->isHTML(false);
          $mail->Subject = 'Welcome to Park Heaven - Your Vendor Account Details';

          $message = "Hello {$name},\n\n"
              . "Your vendor account has been successfully created. Here are your account details:\n\n"
              . "Details:\n"
              . "- Name: {$name}\n"
              . "- Email: {$email}\n"
              . "- Password: {$original_password}\n"
              . "- City: {$city}\n"
              . "- Area: {$area}\n"
              . "- Location: {$location}\n\n"
              . "Please keep these credentials safe and change your password after your first login.\n"
              . "If you have any questions, please don't hesitate to contact us.\n\n"
              . "Thank you for joining Park Heaven!";

          $mail->Body = $message;
          $mail->send();
          $success_message = "Vendor added successfully! Welcome email sent.";
      } catch (Exception $e) {
          $success_message = "Vendor added successfully! However, there was an issue sending the welcome email.";
          error_log("Email sending failed: " . $mail->ErrorInfo);
      }
      
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $success_message]);
        exit;
      }
    } else {
      if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => "Error: " . $stmt->error]);
        exit;
      }
      $error_message = "Error: " . $stmt->error;
    }
    $stmt->close();
  }
    $check_email->close();
  $check_name->close();
  $check_location->close();
  $check_area_location->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <link rel="shortcut icon" type="image/x-icon" href="../Car/Images/Main_Image.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin - Add Vendor</title>
  <link rel="stylesheet" href="Styles.css">
  <link rel="stylesheet" href="bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

  <style>
    .sidebar {
      height: 100vh;
      padding: 20px;
      position: fixed;
      top: 0;
      left: 0;
    }

    .main-content {
      margin-left: 750px;
      padding: 20px;
      min-height: 100vh;
      background-color: #fff;
    }

    .table-container {
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
      padding: 20px;
      margin-top: 20px;
    }

    .table td {
      vertical-align: middle;
    }

    .action-buttons {
      display: flex;
      gap: 5px;
    }

    .btn-action {
      padding: 5px 10px;
      border-radius: 4px;
    }

    #modalAlertContainer {
      display: none;
    }

    @media (max-width: 768px) {
      .sidebar {
        width: 100%;
        height: auto;
        position: relative;
        box-shadow: none;
      }

      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>

<body class="bg-content">
  <div class="sidebar">
    <?php include './Includes/sidebar.php'; ?>
  </div>

  <div class="main-content">
    <div class="container col-11">
      <div class="d-flex justify-content-between align-items-center mb-4">
  <h1 class="h3 mb-0">Vendor Management</h1>

  <div>
    <!-- Login Vendors Button -->
    <a href="http://localhost/Car/Vendors/Login.php" 
       class="btn btn-success mr-2">
      <i class="fas fa-sign-in-alt mr-1"></i> Login Vendors
    </a>

    <!-- Add New Vendor Button -->
    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addVendorModal">
      <i class="fas fa-plus mr-2"></i> Add New Vendor
    </button>
  </div>
</div>


      <div id="alertContainer" class="mb-4" style="display: none;">
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="fas fa-check-circle mr-2"></i>
          <span id="alertMessage"></span>
          <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>

      <div class="table-container">
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr>
                <th>No</th>
                <th>Name</th>
                <th>Email</th>
                <th>City</th>
                <th>Area</th>
                <th>Location</th>
                <th>Contact No</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $i = 1;
              $query = "SELECT id, name, email, city, area, location, phone FROM vendors ORDER BY name";
              $result = $conn->query($query);

              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  echo "<tr>";
                  echo "<td>" . $i++ . "</td>";
                  echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['city']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['area']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                  echo "<td>
                          <div class='action-buttons'>
                            <button class='btn btn-sm btn-danger btn-action' onclick='deleteVendor(" . $row['id'] . ")'><i class='fas fa-trash'></i></button>
                          </div>
                        </td>";
                  echo "</tr>";
                }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Vendor  -->
  <div class="modal fade" id="addVendorModal" tabindex="-1" role="dialog" aria-labelledby="addVendorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content rounded">
        <div class="modal-header bg-light border-bottom">
          <h5 class="modal-title font-weight-bold text-dark" id="addVendorModalLabel">Add New Vendor</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body p-4">
          <div id="modalAlertContainer" class="mb-4">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="fas fa-exclamation-circle mr-2"></i>
              <span id="modalAlertMessage"></span>
              <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
          </div>

          <?php if (isset($success_message)): ?>
            <div class="alert alert-success text-center mb-4">
              <i class="fas fa-check-circle mr-2"></i> <?php echo $success_message; ?>
            </div>
          <?php endif; ?>

          <?php if (isset($error_message)): ?>
            <div class="alert alert-danger text-center mb-4">
              <i class="fas fa-exclamation-circle mr-2"></i> <?php echo $error_message; ?>
            </div>
          <?php endif; ?>

          <form id="vendorForm" method="POST" action="" onsubmit="return false;">
            <div class="form-group mb-4">
              <label for="name" class="h5">Vendor Name</label>
              <input type="text" class="form-control form-control-lg" id="name" name="name">
              <div class="invalid-feedback" id="nameError">
                Please enter a valid name
              </div>
            </div>

            <div class="form-group mb-4">
              <label for="email" class="h5">Email Address</label>
              <input type="email" class="form-control form-control-lg" id="email" name="email">
              <div class="invalid-feedback" id="emailError">
                Please enter a valid email address
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-md-4">
                <div class="form-group">
                  <label for="city" class="h5">City</label>
                  <input type="text" class="form-control form-control-lg" id="city" name="city">
                  <div class="invalid-feedback" id="cityError">
                    Please enter a city
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="area" class="h5">Area</label>
                  <input type="text" class="form-control form-control-lg" id="area" name="area">
                  <div class="invalid-feedback" id="areaError">
                    Please enter an area
                  </div>
                </div>
              </div>
              <div class="col-md-4">
                <div class="form-group">
                  <label for="location" class="h5">Location</label>
                  <input type="text" class="form-control form-control-lg" id="location" name="location">
                  <div class="invalid-feedback" id="locationError">
                    Please enter a location
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group mb-4">
              <label for="password" class="h5">Password</label>
              <input type="password" class="form-control form-control-lg" id="password" name="password">
              <div class="invalid-feedback" id="passwordError">
                Password must be at least 8 characters long
              </div>
            </div>

            <div class="form-group mb-4">
              <label for="phone" class="h5">Phone Number</label>
              <input type="tel" class="form-control form-control-lg" id="phone" name="phone">
              <div class="invalid-feedback" id="phoneError">
                Please enter a valid phone number
              </div>
            </div>

            <div class="modal-footer border-top p-4">
              <button type="button" class="btn btn-secondary btn-lg" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary btn-lg">Add Vendor</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('vendorForm');
      const nameInput = document.getElementById('name');
      const emailInput = document.getElementById('email');
      const passwordInput = document.getElementById('password');
      const phoneInput = document.getElementById('phone');
      const cityInput = document.getElementById('city');
      const areaInput = document.getElementById('area');
      const locationInput = document.getElementById('location');
      const modalAlertContainer = document.getElementById('modalAlertContainer');
      const modalAlertMessage = document.getElementById('modalAlertMessage');
      const alertContainer = document.getElementById('alertContainer');
      const alertMessage = document.getElementById('alertMessage');

      function showError(input, errorElement, message) {
        input.classList.add('is-invalid');
        errorElement.style.display = 'block';
        errorElement.textContent = message;
      }

      function hideError(input, errorElement) {
        input.classList.remove('is-invalid');
        errorElement.style.display = 'none';
      }

      nameInput.addEventListener('input', function () {
        const name = this.value.trim();
        const nameError = document.getElementById('nameError');

        if (name === '') {
          showError(this, nameError, 'Name is required!');
        } else if (name.length < 2) {
          showError(this, nameError, 'Name must be at least 2 characters long!');
        } else {
          hideError(this, nameError);
        }
      });

      emailInput.addEventListener('input', function () {
        const email = this.value.trim();
        const emailError = document.getElementById('emailError');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if (email === '') {
          showError(this, emailError, 'Email is required!');
        } else if (!emailRegex.test(email)) {
          showError(this, emailError, 'Please enter a valid email address!');
        } else {
          hideError(this, emailError);
        }
      });

      passwordInput.addEventListener('input', function () {
        const password = this.value;
        const passwordError = document.getElementById('passwordError');

        if (password === '') {
          showError(this, passwordError, 'Password is required!');
        } else if (password.length < 8) {
          showError(this, passwordError, 'Password must be at least 8 characters long!');
        } else {
          hideError(this, passwordError);
        }
      });

      phoneInput.addEventListener('input', function () {
        const phone = this.value.trim();
        const phoneError = document.getElementById('phoneError');
        const phoneRegex = /^[0-9]{10}$/;

        if (phone === '') {
          showError(this, phoneError, 'Phone number is required!');
        } else if (!phoneRegex.test(phone)) {
          showError(this, phoneError, 'Please enter a valid 10-digit phone number!');
        } else {
          hideError(this, phoneError);
        }
      });

      cityInput.addEventListener('input', function () {
        const city = this.value.trim();
        const cityError = document.getElementById('cityError');

        if (city === '') {
          showError(this, cityError, 'City is required!');
        } else if (city.length < 2) {
          showError(this, cityError, 'City must be at least 2 characters long!');
        } else {
          hideError(this, cityError);
        }
      });

      areaInput.addEventListener('input', function () {
        const area = this.value.trim();
        const areaError = document.getElementById('areaError');

        if (area === '') {
          showError(this, areaError, 'Area is required!');
        } else if (area.length < 2) {
          showError(this, areaError, 'Area must be at least 2 characters long!');
        } else {
          hideError(this, areaError);
        }
      });

      locationInput.addEventListener('input', function () {
        const location = this.value.trim();
        const locationError = document.getElementById('locationError');

        if (location === '') {
          showError(this, locationError, 'Location is required!');
        } else if (location.length < 2) {
          showError(this, locationError, 'Location must be at least 2 characters long!');
        } else {
          hideError(this, locationError);
        }
      });

      modalAlertContainer.style.display = 'none';

      form.addEventListener('submit', function (e) {
        e.preventDefault();

        nameInput.dispatchEvent(new Event('input'));
        emailInput.dispatchEvent(new Event('input'));
        passwordInput.dispatchEvent(new Event('input'));
        phoneInput.dispatchEvent(new Event('input'));
        cityInput.dispatchEvent(new Event('input'));
        areaInput.dispatchEvent(new Event('input'));
        locationInput.dispatchEvent(new Event('input'));

        const hasErrors = form.querySelectorAll('.is-invalid').length > 0;

        if (!hasErrors) {
          const formData = new FormData(form);
          fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                alertContainer.querySelector('.alert').className = 'alert alert-success alert-dismissible fade show';
                alertMessage.textContent = data.message;
                alertContainer.style.display = 'block';
                
                form.reset();
                $('#addVendorModal').modal('hide');

                setTimeout(() => {
                  location.reload();
                }, 1500);
              } else {
                modalAlertContainer.style.display = 'block';
                modalAlertContainer.querySelector('.alert').className = 'alert alert-danger alert-dismissible fade show';
                modalAlertMessage.textContent = data.message;
                                setTimeout(() => {
                  modalAlertContainer.style.display = 'none';
                }, 2000);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              modalAlertContainer.style.display = 'block';
              modalAlertContainer.querySelector('.alert').className = 'alert alert-danger alert-dismissible fade show';
              modalAlertMessage.textContent = 'An error occurred while adding the vendor';
                            setTimeout(() => {
                modalAlertContainer.style.display = 'none';
              }, 2000);
            });
        }
      });

      $('#addVendorModal').on('hidden.bs.modal', function () {
        form.reset();
        document.querySelectorAll('.is-invalid').forEach(input => {
          input.classList.remove('is-invalid');
        });
        document.querySelectorAll('.invalid-feedback').forEach(error => {
          error.style.display = 'none';
        });
        modalAlertContainer.style.display = 'none';
      });

      window.deleteVendor = function (id) {
        if (confirm('Are you sure you want to delete this vendor?')) {
          const formData = new FormData();
          formData.append('vendor_id', id);

          fetch(window.location.href, {
            method: 'POST',
            body: formData,
            headers: {
              'X-Requested-With': 'XMLHttpRequest'
            }
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                alertContainer.querySelector('.alert').className = 'alert alert-success alert-dismissible fade show';
                alertMessage.textContent = data.message;
                alertContainer.style.display = 'block';
                setTimeout(() => {
                  location.reload();
                }, 1500);
              } else {
                alertContainer.querySelector('.alert').className = 'alert alert-danger alert-dismissible fade show';
                alertMessage.textContent = data.message;
                alertContainer.style.display = 'block';
                setTimeout(() => {
                  alertContainer.style.display = 'none';
                }, 2000);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alertContainer.querySelector('.alert').className = 'alert alert-danger alert-dismissible fade show';
              alertMessage.textContent = 'An error occurred while deleting the vendor';
              alertContainer.style.display = 'block';
              setTimeout(() => {
                alertContainer.style.display = 'none';
              }, 2000);
            });
        }
      };

      <?php if (isset($success_message)): ?>
        alertContainer.querySelector('.alert').className = 'alert alert-success alert-dismissible fade show';
        alertMessage.textContent = '<?php echo $success_message; ?>';
        alertContainer.style.display = 'block';
        setTimeout(() => {
          alertContainer.style.display = 'none';
        }, 2000);
      <?php endif; ?>

      <?php if (isset($error_message)): ?>
        if ($('#addVendorModal').hasClass('show')) {
          modalAlertContainer.style.display = 'block';
          modalAlertContainer.querySelector('.alert').className = 'alert alert-danger alert-dismissible fade show';
          modalAlertMessage.textContent = '<?php echo $error_message; ?>';
          
          setTimeout(() => {
            modalAlertContainer.style.display = 'none';
          }, 2000);
        } else {
          alertContainer.querySelector('.alert').className = 'alert alert-danger alert-dismissible fade show';
          alertMessage.textContent = '<?php echo $error_message; ?>';
          alertContainer.style.display = 'block';
          
          setTimeout(() => {
            alertContainer.style.display = 'none';
          }, 2000);
        }
      <?php endif; ?>
    });
  </script>
</body>

</html>