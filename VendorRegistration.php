<?php
session_start();

$nameError = $emailError = $contactError = $pwdError = $rPwdError = "";
$cityError = $areaError = $locationError = "";
$success = "";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $r_pwd = $_POST['r_pwd'];
    $contact = $_POST['contact'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $location = $_POST['location'];

    require_once "Connection.php";

    $sql = "SELECT * FROM vendors WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $rowcount = mysqli_num_rows($result);

    if (empty($name)) $nameError = "Name is required";
    if (empty($email)) $emailError = "Email is required";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $emailError = "Invalid email";
    elseif ($rowcount > 0) $emailError = "Email already exists";
    
    if (empty($contact)) $contactError = "Contact is required";
    elseif (!preg_match('/^[0-9]{10}$/', $contact)) $contactError = "10 digits required";
    
    if (empty($pwd)) $pwdError = "Password is required";
    elseif (strlen($pwd) < 7) $pwdError = "Min 7 characters";
    
    if (empty($r_pwd)) $rPwdError = "Confirm password";
    elseif ($pwd !== $r_pwd) $rPwdError = "No match";

    if (empty($city)) $cityError = "City is required";
    if (empty($area)) $areaError = "Area is required";
    if (empty($location)) $locationError = "Location is required";

    if (empty($nameError) && empty($emailError) && empty($contactError) && empty($pwdError) && empty($rPwdError) && empty($cityError) && empty($areaError) && empty($locationError)) {
        $hashpwd = password_hash($pwd, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO vendors (name, email, password, phone, city, area, location, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssssss", $name, $email, $hashpwd, $contact, $city, $area, $location);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['vendor_msg'] = "Your data has been sent for approval from the admin side. You will be able to log in once approved.";
            header('location:Index.php');
            exit();
        } else {
            $error = "Something went wrong.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendor Registration</title>
    <link rel="stylesheet" href="./bootstrap.min.css">
    <link rel="stylesheet" href="./Styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .img {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./Images/Parking.jpg');
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 100vh;
            background-attachment: fixed;
        }
        .login { 
            max-width: 850px; 
            padding: 40px; 
            margin: 0;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 33px;
            box-shadow: 0px 25px 50px rgba(0, 0, 0, 0.3);
        }
        .form-group { position: relative; margin-bottom: 25px; }
        .form-control {
            height: 55px;
            font-size: 19px;
            padding: 12px 15px;
            border-radius: 10px;
        }
        .input-group-text {
            padding: 0 20px;
            border-radius: 0 10px 10px 0;
        }
        .error-message { color: #ff4d4d; font-size: 13px; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="img">
        <div class="login">
            <form action="VendorRegistration.php" method="post">
                <h2 class="text-center text-white">Vendor Registration</h2>
                <hr class="w-50 mx-auto pb-2 border-dark">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="name" placeholder="Full Name" value="<?=isset($name)?htmlspecialchars($name):''?>">
                            <div class="error-message"><?=$nameError?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="email" class="form-control" name="email" placeholder="Email" value="<?=isset($email)?htmlspecialchars($email):''?>">
                            <div class="error-message"><?=$emailError?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="contact" placeholder="Contact Number" maxlength="10" value="<?=isset($contact)?htmlspecialchars($contact):''?>">
                            <div class="error-message"><?=$contactError?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="city" placeholder="City" value="<?=isset($city)?htmlspecialchars($city):''?>">
                            <div class="error-message"><?=$cityError?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="area" placeholder="Area" value="<?=isset($area)?htmlspecialchars($area):''?>">
                            <div class="error-message"><?=$areaError?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" name="location" placeholder="Exact Location Name" value="<?=isset($location)?htmlspecialchars($location):''?>">
                            <div class="error-message"><?=$locationError?></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Password" style="border-right: none;">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white border-left-0" style="cursor: pointer;" onclick="togglePassword('pwd', this)">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="error-message"><?=$pwdError?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="password" class="form-control" name="r_pwd" id="r_pwd" placeholder="Confirm Password" style="border-right: none;">
                                <div class="input-group-append">
                                    <span class="input-group-text bg-white border-left-0" style="cursor: pointer;" onclick="togglePassword('r_pwd', this)">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="error-message"><?=$rPwdError?></div>
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <input type="submit" class="btn btn-custom w-100" name="submit" value="Register as Vendor">
                </div>
            </form>
            <div class="text-center mt-3">
                <p class="reg text-white mb-1">Register as a User? <a href="Ragistation.php" class="register-link">Register Here</a></p>
                <p class="reg text-white">Already Registered? <a href="Index.php" class="register-link">Login Here</a></p>
            </div>
        </div>
    </div>
    <script>
        function togglePassword(inputId, element) {
            const input = document.getElementById(inputId);
            const icon = element.querySelector('i');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>