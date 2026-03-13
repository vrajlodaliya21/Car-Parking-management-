<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: UDashboard.php');
    exit();
} elseif (isset($_SESSION['admin'])) {
    header('Location: dashboard.php');
    exit();
} elseif (isset($_SESSION['vendor'])) {
    header('Location: Vendors/VDashboard.php');
    exit();
}

include 'Connection.php';

$emailError = "";
$pwdError = "";

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $role = $_POST['role'];

    if ($role === 'user') {
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

        if ($user) {
            if (!password_verify($pwd, $user['Password'])) {
                $pwdError = "Incorrect password";
            } else {
                $_SESSION['user'] = $user['U_Name'];
                $_SESSION['user_email'] = $user['U_Email'];
                header('Location: UDashboard.php');
                exit();
            }
        } elseif ($admin) {
            if ($pwd !== $admin['A_Password']) {
                $pwdError = "Incorrect password";
            } else {
                $_SESSION['admin'] = 'yes';
                header('Location: dashboard.php');
                exit();
            }
        } else {
            $emailError = "Account not found";
        }
    } elseif ($role === 'vendor') {
        $city = $_POST['city'];
        $area = $_POST['area'];
        $location = $_POST['location'];

        $sqlVendor = "SELECT * FROM vendors WHERE email = ? AND city = ? AND area = ? AND location = ?";
        $stmt = mysqli_prepare($conn, $sqlVendor);
        mysqli_stmt_bind_param($stmt, "ssss", $email, $city, $area, $location);
        mysqli_stmt_execute($stmt);
        $resultVendor = mysqli_stmt_get_result($stmt);
        $vendor = mysqli_fetch_array($resultVendor, MYSQLI_ASSOC);

        if (!$vendor) {
            $emailError = "Vendor account not found with these details";
        } elseif (!password_verify($pwd, $vendor['password'])) {
            $pwdError = "Incorrect password";
        } elseif ($vendor['status'] === 'pending') {
            $emailError = "Account awaiting admin approval";
        } elseif ($vendor['status'] === 'denied') {
            $emailError = "Account denied. Contact support";
        } else {
            $_SESSION['vendor'] = $vendor['name'];
            $_SESSION['vendor_id'] = $vendor['id'];
            header('Location: Vendors/VDashboard.php');
            exit();
        }
    }
}

$available_locations = $conn->query("SELECT id, city, area, location, price FROM slots")->fetch_all(MYSQLI_ASSOC);
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
        .img {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('./Images/login.jpg');
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 100vh;
            background-attachment: fixed;
        }
        .login { 
            max-width: 450px; 
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
        .form-group { position: relative; margin-bottom: 15px; }
        .form-group input::placeholder { font-size: 19px; }
        .error-message { color: red; font-size: 14px; display: none; align-items: center; padding-left: 10px; margin-top: 5px; }
        .error-message.show { display: flex; }
        .form-group.has-error {
            margin-bottom: 25px;
        }

        /* Make vendor selects same size as inputs */
        #vendor-fields .form-control {
            height: 55px;
            font-size: 19px;
            padding: 12px 15px;
        }

        #vendor-fields .form-group {
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <?php if(isset($_SESSION['vendor_msg'])): ?>
        <div id="vendor-notification" class="alert alert-info alert-dismissible fade show animate__animated animate__fadeInDown" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 9999; max-width: 500px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); border: none; background: #fff; color: #0284c7; padding: 20px 40px;">
            <div class="d-flex align-items-center">
                <i class="fas fa-clock fa-2x mr-3 text-info"></i>
                <div>
                    <h6 class="font-weight-bold mb-1">Registration Pending</h6>
                    <p class="mb-0 small"><?php echo $_SESSION['vendor_msg']; unset($_SESSION['vendor_msg']); ?></p>
                </div>
            </div>
            <button type="button" class="close" data-dismiss="alert" style="outline: none;">&times;</button>
        </div>
        <script>
            setTimeout(() => {
                const el = document.getElementById('vendor-notification');
                if(el) {
                    el.classList.remove('animate__fadeInDown');
                    el.classList.add('animate__fadeOutUp');
                    setTimeout(() => el.remove(), 1000);
                }
            }, 6000);
        </script>
    <?php endif; ?>
    <div class="img">
        <div class="login">
            <form action="Index.php" method="post" id="loginForm">
                <h2 class="text-center">Log In</h2>
                <hr class="w-50 mx-auto pb-2 border-primary">
                
                <div class="d-flex justify-content-center mb-3 gap-4 text-white">
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleUser" value="user" checked>
                        <label class="form-check-label" for="roleUser">User / Admin</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="roleVendor" value="vendor">
                        <label class="form-check-label" for="roleVendor">Vendor</label>
                    </div>
                </div>

                <div class="form-group <?php echo $emailError ? 'has-error' : ''; ?>">
                    <input type="email" class="form-control" name="email" id="email" placeholder="Email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                    <div class="error-message <?php echo $emailError ? 'show' : ''; ?>"><?php echo $emailError; ?></div>
                </div>
                
                <div class="form-group <?php echo $pwdError ? 'has-error' : ''; ?>">
                    <div class="input-group">
                        <input type="password" class="form-control" name="pwd" id="pwd" placeholder="Password" style="border-right: none;" required>
                        <div class="input-group-append">
                            <span class="input-group-text bg-white border-left-0" style="cursor: pointer;" onclick="togglePassword('pwd', this)"><i class="fas fa-eye"></i></span>
                        </div>
                    </div>
                    <div class="error-message <?php echo $pwdError ? 'show' : ''; ?>"><?php echo $pwdError; ?></div>
                </div>

                <div id="vendor-fields" style="display: none;">
                    <div class="form-group">
                        <select class="form-control" name="city" id="city" onchange="updateAreas()">
                            <option value="">Select City</option>
                            <?php 
                            $cities = array_unique(array_column($available_locations, 'city'));
                            foreach($cities as $c) echo "<option value='$c'>$c</option>"; 
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="area" id="area" onchange="updateLocations()" disabled><option value="">Select Area</option></select>
                    </div>
                    <div class="form-group">
                        <select class="form-control" name="location" id="location" disabled><option value="">Select Location</option></select>
                    </div>
                </div>
                
                <div class="form-group mt-4">
                    <input type="submit" class="btn btn-custom w-100" name="submit" value="Log In">
                </div>
            </form>
            <div class="text-center mt-3">
                <p class="reg text-white" id="reg-link-container">
                    Not Registered Yet? <a href="Ragistation.php" class="register-link">Register Here</a>
                </p>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const locations = <?= json_encode($available_locations) ?>;
        
        function updateUIByRole() {
            const isVendor = document.getElementById('roleVendor').checked;
            document.getElementById('vendor-fields').style.display = isVendor ? 'block' : 'none';
            document.getElementById('reg-link-container').innerHTML = isVendor 
                ? 'Register as Vendor? <a href="VendorRegistration.php" class="register-link">Register Here</a>' 
                : 'Not Registered Yet? <a href="Ragistation.php" class="register-link">Register Here</a>';
            
            const fields = ['city', 'area', 'location'];
            fields.forEach(id => document.getElementById(id).required = isVendor);
        }

        document.getElementsByName('role').forEach(r => r.addEventListener('change', updateUIByRole));

        function updateAreas() {
            const city = document.getElementById('city').value;
            const areaSelect = document.getElementById('area');
            const locSelect = document.getElementById('location');
            areaSelect.innerHTML = '<option value="">Select Area</option>';
            locSelect.innerHTML = '<option value="">Select Location</option>';
            areaSelect.disabled = true; locSelect.disabled = true;

            if(city) {
                const areas = [...new Set(locations.filter(l => l.city.trim() === city.trim()).map(l => l.area.trim()))];
                areas.sort().forEach(a => {
                    const opt = document.createElement('option');
                    opt.value = opt.textContent = a;
                    areaSelect.appendChild(opt);
                });
                areaSelect.disabled = false;
            }
        }

        function updateLocations() {
            const city = document.getElementById('city').value;
            const area = document.getElementById('area').value;
            const locSelect = document.getElementById('location');
            locSelect.innerHTML = '<option value="">Select Location</option>';
            locSelect.disabled = true;

            if(area) {
                locations.filter(l => l.city.trim() === city.trim() && l.area.trim() === area.trim()).forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = opt.textContent = l.location.trim();
                    locSelect.appendChild(opt);
                });
                locSelect.disabled = false;
            }
        }

        function togglePassword(id, el) {
            const x = document.getElementById(id);
            const icon = el.querySelector('i');
            if (x.type === "password") { x.type = "text"; icon.className = "fas fa-eye-slash"; }
            else { x.type = "password"; icon.className = "fas fa-eye"; }
        }

        updateUIByRole();
    </script>
</body>
</html>