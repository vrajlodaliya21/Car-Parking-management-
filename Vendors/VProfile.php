<?php
session_start();
include '../Connection.php';
if (isset($_SESSION['admin'])) {
  header('Location: Index.php');
  exit();
}
if (isset($_SESSION['user'])) {
  header('Location: Index.php');
  exit();
}
if (!isset($_SESSION['vendor'])) {
  header('Location: Login.php');
  exit();
}

$vendor_id = $_SESSION['vendor_id'];

$sql_vendor = 'SELECT * FROM vendors WHERE id = ?';
$stmt = mysqli_prepare($conn, $sql_vendor);
mysqli_stmt_bind_param($stmt, 'i', $vendor_id);
mysqli_stmt_execute($stmt);
$vendor_details = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$sql_recent = 'SELECT * FROM bookings
WHERE city = ? AND area = ? AND location = ?
ORDER BY booking_time DESC LIMIT 5';
$stmt = mysqli_prepare($conn, $sql_recent);
mysqli_stmt_bind_param($stmt, 'sss', $vendor_details['city'], $vendor_details['area'], $vendor_details['location']);
mysqli_stmt_execute($stmt);
$recent_bookings = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang='en'>

<head>
  <meta charset='UTF-8'>
  <link rel='shortcut icon' type='image/x-icon' href='../../Car/Images/Main_Image.png'>
  <meta http-equiv='X-UA-Compatible' content='IE=edge'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Vendor Profile</title>
  <link rel='stylesheet' href='../bootstrap.min.css'>
  <link rel='stylesheet' href='../Styles.css'>
  <link rel='stylesheet' href='https://pro.fontawesome.com/releases/v5.10.0/css/all.css' />
  <style>
    .profile-header {
      background: linear-gradient(135deg, #007bff, #00bcd4);
      color: white;
      padding: 30px;
      border-radius: 10px;
      margin-bottom: 30px;
    }

    .profile-header h1 {
      margin: 0;
      font-size: 2rem;
      text-align: center;
    }

    .profile-header p {
      margin: 10px 0 0;
      opacity: 0.9;
      text-align: center;
    }

    .profile-section {
      background: white;
      padding: 25px;
      border-radius: 10px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      margin-bottom: 20px;
    }

    .profile-section h3 {
      color: #2c3e50;
      margin-bottom: 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid #f0f0f0;
    }

    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
    }

    .info-item {
      display: flex;
      align-items: center;
      margin-bottom: 15px;
    }

    .info-icon {
      width: 40px;
      height: 40px;
      background: #f8f9fa;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 15px;
      color: #007bff;
    }

    .info-content {
      flex: 1;
    }

    .info-label {
      font-size: 0.9rem;
      color: #6c757d;
      margin-bottom: 5px;
    }

    .info-value {
      font-size: 1.1rem;
      color: #2c3e50;
      font-weight: 500;
    }

    @media (max-width: 768px) {
      .profile-header {
        padding: 20px;
      }

      .profile-header h1 {
        font-size: 1.5rem;
      }

      .info-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body class='bg-content'>
  <div class='sidebar'>
    <?php include '../Includes/Vsidebar.php';
    ?>
  </div>

  <main class='container d-flex'>
    <div class='container-fluid main-content px-4'>
      <div class='profile-header'>
        <h1>
          <?php echo htmlspecialchars($_SESSION['vendor']);
          ?>
        </h1>
        <p>Vendor Profile</p>
      </div>

      <div class='profile-section'>
        <h3><i class='fas fa-user-circle me-2'></i>Personal Information</h3>
        <div class='info-grid'>
          <div class='info-item'>
            <div class='info-icon'>
              <i class='fas fa-envelope'></i>
            </div>
            <div class='info-content'>
              <div class='info-label'>Email Address</div>
              <div class='info-value'>
                <?php echo htmlspecialchars($vendor_details['email']);
                ?>
              </div>
            </div>
          </div>
          <div class='info-item'>
            <div class='info-icon'>
              <i class='fas fa-phone'></i>
            </div>
            <div class='info-content'>
              <div class='info-label'>Phone Number</div>
              <div class='info-value'>
                <?php echo htmlspecialchars($vendor_details['phone']);
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class='profile-section'>
        <h3><i class='fas fa-map-marker-alt me-2'></i>Location Details</h3>
        <div class='info-grid'>
          <div class='info-item'>
            <div class='info-icon'>
              <i class='fas fa-city'></i>
            </div>
            <div class='info-content'>
              <div class='info-label'>City</div>
              <div class='info-value'>
                <?php echo htmlspecialchars($vendor_details['city']);
                ?>
              </div>
            </div>
          </div>
          <div class='info-item'>
            <div class='info-icon'>
              <i class='fas fa-building'></i>
            </div>
            <div class='info-content'>
              <div class='info-label'>Area</div>
              <div class='info-value'>
                <?php echo htmlspecialchars($vendor_details['area']);
                ?>
              </div>
            </div>
          </div>
          <div class='info-item'>
            <div class='info-icon'>
              <i class='fas fa-map'></i>
            </div>
            <div class='info-content'>
              <div class='info-label'>Location</div>
              <div class='info-value'>
                <?php echo htmlspecialchars($vendor_details['location']);
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include '../Includes/Adminfooter.php';
  ?>
</body>

</html>