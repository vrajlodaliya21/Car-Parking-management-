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

?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <link rel='shortcut icon' type='image/x-icon' href='../Car/Images/Main_Image.png'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='shortcut icon' type='x-icon' href=''>
    <title>About Us</title>
    <link rel='stylesheet' href='./bootstrap.min.css'>
    <link rel='stylesheet' href='./Styles.css'>
    <style>
        body {
            padding-top: 50px;
        }
        
    </style>
</head>

<body>
    <?php require_once './Includes/Unavbar.php' ?>
    <section class='bg-content py-5'>
        <div class='container-fluid col-11 mb-5'>
            <div>
                <h1 class='text-center text-capitalize pb-2'><b><span>About </span>- Us</b></h1>
                <hr class='w-25 mx-auto pb-5 border-dark'>
            </div>
            <div class='row mb-5 '>
                <div class='col-lg-6 col-md-6 col-12'>
                    <div>
                        <img src='./Images/Team.jpg' class='img-fluid rounded-left rounded-right'>
                    </div>
                </div>
                <div class='col-lg-6 col-md-6 col-12 mt-5'>
                    <h1 class='text-black pt-4 h1'>Team</h1>
                    <p class='mt-3 h5'>We are a group of earnest and dedicated individuals from backgrounds as diverse
                        as engineering and creative writing.
                    </p>

                    <p class='mt-3 h5'>
                        Our products have been well received since the initial stages by organisations across sectors.
                        It’s a feat that, we believe, would have been impossible without a fierce team spirit, a strong
                        sense of purpose, and a deeply creative approach to problem solving.
                    </p>

                    <p class='mt-3 h5'>
                        Lorem ipsum dolor sit amet, consectetur adipisicing elit. Ut quos quisquam odio?
                    </p>

                </div>
            </div>

            <div class='row mb-5 '>
                <div class='col-lg-6 col-md-6 col-12 mt-5'>
                    <h1 class='text-black pt-4 h1'>Culture</h1>
                    <p class='mt-3 h5'>As an organisation, we follow a flat structure and a liberal work environment
                        where everyone has a say and fresh ideas are not only welcome but encouraged.
                    </p>

                    <p class='mt-3 h5'>
                        Our culture fosters learning, creativity, innovation, patience, and continuous growth.
                    </p>

                    <p class='mt-3 h5'>
                        Also, 60% of us are women (as of Nov 2022).
                    </p>

                    <p class='mt-3 h5'>We're POSH compliant too.</p>

                </div>

                <div class='col-lg-6 col-md-6 col-12'>
                    <div>
                        <img src='./Images/culture.jpg' class='img-fluid rounded-left rounded-right'>
                    </div>
                </div>
            </div>

            <div class='row pb-5 '>

                <div class='col-lg-6 col-md-6 col-12'>
                    <div>
                        <img src='./Images/founders.jpg' class='img-fluid rounded-left rounded-right'>
                    </div>
                </div>

                <div class='col-lg-6 col-md-6 col-12 mt-5'>
                    <h1 class='text-black pt-4 h1'>Founders</h1>
                    <p class='mt-3 h5'>A company is only as good as its people.
                    </p>

                    <p class='mt-3 h5'>
                        My prime responsibility is to create a space where each individual at Our achieves his or her
                        fullest potential; where there is complete trust, freedom, and accountability; where everyone
                        does what they love doing and feel belonged.
                    </p>

                    <p class='mt-3 h5'>
                        - Preet Kachhadiya.
                    </p>
                </div>
            </div>
        </div>
    </section>


    <section>
        <?php require_once './Includes/footer.php' ?>
    </section>
</body>

</html>

<script src='https://code.jquery.com/jquery-3.2.1.slim.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js'></script>