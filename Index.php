<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: UDashboard.php');
    exit();
} elseif (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit();
}

$emailError = "";
$pwdError = "";

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    require_once 'Connection.php';

    // Check if email exists in users table
    $sqlUser = "SELECT * FROM users WHERE U_Email = ?";
    $stmt = mysqli_prepare($conn, $sqlUser);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultUser = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_array($resultUser, MYSQLI_ASSOC);

    $sqlAdmin = "SELECT * FROM admins WHERE A_Email = ?";
    $stmt = mysqli_prepare($conn, $sqlAdmin);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $resultAdmin = mysqli_stmt_get_result($stmt);
    $admin = mysqli_fetch_array($resultAdmin, MYSQLI_ASSOC);

    if (!$user && !$admin) {
        $emailError = "Email not found";
    } else {
        if ($user && !password_verify($pwd, $user['Password'])) {
            $pwdError = "Incorrect password";
        } elseif ($admin && $pwd !== $admin['A_Password']) {
            $pwdError = "Incorrect password";
        } elseif ($user) {
            $_SESSION['user'] = $user['U_Name'];
            $_SESSION['user_email'] = $user['U_Email'];
            header('Location: UDashboard.php');
            exit();
        } elseif ($admin) {
            $_SESSION['admin'] = 'yes';
            header('Location: dashboard.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="shortcut icon" type="image/x-icon" href="../Car/Images/Main_Image.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log In</title>
    <link rel="stylesheet" href="bootstrap.min.css">
    <link rel="stylesheet" href="./Styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-group {
            position: relative;
        }

        .form-group input::placeholder {
            font-size: 19px;
        }

        .form-group input {
            margin: 0 !important;
        }

        .error-message {
            color: red;
            font-size: 14px;
            display: none;
            align-items: center;
            padding-left: 10px;
            margin-top: 10px;
            min-height: 10px;
        }

        .error-message.show {
            display: flex;
        }

        .error-message i {
            margin-right: 5px;
        }

        .form-group.has-error {
            margin-bottom: 30px;
        }

        @media only screen and (max-width: 400px) {
            .form-group input {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="img">
        <div class="login">
            <form action="Index.php" method="post" onsubmit="return validateForm()">
                <h2 class="text-center">Log In</h2>
                <hr class="w-50 mx-auto pb-2 border-primary">
                
                <div class="form-group <?php echo $emailError ? 'has-error' : ''; ?>">
                    <input type="email" class="form-control" name="email" id="email" 
                           placeholder="Email" style="font-size: 19px;" 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="error-message <?php echo $emailError ? 'show' : ''; ?>" id="email-error">
                        <?php if ($emailError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $emailError; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group <?php echo $pwdError ? 'has-error' : ''; ?>">
                    <input type="password" class="form-control" name="pwd" id="pwd" 
                           placeholder="Password" style="font-size: 19px;">
                    <div class="error-message <?php echo $pwdError ? 'show' : ''; ?>" id="pwd-error">
                        <?php if ($pwdError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $pwdError; ?>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <input type="submit" class="btn btn-custom w-100" name="submit" value="Log In">
                </div>
            </form>
            <div class="text-center">
                <p class="reg text-white">Not Registered Yet? <a href="Ragistation.php" class="register-link">Register Here</a></p>
            </div>
        </div>
    </div>

    <script>
        function displayError(inputId, message) {
            const formGroup = document.getElementById(inputId).parentElement;
            const errorElement = document.getElementById(`${inputId}-error`);
            
            formGroup.classList.add('has-error');
            errorElement.innerHTML = `<i class="fas fa-info-circle"></i>${message}`;
            errorElement.classList.add('show');
        }

        function clearErrors() {
            const formGroups = document.getElementsByClassName('form-group');
            const errors = document.getElementsByClassName('error-message');
            
            for (let group of formGroups) {
                group.classList.remove('has-error');
            }
            
            for (let error of errors) {
                error.innerHTML = '';
                error.classList.remove('show');
            }
        }

        function validateForm() {
            let isValid = true;
            const email = document.getElementById('email');
            const pwd = document.getElementById('pwd');

            clearErrors();

            if (email.value.trim() === '') {
                displayError('email', 'Email is required');
                isValid = false;
            } else if (!email.value.includes('@')) {
                displayError('email', 'Please enter a valid email');
                isValid = false;
            }

            if (pwd.value.trim() === '') {
                displayError('pwd', 'Password is required');
                isValid = false;
            }

            return isValid;
        }

        document.getElementById('email').addEventListener('input', function() {
            const formGroup = this.parentElement;
            const errorElement = document.getElementById('email-error');
            
            formGroup.classList.remove('has-error');
            errorElement.classList.remove('show');
            errorElement.innerHTML = '';
        });

        document.getElementById('pwd').addEventListener('input', function() {
            const formGroup = this.parentElement;
            const errorElement = document.getElementById('pwd-error');
            
            formGroup.classList.remove('has-error');
            errorElement.classList.remove('show');
            errorElement.innerHTML = '';
        });
    </script>
</body>
</html>