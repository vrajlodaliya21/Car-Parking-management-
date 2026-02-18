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

require_once 'Connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $current_pwd = $_POST['current_pwd'];
  $new_pwd = $_POST['new_pwd'];
  $confirm_pwd = $_POST['confirm_pwd'];

  $email = $_SESSION['user_email'];

  // Fetch user details..
  $sql = "SELECT * FROM users WHERE U_Email = ?";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param('s', $email);
  $stmt->execute();
  $user = $stmt->get_result()->fetch_assoc();

  if ($user) {
    if (password_verify($current_pwd, $user['Password'])) {
      if ($new_pwd === $confirm_pwd) {
        $new_pwd_hash = password_hash($new_pwd, PASSWORD_BCRYPT);
        $sql = "UPDATE users SET Password = ? WHERE U_Email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $new_pwd_hash, $email);
        if ($stmt->execute()) {
          $_SESSION['flash_message'] = 'Password changed successfully!';
          $_SESSION['flash_message_type'] = 'success';
          header('Location: UDashboard.php');
          exit();
        } else {
          $_SESSION['flash_message'] = 'Failed to change password. Please try again later.';
          $_SESSION['flash_message_type'] = 'danger';
        }
      } else {
        $_SESSION['flash_message'] = 'New passwords do not match.';
        $_SESSION['flash_message_type'] = 'danger';
      }
    } else {
      $_SESSION['flash_message'] = 'Current password is incorrect.';
      $_SESSION['flash_message_type'] = 'danger';
    }
  } else {
    $_SESSION['flash_message'] = 'User not found.';
    $_SESSION['flash_message_type'] = 'danger';
  }
  header('Location: Changepass.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
  <meta charset='UTF-8'>
  <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <title>Change Password</title>
  <link rel='stylesheet' href='./bootstrap.min.css'>
  <link rel='stylesheet' href='./Styles.css'>
  <style>
    .change-password-form {
      position: absolute;
      top: 50%;
      left: 50%;
      width: 500px;
      padding: 40px;
      transform: translate(-50%, -50%);
      backdrop-filter: blur(9px);
      border: 1px solid black;
      border-radius: 33px;
      background-color: #66cfe21f;
    }

    .change-password-form h2 {
      margin-bottom: 1rem;
      font-weight: bold;
      color: black;
      font-size: 35px;
    }

    .form-control {
      border-radius: 30px;
      padding: 0.75rem 1.25rem;
      font-size: 1rem;
    }

    .form-group label {
      font-size: 20px;
      font-weight: 500;
    }

    .btn-custom {
      border-radius: 12px;
      padding: 0.75rem;
      font-size: 1rem;
      border: none;
      width: 200px;
      margin-right: 5px;
      color: white;
      transition: background-color 0.3s ease;
    }

    .btn-custom-cancel {
      border-radius: 12px;
      padding: 0.75rem;
      font-size: 1rem;
      width: 200px;
      border: none;
      color: white;
      transition: background-color 0.3s ease;
    }

    .btn-custom:hover {
      background-color: #0056b3;
    }

    .alert {
      margin: 1rem 0;
      font-size: 1rem;
    }

    .error-message {
      color: red;
      font-size: 0.875rem;
      margin-top: 0.25rem;
    }

    @media only screen and (max-width: 400px) {
      .change-password-form {
        width: 345px;
      }

      .btn-custom {
        width: 130px;
        font-size: 12px;
      }

      .btn-custom-cancel {
        width: 115px;
        font-size: 12px;
      }
    }
  </style>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-content">
  <div class='change-password-form'>
    <button onclick="window.history.back()" class="btn btn-secondary" 
      style="position: absolute; top: 41px; left: 25px; border-radius: 50%; width: 40px; height: 40px; padding: 0; display: flex; align-items: center; justify-content: center;">
      <i class="fas fa-arrow-left"></i>
    </button>

    <h2 class="text-center">Change Password</h2>
    <hr class="w-25 mx-auto border-dark">
    <?php if (isset($_SESSION['flash_message'])): ?>
      <div id="flashMessage" class="alert alert-<?php echo $_SESSION['flash_message_type']; ?>">
        <?php
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_message_type']);
        ?>
      </div>
    <?php endif; ?>
    <form action='Changepass.php' method='post' class="mt-5" id="change-password-form">
      <div class='form-group'>
        <label for='current_pwd'>Current Password:</label>
        <input type='password' class='form-control' id='current_pwd' name='current_pwd'>
        <span class="error-message" id="current-pwd-error"></span>
      </div>
      <div class='form-group'>
        <label for='new_pwd'>New Password:</label>
        <input type='password' class='form-control' id='new_pwd' name='new_pwd'>
        <span class="error-message" id="new-pwd-error"></span>
      </div>
      <div class='form-group mb-4'>
        <label for='confirm_pwd'>Confirm New Password:</label>
        <input type='password' class='form-control' id='confirm_pwd' name='confirm_pwd'>
        <span class="error-message" id="confirm-pwd-error"></span>
      </div>
      <div>
        <button type='submit' class='btn btn-custom'>Change Password</button>
        <button type='reset' class='btn btn-danger btn-custom-cancel'>Cancel</button>
      </div>
    </form>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var flashMessage = document.getElementById('flashMessage');
      if (flashMessage) {
        flashMessage.style.display = 'block';
        setTimeout(function () {
          flashMessage.style.display = 'none';
        }, 3000);
      }

      document.getElementById('change-password-form').addEventListener('submit', function(event) {
        let valid = true;

        document.getElementById('current-pwd-error').textContent = '';
        document.getElementById('new-pwd-error').textContent = '';
        document.getElementById('confirm-pwd-error').textContent = '';

        const currentPwd = document.getElementById('current_pwd').value;
        if (!currentPwd) {
          valid = false;
          document.getElementById('current-pwd-error').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Current password is required.';
        }

        const newPwd = document.getElementById('new_pwd').value;
        if (!newPwd) {
          valid = false;
          document.getElementById('new-pwd-error').innerHTML = '<i class="fas fa-info-circle ml-1"></i> New password is required.';
        }

        const confirmPwd = document.getElementById('confirm_pwd').value;
        if (!confirmPwd) {
          valid = false;
          document.getElementById('confirm-pwd-error').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Please confirm your new password.';
        } else if (newPwd !== confirmPwd) {
          valid = false;
          document.getElementById('confirm-pwd-error').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Passwords do not match.';
        }

        if (!valid) {
          event.preventDefault();
        }
      });
    });
  </script>
</body>

</html>
