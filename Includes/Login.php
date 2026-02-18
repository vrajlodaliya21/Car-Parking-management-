<?php
session_start();

// If vendor is already logged in, redirect to dashboard
if (isset($_SESSION['vendor'])) {
    header('Location: VDashboard.php');
    exit();
}

require_once '../Connection.php';

$emailError = '';
$pwdError = '';
$cityError = '';
$areaError = '';
$locationError = '';

$city_query = 'SELECT DISTINCT city FROM vendors ORDER BY city';
$city_result = mysqli_query($conn, $city_query);

$area_query = 'SELECT DISTINCT area FROM vendors ORDER BY area';
$area_result = mysqli_query($conn, $area_query);

$location_query = 'SELECT DISTINCT location FROM vendors ORDER BY location';
$location_result = mysqli_query($conn, $location_query);

if (isset($_POST['submit'])) {
    $email = $_POST['email'];
    $pwd = $_POST['pwd'];
    $city = $_POST['city'];
    $area = $_POST['area'];
    $location = $_POST['location'];

    if (empty($email)) {
        $emailError = 'Email is required';
    } elseif (empty($pwd)) {
        $pwdError = 'Password is required';
    } elseif (empty($city)) {
        $cityError = 'Please select a city';
    } elseif (empty($area)) {
        $areaError = 'Please select an area';
    } elseif (empty($location)) {
        $locationError = 'Please select a location';
    } else {
        $sqlVendor = 'SELECT * FROM vendors WHERE email = ? AND city = ? AND area = ? AND location = ?';
        $stmt = mysqli_prepare($conn, $sqlVendor);
        mysqli_stmt_bind_param($stmt, 'ssss', $email, $city, $area, $location);
        mysqli_stmt_execute($stmt);
        $resultVendor = mysqli_stmt_get_result($stmt);
        $vendor = mysqli_fetch_array($resultVendor, MYSQLI_ASSOC);

        if (!$vendor) {
            $emailError = 'No vendor found with this email and location combination';
        } else {
            if (!password_verify($pwd, $vendor['password'])) {
                $pwdError = 'Incorrect password. Please try again';
            } else {
                $_SESSION['vendor'] = $vendor['name'];
                $_SESSION['vendor_email'] = $vendor['email'];
                $_SESSION['vendor_id'] = $vendor['id'];
                header('Location: VDashboard.php');
                exit();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Vendor Log In</title>
    <link rel='stylesheet' href='bootstrap.min.css'>
    <link rel='stylesheet' href='Styles.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'>
    <style>
        .form-group {
            position: relative;
            margin-bottom: 25px;
        }

        .form-group input::placeholder,
        .form-group select {
            font-size: 19px;
        }

        .form-group input,
        .form-group select {
            margin: 0 !important;
            padding: 12px 15px;
        }

        .form-group select {
            height: 55px !important;
            padding: 12px 15px;
        }

        .error-message {
            color: #dc3545;
            font-size: 14px;
            display: none;
            align-items: center;
            padding: 8px 0 0 15px;
            margin: 0;
            min-height: 24px;
            font-weight: 500;
        }

        .error-message.show {
            display: flex;
        }

        .error-message i {
            margin-right: 8px;
            font-size: 16px;
        }

        .form-group.has-error input,
        .form-group.has-error select {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .form-group.has-error {
            margin-bottom: 25px;
        }

        @media only screen and (max-width: 400px) {
            .form-group input,
            .form-group select {
                font-size: 16px;
            }

            .error-message {
                font-size: 13px;
                padding: 6px 0 0 12px;
            }
        }
    </style>
</head>

<body>
    <div class='img'>
        <div class='login'>
            <form action='Login.php' method='post' onsubmit='return validateForm()'>
                <h2 class='text-center'>Vendor Log In</h2>
                <hr class='w-50 mx-auto pb-2 border-primary'>

                <div class="form-group <?php echo $emailError ? 'has-error' : ''; ?>">
                    <input type='email' class='form-control' name='email' id='email' placeholder='Email'
                        style='font-size: 19px;'
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    <div class="error-message <?php echo $emailError ? 'show' : ''; ?>" id='email-error'>
                        <?php if ($emailError): ?>
                            <i class='fas fa-info-circle'></i>
                            <?php echo $emailError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $pwdError ? 'has-error' : ''; ?>">
                    <input type='password' class='form-control' name='pwd' id='pwd' placeholder='Password'
                        style='font-size: 19px;'>
                    <div class="error-message <?php echo $pwdError ? 'show' : ''; ?>" id='pwd-error'>
                        <?php if ($pwdError): ?>
                            <i class='fas fa-info-circle'></i>
                            <?php echo $pwdError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $cityError ? 'has-error' : ''; ?>">
                    <select class='form-control' name='city' id='city'>
                        <option value=''>Select City</option>
                        <?php while ($city = mysqli_fetch_assoc($city_result)): ?>
                            <option value="<?php echo htmlspecialchars($city['city']); ?>" <?php echo (isset($_POST['city']) && $_POST['city'] == $city['city']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($city['city']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="error-message <?php echo $cityError ? 'show' : ''; ?>" id='city-error'>
                        <?php if ($cityError): ?>
                            <i class='fas fa-info-circle'></i>
                            <?php echo $cityError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $areaError ? 'has-error' : ''; ?>">
                    <select class='form-control' name='area' id='area'>
                        <option value=''>Select Area</option>
                        <?php while ($area = mysqli_fetch_assoc($area_result)): ?>
                            <option value="<?php echo htmlspecialchars($area['area']); ?>" <?php echo (isset($_POST['area']) && $_POST['area'] == $area['area']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($area['area']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="error-message <?php echo $areaError ? 'show' : ''; ?>" id='area-error'>
                        <?php if ($areaError): ?>
                            <i class='fas fa-info-circle'></i>
                            <?php echo $areaError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-group <?php echo $locationError ? 'has-error' : ''; ?>">
                    <select class='form-control' name='location' id='location'>
                        <option value=''>Select Location</option>
                        <?php while ($location = mysqli_fetch_assoc($location_result)): ?>
                            <option value="<?php echo htmlspecialchars($location['location']); ?>" <?php echo (isset($_POST['location']) && $_POST['location'] == $location['location']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($location['location']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="error-message <?php echo $locationError ? 'show' : ''; ?>" id='location-error'>
                        <?php if ($locationError): ?>
                            <i class='fas fa-info-circle'></i>
                            <?php echo $locationError; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class='form-group' style='margin-top: 20px;'>
                    <input type='submit' class='btn btn-custom w-100' name='submit' value='Log In'>
                </div>
            </form>
        </div>
    </div>

    <script>
        function displayError(inputId, message) {
            const formGroup = document.getElementById(inputId).parentElement;
            const errorElement = document.getElementById(`${inputId}-error`);

            formGroup.classList.add('has-error');
            errorElement.innerHTML = `<i class='fas fa-info-circle'></i>${message}`;
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
            const city = document.getElementById('city');
            const area = document.getElementById('area');
            const location = document.getElementById('location');

            clearErrors();

            if (email.value.trim() === '') {
                displayError('email', 'Email is required');
                isValid = false;
            } else if (!email.value.includes('@')) {
                displayError('email', 'Please enter a valid email address');
                isValid = false;
            } else if (!email.value.includes('.')) {
                displayError('email', 'Please enter a valid email address');
                isValid = false;
            }

            if (pwd.value.trim() === '') {
                displayError('pwd', 'Password is required');
                isValid = false;
            } else if (pwd.value.length < 6) {
                displayError('pwd', 'Password must be at least 6 characters long');
                isValid = false;
            }
            if (city.value === '') {
                displayError('city', 'Please select a city');
                isValid = false;
            }
            if (area.value === '') {
                displayError('area', 'Please select an area');
                isValid = false;
            }
            if (location.value === '') {
                displayError('location', 'Please select a location');
                isValid = false;
            }

            return isValid;
        }

        ['email', 'pwd', 'city', 'area', 'location'].forEach(id => {
            document.getElementById(id).addEventListener('input', function () {
                const formGroup = this.parentElement;
                const errorElement = document.getElementById(`${id}-error`);

                formGroup.classList.remove('has-error');
                errorElement.classList.remove('show');
                errorElement.innerHTML = '';
            });
        });
    </script>
</body>
</html>