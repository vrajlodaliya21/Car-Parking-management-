<?php
session_start();

$nameError = $emailError = $contactError = $pwdError = $rPwdError = "";
$success = "";

if (isset($_POST['submit'])) {
    $u_name = $_POST['u_name'];
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $r_pwd = $_POST['r_pwd'];
    $contact = $_POST['contact'];

    require_once "Connection.php";

    $sql = "SELECT * FROM users WHERE U_Email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowcount = mysqli_num_rows($result);

    if (empty($u_name)) {
        $nameError = "User Name is required";
    }
    if (empty($email)) {
        $emailError = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $emailError = "Please enter a valid email";
    } elseif ($rowcount > 0) {
        $emailError = "Email already exists";
    }
    if (empty($contact)) {
        $contactError = "Contact number is required";
    } elseif (!preg_match('/^[0-9]{10}$/', $contact)) {
        $contactError = "Contact number must be 10 digits";
    }
    if (empty($pwd)) {
        $pwdError = "Password is required";
    } elseif (strlen($pwd) < 7) {
        $pwdError = "Password must be at least 7 characters";
    }
    if (empty($r_pwd)) {
        $rPwdError = "Please confirm password";
    } elseif ($pwd !== $r_pwd) {
        $rPwdError = "Passwords do not match";
    }

    if (empty($nameError) && empty($emailError) && empty($contactError) && empty($pwdError) && empty($rPwdError)) {
        $hashpwd = password_hash($pwd, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (U_Name, U_Email, Password, Phone_number) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $u_name, $email, $hashpwd, $contact);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = "Registration successful!";
            header('location:Index.php');
            exit();
        } else {
            $error = "Something went wrong. Please try again.";
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
    <title>Register</title>
    <link rel="stylesheet" href="./bootstrap.min.css">
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
            margin-top: 5px;
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

        .form-group.valid input {
            border-color: #28a745;
        }

        .form-group.invalid input {
            border-color: #dc3545;
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
            <form action="Ragistation.php" method="post" onsubmit="return validateForm()">
                <h2 class="text-center text-capitalize text-white">Registration</h2>
                <hr class="w-50 mx-auto pb-2 border-dark">

                <div class="form-group <?php echo $nameError ? 'has-error invalid' : ''; ?>">
                    <input type="text" class="form-control" name="u_name" id="u_name" 
                           placeholder="User Name" value="<?php echo isset($u_name) ? htmlspecialchars($u_name) : ''; ?>">
                    <div class="error-message <?php echo $nameError ? 'show' : ''; ?>" id="u_name-error">
                        <?php if ($nameError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $nameError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $emailError ? 'has-error invalid' : ''; ?>">
                    <input type="email" class="form-control" name="email" id="email" 
                           placeholder="Email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    <div class="error-message <?php echo $emailError ? 'show' : ''; ?>" id="email-error">
                        <?php if ($emailError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $emailError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $contactError ? 'has-error invalid' : ''; ?>">
                    <input type="text" class="form-control" name="contact" id="contact" 
                           placeholder="Contact Number" maxlength="10" 
                           value="<?php echo isset($contact) ? htmlspecialchars($contact) : ''; ?>">
                    <div class="error-message <?php echo $contactError ? 'show' : ''; ?>" id="contact-error">
                        <?php if ($contactError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $contactError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $pwdError ? 'has-error invalid' : ''; ?>">
                    <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Password">
                    <div class="error-message <?php echo $pwdError ? 'show' : ''; ?>" id="pwd-error">
                        <?php if ($pwdError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $pwdError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $rPwdError ? 'has-error invalid' : ''; ?>">
                    <input type="password" class="form-control" name="r_pwd" id="r_pwd" placeholder="Repeat Password">
                    <div class="error-message <?php echo $rPwdError ? 'show' : ''; ?>" id="r_pwd-error">
                        <?php if ($rPwdError): ?>
                            <i class="fas fa-info-circle"></i><?php echo $rPwdError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <input type="submit" class="btn btn-custom w-100" name="submit" value="Register">
                </div>
            </form>

            <div class="text-center">
                <p class="reg text-white">Already Registered? <a href="Index.php" class="register-link">Login Here</a></p>
            </div>
        </div>
    </div>

    <script>
        function displayError(inputId, message) {
            const formGroup = document.getElementById(inputId).parentElement;
            const errorElement = document.getElementById(`${inputId}-error`);
            
            formGroup.classList.add('has-error', 'invalid');
            formGroup.classList.remove('valid');
            errorElement.innerHTML = `<i class="fas fa-info-circle"></i>${message}`;
            errorElement.classList.add('show');
        }

        function displayValid(inputId) {
            const formGroup = document.getElementById(inputId).parentElement;
            const errorElement = document.getElementById(`${inputId}-error`);
            
            formGroup.classList.remove('has-error', 'invalid');
            formGroup.classList.add('valid');
            errorElement.classList.remove('show');
            errorElement.innerHTML = '';
        }

        function validateForm() {
            let isValid = true;
            const uName = document.getElementById('u_name');
            const email = document.getElementById('email');
            const contact = document.getElementById('contact');
            const pwd = document.getElementById('pwd');
            const rPwd = document.getElementById('r_pwd');

            if (uName.value.trim() === '') {
                displayError('u_name', 'User Name is required');
                isValid = false;
            } else {
                displayValid('u_name');
            }

            if (email.value.trim() === '') {
                displayError('email', 'Email is required');
                isValid = false;
            } else if (!email.value.includes('@')) {
                displayError('email', 'Please enter a valid email');
                isValid = false;
            } else {
                displayValid('email');
            }

            if (contact.value.trim() === '') {
                displayError('contact', 'Contact number is required');
                isValid = false;
            } else if (!contact.value.match(/^[0-9]{10}$/)) {
                displayError('contact', 'Contact number must be 10 digits');
                isValid = false;
            } else {
                displayValid('contact');
            }

            if (pwd.value.trim() === '') {
                displayError('pwd', 'Password is required');
                isValid = false;
            } else if (pwd.value.length < 7) {
                displayError('pwd', 'Password must be at least 7 characters');
                isValid = false;
            } else {
                displayValid('pwd');
            }

            if (rPwd.value.trim() === '') {
                displayError('r_pwd', 'Please confirm password');
                isValid = false;
            } else if (rPwd.value !== pwd.value) {
                displayError('r_pwd', 'Passwords do not match');
                isValid = false;
            } else {
                displayValid('r_pwd');
            }

            return isValid;
        }

        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', function() {
                const formGroup = this.parentElement;
                const errorElement = document.getElementById(`${this.id}-error`);
                
                formGroup.classList.remove('has-error', 'invalid');
                errorElement.classList.remove('show');
                errorElement.innerHTML = '';
            });
        });
    </script>
</body>
</html>