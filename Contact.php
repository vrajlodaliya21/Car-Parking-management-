<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

include 'Connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $mobile = htmlspecialchars($_POST['mno']);
    $message = htmlspecialchars($_POST['message']);

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'parkheaven777@gmail.com';
        $mail->Password = 'ytxwhtesrejqqkcd';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('parkheaven777@gmail.com', 'Park Heaven Support');
        $mail->addAddress($email, $name);
        $mail->addReplyTo('parkheaven777@gmail.com', 'Park Heaven Support');

        $mail->isHTML(true);
        $mail->Subject = 'Thank You for Contacting Us';
        $mail->Body = "
            <h1>Thank You for Reaching Out!</h1>
            <p>Dear $name,</p>
            <p>We have received your message:</p>
            <blockquote>$message</blockquote>
            <p>Our support team will get back to you shortly.</p>
            <p><strong>Your Contact Details:</strong></p>
            <ul>
                <li><strong>Email:</strong> $email</li>
                <li><strong>Mobile:</strong> $mobile</li>
            </ul>
            <p>Best Regards,</p>
            <p>The Park Heaven Team</p>
        ";

        $mail->send();

        $mail->clearAddresses();
        $mail->clearReplyTos();

        $mail->setFrom('parkheaven777@gmail.com', 'Park Heaven Website');
        $mail->addAddress('parkheaven777@gmail.com', 'Park Heaven Support');

        $mail->Subject = 'New Contact Form Submission';
        $mail->Body = "
            <h2>New Contact Form Submission</h2>
            <p><strong>From:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Mobile:</strong> $mobile</p>
            <p><strong>Message:</strong></p>
            <blockquote>$message</blockquote>
        ";

        $mail->send();

        $_SESSION['alert'] = [
            'type' => 'success',
            'message' => 'Your message has been sent successfully! We will contact you soon.'
        ];

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['alert'] = [
            'type' => 'danger',
            'message' => 'Message could not be sent. Please try again later.'
        ];
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
}

if (isset($_SESSION['admin'])) {
    header('Location: Index.php');
    exit();
}

include 'Connection.php';

if (!isset($_SESSION['user'])) {
    header('Location: Index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Contact Us</title>
    <link rel='stylesheet' href='./Styles.css'>
    <link href='bootstrap.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>
    <style>
        body {
            padding-top: 60px;
        }

        .form-group label {
            font-size: 1.25rem;
        }

        .form input {
            margin-top: 30px;
            border-radius: 22px;
            height: 45px;
        }

        .con {
            font-size: 19px;
        }

        .con1 {
            font-size: 60px;
            font-weight: 700;
        }

        .con2 {
            font-size: 45px;
        }

        .round {
            border-radius: 20px;
            margin-top: 170px;
            margin-left: 50px;
        }

        @media only screen and (max-width:400px) {
            .round {
                border-radius: 20px;
                margin-top: 20px;
                margin-left: 0px;
            }
        }
    </style>
</head>

<body>
    <?php require_once './Includes/Unavbar.php' ?>

    <?php if (isset($_SESSION['alert'])): ?>
        <div class="container mt-3">
            <div class="alert alert-<?php echo $_SESSION['alert']['type']; ?> alert-dismissible fade show" role="alert">
                <strong><?php echo $_SESSION['alert']['type'] === 'success' ? 'Success!' : 'Error!'; ?></strong>
                <?php echo $_SESSION['alert']['message']; ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
        <?php
        unset($_SESSION['alert']);
    endif;
    ?>

    <section class='bg-content pt-5'>
        <div class='container col-11'>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-12">
                    <div>
                        <h1 class='con1'>Contact Us</h1>
                        <p class='mt-3 h5'>If you need more information, please contact us, and we'll be glad to assist
                            you as soon as possible.</p>
                    </div>

                    <form action='Contact.php' method='post' class='form mx-auto' id="contactForm">
                        <div class='form-group'>
                            <input type='text' class='form-control' id='name' name='name' placeholder="Your Name">
                            <small class="error" id="nameError" style="color: red;"></small>
                        </div>

                        <div class='form-group'>
                            <input type='email' class='form-control' id='email' name='email' placeholder="Your Email">
                            <small class="error" id="emailError" style="color: red;"></small>
                        </div>

                        <div class='form-group'>
                            <input type='text' maxlength="10" class='form-control' id='mno' name='mno'
                                placeholder="Mobile No.">
                            <small class="error" id="mnoError" style="color: red;"></small>
                        </div>

                        <div class='form-group'>
                            <textarea class='form-control mb-4' id='message' name='message' rows='5'
                                style="border-radius: 19px;" placeholder="How Can We Help You?"></textarea>
                            <small class="error" id="messageError" style="color: red;"></small>
                        </div>

                        <button type='submit' class='btn btn-outline-primary w-25'>Submit</button>
                    </form>
                </div>

                <div class='col-lg-6 col-md-6 col-12'>
                    <div>
                        <img src='./Images/contact.jpg' class='img-fluid round'>
                    </div>
                </div>
            </div>

            <h2 class='con2 mb-3 mt-5'>Contact Details : </h2>
            <hr class='w-25 border-dark mb-5'>
            <div class="con">
                <p><strong>Phone:</strong> +91 9876543211</p>
                <p><strong>Email:</strong> Parkheaven007@gmail.com</p>
            </div>
            <h2 class='con2 mb-3 mt-5'>Find Us : </h2>
            <hr class='w-25 border-dark mb-5'>
            <iframe
                src='https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d124337.48871247417!2d77.036871!3d28.647358!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x390d1f1b6310f6bf%3A0x9b058a845a6a0b9!2sNew%20Delhi%2C%20Delhi%2C%20India!5e0!3m2!1sen!2sus!4v1625835852302!5m2!1sen!2sus'
                width='100%' height='400' style='border:0;' allowfullscreen='' loading='lazy' class='pb-5'></iframe>
        </div>
    </section>

    <section>
        <?php require_once './Includes/footer.php' ?>
    </section>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function (e) {
            let valid = true;

            document.getElementById('nameError').textContent = '';
            document.getElementById('emailError').textContent = '';
            document.getElementById('mnoError').textContent = '';
            document.getElementById('messageError').textContent = '';

            // Validate Data.
            if (document.getElementById('name').value.trim() === '') {
                valid = false;
                document.getElementById('nameError').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Name is required';
            }

            let email = document.getElementById('email').value.trim();
            let emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (email === '') {
                valid = false;
                document.getElementById('emailError').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Email is required';
            } else if (!emailPattern.test(email)) {
                valid = false;
                document.getElementById('emailError').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Please enter a valid email address';
            }

            let mobile = document.getElementById('mno').value.trim();
            if (mobile === '') {
                valid = false;
                document.getElementById('mnoError').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Mobile number is required';
            } else if (mobile.length !== 10) {
                valid = false;
                document.getElementById('mnoError').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Please enter a 10-digit mobile number';
            }

            if (document.getElementById('message').value.trim() === '') {
                valid = false;
                document.getElementById('messageError').innerHTML = '<i class="fas fa-info-circle ml-1"></i> Message is required';
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    </script>

</body>

</html>
<script src='https://code.jquery.com/jquery-3.6.0.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/popper.js/2.11.6/umd/popper.min.js'></script>
<script src='https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js'></script>